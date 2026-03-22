<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpendIncomeRequest;
use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\SpendIncome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SpendIncomeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePermission('spend_income.view');

        $userId = Auth::id();
        $search = trim((string) $request->input('search', ''));

        $spendIncomes = SpendIncome::query()
            ->with([
                'expense:id,name,type,created_by',
                'account:id,account_name,account_type,user_id',
            ])
            ->where('user_id', $userId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $entryQuery) use ($search): void {
                    $entryQuery
                        ->where('entry_type', 'like', "%{$search}%")
                        ->orWhere('transaction_date', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('expense', function (Builder $expenseQuery) use ($search): void {
                            $expenseQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('account', function (Builder $accountQuery) use ($search): void {
                            $accountQuery->where('account_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (SpendIncome $entry): array => $this->transformEntry($entry));

        return Inertia::render('spend-income/Index', [
            'spendIncomes' => $spendIncomes,
            'filters' => [
                'search' => $search,
            ],
            'options' => [
                'expenses' => Expenses::query()
                    ->where('created_by', $userId)
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Expenses $expense): array => [
                        'id' => $expense->id,
                        'name' => $expense->name,
                        'type' => $expense->type,
                    ])
                    ->values()
                    ->all(),
                'accounts' => Accounts::query()
                    ->where('user_id', $userId)
                    ->orderBy('account_name')
                    ->get()
                    ->map(fn (Accounts $account): array => [
                        'id' => $account->id,
                        'account_name' => $account->account_name,
                        'account_type' => $account->account_type,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorizePermission('spend_income.create');

        return redirect()->route('spend-income.index');
    }

    public function store(SpendIncomeRequest $request): RedirectResponse
    {
        $this->authorizePermission('spend_income.create');

        $payload = $this->normalizePayload($request);
        $payload['user_id'] = Auth::id();

        DB::transaction(function () use ($payload): void {
            $entry = SpendIncome::create($payload);

            $this->applyLinkedAmounts($entry);
        });

        return redirect()
            ->route('spend-income.index')
            ->with('success', 'Spend / income entry created successfully.');
    }

    public function show(SpendIncome $spendIncome): JsonResponse
    {
        $this->authorizePermission('spend_income.view');
        abort_unless($spendIncome->user_id === Auth::id(), 404);

        $spendIncome->loadMissing([
            'expense:id,name,type,created_by',
            'account:id,account_name,account_type,user_id',
        ]);

        return response()->json($this->transformEntry($spendIncome));
    }

    public function edit(SpendIncome $spendIncome): JsonResponse
    {
        $this->authorizePermission('spend_income.update');
        abort_unless($spendIncome->user_id === Auth::id(), 404);

        return response()->json([
            ...$spendIncome->toArray(),
            'transaction_date' => $spendIncome->transaction_date?->toDateString(),
            'expense_reference' => $spendIncome->expense_id === null ? 'others' : (string) $spendIncome->expense_id,
            'account_reference' => $spendIncome->account_id === null ? 'others' : (string) $spendIncome->account_id,
        ]);
    }

    public function update(SpendIncomeRequest $request, SpendIncome $spendIncome): RedirectResponse
    {
        $this->authorizePermission('spend_income.update');
        abort_unless($spendIncome->user_id === Auth::id(), 404);

        $payload = $this->normalizePayload($request);

        DB::transaction(function () use ($spendIncome, $payload): void {
            $this->revertLinkedAmounts($spendIncome);

            $spendIncome->update($payload);
            $spendIncome->refresh();

            $this->applyLinkedAmounts($spendIncome);
        });

        return redirect()
            ->route('spend-income.index')
            ->with('success', 'Spend / income entry updated successfully.');
    }

    public function destroy(SpendIncome $spendIncome): RedirectResponse
    {
        $this->authorizePermission('spend_income.delete');
        abort_unless($spendIncome->user_id === Auth::id(), 404);

        DB::transaction(function () use ($spendIncome): void {
            $this->revertLinkedAmounts($spendIncome);
            $spendIncome->delete();
        });

        return redirect()
            ->route('spend-income.index')
            ->with('success', 'Spend / income entry deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(SpendIncomeRequest $request): array
    {
        $validated = $request->validated();
        $entryType = (string) $validated['entry_type'];
        $isPayroll = $entryType === 'income' ? $request->boolean('is_payroll') : false;

        return [
            'entry_type' => $entryType,
            'transaction_date' => $validated['transaction_date'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'expense_id' => $entryType === 'spend' && ($validated['expense_reference'] ?? 'others') !== 'others'
                ? (int) $validated['expense_reference']
                : null,
            'account_id' => $entryType === 'income' && ($validated['account_reference'] ?? 'others') !== 'others'
                ? (int) $validated['account_reference']
                : null,
            'is_payroll' => $isPayroll,
            'payroll_month' => $isPayroll ? (int) $validated['payroll_month'] : null,
            'payroll_year' => $isPayroll ? (int) $validated['payroll_year'] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformEntry(SpendIncome $entry): array
    {
        return [
            'id' => $entry->id,
            'entry_type' => $entry->entry_type,
            'transaction_date' => $entry->transaction_date?->toDateString(),
            'amount' => (float) $entry->amount,
            'description' => $entry->description,
            'expense_id' => $entry->expense_id,
            'account_id' => $entry->account_id,
            'is_payroll' => $entry->is_payroll,
            'payroll_month' => $entry->payroll_month,
            'payroll_year' => $entry->payroll_year,
            'expense' => $entry->expense === null ? null : [
                'id' => $entry->expense->id,
                'name' => $entry->expense->name,
                'type' => $entry->expense->type,
            ],
            'account' => $entry->account === null ? null : [
                'id' => $entry->account->id,
                'account_name' => $entry->account->account_name,
                'account_type' => $entry->account->account_type,
            ],
        ];
    }

    private function applyLinkedAmounts(SpendIncome $entry): void
    {
        $amount = (float) $entry->amount;

        if ($entry->entry_type === 'spend' && $entry->expense_id !== null) {
            $expense = Expenses::query()
                ->whereKey($entry->expense_id)
                ->where('created_by', $entry->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $expense->update([
                'paid_amount' => max(0, round((float) $expense->paid_amount + $amount, 4)),
            ]);

            return;
        }

        if ($entry->entry_type === 'income' && $entry->account_id !== null) {
            $account = Accounts::query()
                ->whereKey($entry->account_id)
                ->where('user_id', $entry->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $account->update([
                'balance' => max(0, round((float) $account->balance + $amount, 4)),
            ]);
        }
    }

    private function revertLinkedAmounts(SpendIncome $entry): void
    {
        $amount = (float) $entry->amount;

        if ($entry->entry_type === 'spend' && $entry->expense_id !== null) {
            $expense = Expenses::query()
                ->whereKey($entry->expense_id)
                ->where('created_by', $entry->user_id)
                ->lockForUpdate()
                ->first();

            if ($expense !== null) {
                $expense->update([
                    'paid_amount' => max(0, round((float) $expense->paid_amount - $amount, 4)),
                ]);
            }

            return;
        }

        if ($entry->entry_type === 'income' && $entry->account_id !== null) {
            $account = Accounts::query()
                ->whereKey($entry->account_id)
                ->where('user_id', $entry->user_id)
                ->lockForUpdate()
                ->first();

            if ($account !== null) {
                $account->update([
                    'balance' => max(0, round((float) $account->balance - $amount, 4)),
                ]);
            }
        }
    }
}
