<?php

namespace App\Http\Controllers;

use App\Http\Requests\CutoffPaymentRequest;
use App\Models\Expenses;
use App\Models\SpendIncome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CutoffController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePermission('cutoff.view');

        $selectedMonth = (string) $request->input('month', now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $userId = Auth::id();

        $expenses = Expenses::query()
            ->with([
                'spendIncomes' => fn ($query) => $query
                    ->where('user_id', $userId)
                    ->where('entry_type', 'spend')
                    ->orderBy('transaction_date')
                    ->orderBy('id'),
            ])
            ->where('created_by', $userId)
            ->where('date_start', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart) {
                $query->whereNull('date_end')
                    ->orWhere('date_end', '>=', $monthStart);

                $query->orWhere('type', 'loan');
            })
            ->orderBy('pay_in')
            ->orderBy('name')
            ->get();

        $expenseSnapshots = $expenses
            ->filter(fn (Expenses $expense): bool => $this->isExpenseVisibleInMonth(
                expense: $expense,
                monthStart: $monthStart,
                monthEnd: $monthEnd,
            ))
            ->map(fn (Expenses $expense): array => $this->transformExpenseForMonth(
                expense: $expense,
                monthStart: $monthStart,
                monthEnd: $monthEnd,
            ));

        $cutoffs = collect(['first', 'second'])
            ->mapWithKeys(function (string $payIn) use ($expenseSnapshots): array {
                $items = $expenseSnapshots
                    ->where('pay_in', $payIn)
                    ->values();

                return [
                    $payIn => [
                        'key' => $payIn,
                        'label' => $payIn === 'first' ? 'First Cutoff' : 'Second Cutoff',
                        'count' => $items->count(),
                        'total_amount' => round((float) $items->sum('effective_due_amount'), 4),
                        'total_paid' => round((float) $items->sum('paid_this_month'), 4),
                        'items' => $items->all(),
                    ],
                ];
            });

        return Inertia::render('cutoff/Index', [
            'filters' => [
                'month' => $selectedMonth,
            ],
            'summary' => [
                'month' => $monthStart->format('F Y'),
                'count' => $expenseSnapshots->count(),
                'total_amount' => round((float) $expenseSnapshots->sum('effective_due_amount'), 4),
                'total_paid' => round((float) $expenseSnapshots->sum('paid_this_month'), 4),
            ],
            'cutoffs' => $cutoffs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(CutoffPaymentRequest $request): RedirectResponse
    {
        $this->authorizePermission('cutoff.view');
        $this->authorizePermission('spend_income.create');

        $validated = $request->validated();
        $userId = Auth::id();
        $selectedMonth = (string) $validated['month'];
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $expense = Expenses::query()
            ->whereKey((int) $validated['expense_id'])
            ->where('created_by', $userId)
            ->firstOrFail();

        abort_unless($this->isExpenseVisibleInMonth($expense, $monthStart, $monthEnd), 404);

        DB::transaction(function () use ($expense, $userId, $validated): void {
            $lockedExpense = Expenses::query()
                ->whereKey($expense->id)
                ->where('created_by', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $isPenalty = ($validated['action_type'] ?? 'payment') === 'penalty';

            SpendIncome::create([
                'user_id' => $userId,
                'entry_type' => 'spend',
                'transaction_date' => $validated['transaction_date'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] !== null && $validated['description'] !== ''
                    ? $validated['description']
                    : ($isPenalty
                        ? "Cutoff penalty for {$lockedExpense->name}"
                        : "Cutoff payment for {$lockedExpense->name}"),
                'expense_id' => $lockedExpense->id,
                'account_id' => null,
                'is_penalty' => $isPenalty,
                'is_payroll' => false,
                'payroll_month' => null,
                'payroll_year' => null,
            ]);

            if (! $isPenalty) {
                $lockedExpense->update([
                    'paid_amount' => max(0, round((float) $lockedExpense->paid_amount + (float) $validated['amount'], 4)),
                ]);
            }
        });

        return redirect()
            ->route('cutoff.index', ['month' => $selectedMonth])
            ->with('success', ($validated['action_type'] ?? 'payment') === 'penalty'
                ? 'Expense penalty recorded successfully.'
                : 'Expense payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}

    /**
     * @return array<string, mixed>
     */
    private function transformExpenseForMonth(Expenses $expense, Carbon $monthStart, Carbon $monthEnd): array
    {
        $regularPayments = $expense->spendIncomes
            ->filter(fn (SpendIncome $payment): bool => $payment->is_penalty !== true);
        $penaltyEntries = $expense->spendIncomes
            ->filter(fn (SpendIncome $payment): bool => $payment->is_penalty === true);

        $trackedLifetimePaid = round((float) $regularPayments->sum(
            fn (SpendIncome $payment): float => (float) $payment->amount
        ), 4);

        $legacyPaid = max(0, round((float) $expense->paid_amount - $trackedLifetimePaid, 4));
        $previousPayments = round(
            $legacyPaid + (float) $regularPayments
                ->filter(fn (SpendIncome $payment): bool => $payment->transaction_date !== null && $payment->transaction_date->lt($monthStart))
                ->sum(fn (SpendIncome $payment): float => (float) $payment->amount),
            4,
        );
        $currentMonthPayments = round((float) $regularPayments
            ->filter(fn (SpendIncome $payment): bool => $payment->transaction_date !== null
                && $payment->transaction_date->betweenIncluded($monthStart, $monthEnd))
            ->sum(fn (SpendIncome $payment): float => (float) $payment->amount), 4);
        $currentMonthPenaltyAmount = round((float) $penaltyEntries
            ->filter(fn (SpendIncome $payment): bool => $payment->transaction_date !== null
                && $payment->transaction_date->betweenIncluded($monthStart, $monthEnd))
            ->sum(fn (SpendIncome $payment): float => (float) $payment->amount), 4);
        $carryoverAmount = max(0, round($previousPayments - $this->calculateDueBeforeMonth($expense, $monthStart), 4));
        $previousBalanceAmount = max(0, round($this->calculateDueBeforeMonth($expense, $monthStart) - $previousPayments, 4));
        $paidThisMonth = round($currentMonthPayments + $carryoverAmount, 4);
        $currentMonthDueAmount = $this->isExpenseDueInMonth($expense, $monthStart, $monthEnd)
            ? round((float) $expense->total_amount, 4)
            : 0.0;
        $effectiveDueAmount = round($currentMonthDueAmount + $previousBalanceAmount + $currentMonthPenaltyAmount, 4);
        $remainingAmount = max(0, round($effectiveDueAmount - $paidThisMonth, 4));

        return [
            'id' => $expense->id,
            'name' => $expense->name,
            'type' => $expense->type,
            'category' => $expense->category,
            'reference_no' => $expense->reference_no,
            'date_start' => $expense->date_start,
            'date_end' => $expense->date_end,
            'payment_due' => $expense->payment_due,
            'total_amount' => $currentMonthDueAmount,
            'paid_amount' => round((float) $expense->paid_amount, 4),
            'penalty_amount' => $currentMonthPenaltyAmount,
            'previous_balance_amount' => $previousBalanceAmount,
            'effective_due_amount' => $effectiveDueAmount,
            'paid_this_month' => $paidThisMonth,
            'current_month_paid' => $currentMonthPayments,
            'carryover_amount' => $carryoverAmount,
            'remaining_amount' => $remainingAmount,
            'pay_in' => $expense->pay_in,
        ];
    }

    private function calculateDueBeforeMonth(Expenses $expense, Carbon $monthStart): float
    {
        $previousMonth = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $startMonth = Carbon::parse((string) $expense->date_start)->startOfMonth();

        if ($previousMonth->lt($startMonth)) {
            return 0;
        }

        if ($this->isOneTimeLoan($expense)) {
            return round((float) $expense->total_amount, 4);
        }

        $lastDueMonth = $previousMonth;

        if ($expense->date_end !== null) {
            $expenseEndMonth = Carbon::parse((string) $expense->date_end)->startOfMonth();

            if ($expenseEndMonth->lt($lastDueMonth)) {
                $lastDueMonth = $expenseEndMonth;
            }
        }

        if ($lastDueMonth->lt($startMonth)) {
            return 0;
        }

        $activeMonthCount = $startMonth->diffInMonths($lastDueMonth) + 1;

        return round($activeMonthCount * (float) $expense->total_amount, 4);
    }

    private function isExpenseVisibleInMonth(Expenses $expense, Carbon $monthStart, Carbon $monthEnd): bool
    {
        $expenseStart = Carbon::parse((string) $expense->date_start);

        if ($expenseStart->gt($monthEnd)) {
            return false;
        }

        if ($this->isExpenseDueInMonth($expense, $monthStart, $monthEnd)) {
            return true;
        }

        if ($expense->type !== 'loan') {
            return false;
        }

        return $this->remainingBalanceBeforeMonth($expense, $monthStart) > 0;
    }

    private function isExpenseDueInMonth(Expenses $expense, Carbon $monthStart, Carbon $monthEnd): bool
    {
        $expenseStart = Carbon::parse((string) $expense->date_start);

        if ($expenseStart->gt($monthEnd)) {
            return false;
        }

        if ($this->isOneTimeLoan($expense)) {
            return $expenseStart->startOfMonth()->equalTo($monthStart);
        }

        if ($expense->date_end === null) {
            return true;
        }

        return Carbon::parse((string) $expense->date_end)->gte($monthStart);
    }

    private function isOneTimeLoan(Expenses $expense): bool
    {
        return $expense->type === 'loan' && $expense->payment_mode === 'one_time';
    }

    private function remainingBalanceBeforeMonth(Expenses $expense, Carbon $monthStart): float
    {
        $regularPayments = $expense->spendIncomes
            ->filter(fn (SpendIncome $payment): bool => $payment->is_penalty !== true);

        $trackedLifetimePaid = round((float) $regularPayments->sum(
            fn (SpendIncome $payment): float => (float) $payment->amount
        ), 4);

        $legacyPaid = max(0, round((float) $expense->paid_amount - $trackedLifetimePaid, 4));
        $previousPayments = round(
            $legacyPaid + (float) $regularPayments
                ->filter(fn (SpendIncome $payment): bool => $payment->transaction_date !== null && $payment->transaction_date->lt($monthStart))
                ->sum(fn (SpendIncome $payment): float => (float) $payment->amount),
            4,
        );

        return max(0, round($this->calculateDueBeforeMonth($expense, $monthStart) - $previousPayments, 4));
    }
}
