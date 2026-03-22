<?php

namespace App\Http\Controllers;

use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\SpendIncome;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $this->authorizePermission('dashboard.view');

        $userId = (int) Auth::id();
        $today = now()->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();
        $trendStart = $monthStart->copy()->subMonths(5)->startOfMonth();
        $yearStart = $today->copy()->startOfYear();

        $accounts = Accounts::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('balance')
            ->get([
                'id',
                'account_name',
                'account_type',
                'balance',
                'is_active',
                'is_default',
                'bank_name',
                'currency',
            ]);

        $expenses = Expenses::query()
            ->where('created_by', $userId)
            ->get([
                'id',
                'name',
                'type',
                'total_amount',
                'paid_amount',
                'payment_due',
                'date_start',
                'date_end',
                'pay_in',
                'is_recurring',
            ]);

        $trendEntries = SpendIncome::query()
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$trendStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('transaction_date')
            ->get([
                'id',
                'entry_type',
                'transaction_date',
                'amount',
                'is_payroll',
                'payroll_month',
                'payroll_year',
            ]);

        $recentEntries = SpendIncome::query()
            ->with([
                'expense:id,name,type',
                'account:id,account_name,account_type',
            ])
            ->where('user_id', $userId)
            ->latest('transaction_date')
            ->latest('id')
            ->limit(8)
            ->get([
                'id',
                'entry_type',
                'transaction_date',
                'amount',
                'description',
                'expense_id',
                'account_id',
                'is_payroll',
                'payroll_month',
                'payroll_year',
            ]);

        $yearPayrollEntries = SpendIncome::query()
            ->where('user_id', $userId)
            ->where('entry_type', 'income')
            ->where('is_payroll', true)
            ->whereBetween('transaction_date', [$yearStart->toDateString(), $monthEnd->toDateString()])
            ->orderByDesc('transaction_date')
            ->get([
                'id',
                'transaction_date',
                'amount',
                'payroll_month',
                'payroll_year',
            ]);

        $currentMonthEntries = $trendEntries->filter(
            fn (SpendIncome $entry): bool => $entry->transaction_date !== null
                && $entry->transaction_date->betweenIncluded($monthStart, $monthEnd)
        );

        $previousMonthEntries = $trendEntries->filter(
            fn (SpendIncome $entry): bool => $entry->transaction_date !== null
                && $entry->transaction_date->betweenIncluded($previousMonthStart, $previousMonthEnd)
        );

        $currentMonthIncome = $this->sumEntries($currentMonthEntries, 'income');
        $currentMonthSpend = $this->sumEntries($currentMonthEntries, 'spend');
        $previousMonthIncome = $this->sumEntries($previousMonthEntries, 'income');
        $previousMonthSpend = $this->sumEntries($previousMonthEntries, 'spend');
        $currentMonthNet = round($currentMonthIncome - $currentMonthSpend, 2);
        $previousMonthNet = round($previousMonthIncome - $previousMonthSpend, 2);

        $cashOnHand = round((float) $accounts->sum('balance'), 2);
        $unpaidCommitments = round($expenses->sum(fn (Expenses $expense): float => $this->remainingBalance($expense)), 2);
        $activeAccounts = $accounts->where('is_active', true);
        $defaultAccount = $accounts->firstWhere('is_default', true);
        $activeMonthExpenses = $expenses->filter(
            fn (Expenses $expense): bool => $this->isExpenseActiveForWindow($expense, $monthStart, $monthEnd)
        );
        $monthlyObligation = round((float) $activeMonthExpenses->sum('total_amount'), 2);
        $monthlyPaid = round((float) $activeMonthExpenses->sum('paid_amount'), 2);
        $coverageRate = $monthlyObligation > 0
            ? round(min(($monthlyPaid / $monthlyObligation) * 100, 100), 1)
            : 0.0;

        $dueSoon = $expenses
            ->map(function (Expenses $expense) use ($today): ?array {
                $remainingBalance = $this->remainingBalance($expense);

                if ($remainingBalance <= 0) {
                    return null;
                }

                $nextDueDate = $this->resolveNextDueDate($expense, $today);

                if ($nextDueDate === null) {
                    return null;
                }

                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'type' => $expense->type,
                    'pay_in' => $expense->pay_in,
                    'payment_due' => $expense->payment_due,
                    'next_due_date' => $nextDueDate->toDateString(),
                    'days_until_due' => $today->diffInDays($nextDueDate, false),
                    'total_amount' => round((float) $expense->total_amount, 2),
                    'paid_amount' => round((float) $expense->paid_amount, 2),
                    'remaining_balance' => $remainingBalance,
                ];
            })
            ->filter()
            ->sortBy([
                ['days_until_due', 'asc'],
                ['remaining_balance', 'desc'],
            ])
            ->take(5)
            ->values()
            ->all();

        $topOutstanding = $expenses
            ->map(function (Expenses $expense): array {
                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'type' => $expense->type,
                    'pay_in' => $expense->pay_in,
                    'total_amount' => round((float) $expense->total_amount, 2),
                    'paid_amount' => round((float) $expense->paid_amount, 2),
                    'remaining_balance' => $this->remainingBalance($expense),
                ];
            })
            ->filter(fn (array $expense): bool => $expense['remaining_balance'] > 0)
            ->sortByDesc('remaining_balance')
            ->take(5)
            ->values()
            ->all();

        $topAccounts = $accounts
            ->sortByDesc('balance')
            ->take(5)
            ->map(fn (Accounts $account): array => [
                'id' => $account->id,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'bank_name' => $account->bank_name,
                'currency' => $account->currency,
                'balance' => round((float) $account->balance, 2),
                'is_active' => (bool) $account->is_active,
                'is_default' => (bool) $account->is_default,
            ])
            ->values()
            ->all();

        $accountMix = $accounts
            ->groupBy('account_type')
            ->map(function (Collection $group, string $accountType) use ($cashOnHand): array {
                $typeBalance = round((float) $group->sum('balance'), 2);

                return [
                    'type' => $accountType,
                    'count' => $group->count(),
                    'total_balance' => $typeBalance,
                    'share' => $cashOnHand > 0 ? round(($typeBalance / $cashOnHand) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('total_balance')
            ->values()
            ->all();

        $payrollYearTotal = round((float) $yearPayrollEntries->sum('amount'), 2);
        $latestPayroll = $yearPayrollEntries->first();

        return Inertia::render('Dashboard', [
            'analytics' => [
                'generated_at' => $today->format('M d, Y'),
                'focus_month' => [
                    'label' => $monthStart->format('F Y'),
                    'start' => $monthStart->toDateString(),
                    'end' => $monthEnd->toDateString(),
                ],
                'overview' => [
                    'cash_on_hand' => $cashOnHand,
                    'monthly_income' => $currentMonthIncome,
                    'monthly_spend' => $currentMonthSpend,
                    'monthly_net' => $currentMonthNet,
                    'unpaid_commitments' => $unpaidCommitments,
                    'active_accounts' => $activeAccounts->count(),
                    'total_accounts' => $accounts->count(),
                    'open_expenses' => $expenses->filter(
                        fn (Expenses $expense): bool => $this->remainingBalance($expense) > 0
                    )->count(),
                    'recurring_expenses' => $expenses->where('is_recurring', true)->count(),
                    'coverage_rate' => $coverageRate,
                    'changes' => [
                        'income' => $this->calculateChange($currentMonthIncome, $previousMonthIncome),
                        'spend' => $this->calculateChange($currentMonthSpend, $previousMonthSpend),
                        'net' => $this->calculateChange($currentMonthNet, $previousMonthNet),
                    ],
                ],
                'cash_flow' => [
                    'series' => $this->buildMonthlySeries($trendEntries, $monthStart),
                    'current_month_obligation' => $monthlyObligation,
                    'current_month_paid' => $monthlyPaid,
                ],
                'accounts' => [
                    'default_account_name' => $defaultAccount?->account_name,
                    'mix' => $accountMix,
                    'top_balances' => $topAccounts,
                ],
                'expenses' => [
                    'due_soon' => $dueSoon,
                    'top_outstanding' => $topOutstanding,
                ],
                'activity' => [
                    'recent_entries' => $recentEntries
                        ->map(fn (SpendIncome $entry): array => [
                            'id' => $entry->id,
                            'entry_type' => $entry->entry_type,
                            'transaction_date' => $entry->transaction_date?->toDateString(),
                            'amount' => round((float) $entry->amount, 2),
                            'description' => $entry->description,
                            'source_label' => $this->sourceLabel($entry),
                            'detail_label' => $this->detailLabel($entry),
                            'is_payroll' => (bool) $entry->is_payroll,
                        ])
                        ->values()
                        ->all(),
                    'payroll' => [
                        'year_total' => $payrollYearTotal,
                        'entries' => $yearPayrollEntries->count(),
                        'latest_label' => $latestPayroll === null
                            ? null
                            : $latestPayroll->transaction_date?->format('F Y'),
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  Collection<int, SpendIncome>  $entries
     * @return array<int, array<string, float|string>>
     */
    private function buildMonthlySeries(Collection $entries, CarbonInterface $monthStart): array
    {
        $entryGroups = $entries->groupBy(
            fn (SpendIncome $entry): string => $entry->transaction_date?->format('Y-m') ?? 'unknown'
        );

        $series = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $bucketMonth = $monthStart->copy()->subMonths($offset);
            $bucketKey = $bucketMonth->format('Y-m');
            /** @var Collection<int, SpendIncome> $bucketEntries */
            $bucketEntries = $entryGroups->get($bucketKey, collect());
            $income = $this->sumEntries($bucketEntries, 'income');
            $spend = $this->sumEntries($bucketEntries, 'spend');

            $series[] = [
                'month' => $bucketMonth->format('M'),
                'label' => $bucketMonth->format('M Y'),
                'income' => $income,
                'spend' => $spend,
                'net' => round($income - $spend, 2),
            ];
        }

        return $series;
    }

    /**
     * @param  Collection<int, SpendIncome>  $entries
     */
    private function sumEntries(Collection $entries, string $entryType): float
    {
        return round((float) $entries
            ->where('entry_type', $entryType)
            ->sum('amount'), 2);
    }

    private function remainingBalance(Expenses $expense): float
    {
        return round(max(0, (float) $expense->total_amount - (float) $expense->paid_amount), 2);
    }

    private function isExpenseActiveForWindow(
        Expenses $expense,
        CarbonInterface $windowStart,
        CarbonInterface $windowEnd
    ): bool {
        $startDate = Carbon::parse($expense->date_start)->startOfDay();
        $endDate = $expense->date_end === null
            ? null
            : Carbon::parse($expense->date_end)->endOfDay();

        return $startDate->lte($windowEnd)
            && ($endDate === null || $endDate->gte($windowStart));
    }

    private function resolveNextDueDate(Expenses $expense, CarbonInterface $today): ?CarbonInterface
    {
        if ($expense->payment_due === null) {
            return null;
        }

        $anchorDate = Carbon::parse($expense->date_start)->startOfDay();

        if ($anchorDate->lt($today)) {
            $anchorDate = $today->copy();
        }

        $candidate = $anchorDate->copy()->day(min((int) $expense->payment_due, $anchorDate->daysInMonth));

        if ($candidate->lt($anchorDate)) {
            $candidate = $candidate->addMonthNoOverflow()->day(
                min((int) $expense->payment_due, $candidate->daysInMonth)
            );
        }

        if ($expense->date_end !== null) {
            $endDate = Carbon::parse($expense->date_end)->endOfDay();

            if ($candidate->gt($endDate)) {
                return null;
            }
        }

        return $candidate->startOfDay();
    }

    private function calculateChange(float $current, float $previous): ?float
    {
        if ($previous === 0.0) {
            return $current === 0.0 ? 0.0 : null;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    private function sourceLabel(SpendIncome $entry): string
    {
        if ($entry->entry_type === 'spend' && $entry->expense !== null) {
            return $entry->expense->name;
        }

        if ($entry->entry_type === 'income' && $entry->account !== null) {
            return $entry->account->account_name;
        }

        return $entry->is_payroll ? 'Payroll income' : 'Manual entry';
    }

    private function detailLabel(SpendIncome $entry): string
    {
        if ($entry->entry_type === 'spend' && $entry->expense !== null) {
            return ucfirst((string) $entry->expense->type).' expense';
        }

        if ($entry->entry_type === 'income' && $entry->account !== null) {
            return ucfirst((string) $entry->account->account_type).' account';
        }

        if ($entry->description !== null && $entry->description !== '') {
            return $entry->description;
        }

        return 'No additional details';
    }
}
