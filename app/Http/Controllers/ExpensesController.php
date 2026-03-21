<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expenses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExpensesController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePermission('expenses.view');

        $userId = Auth::id();
        $search = trim((string) $request->input('search', ''));

        $expenses = Expenses::query()
            ->where('created_by', $userId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $expenseQuery) use ($search): void {
                    $expenseQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('expenses/Index', [
            'expenses' => $expenses,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $this->authorizePermission('expenses.create');

        $validated = $this->normalizeExpensePayload($request->validated());

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')
                ->store('expenses', 'public');
        }

        $validated['created_by'] = Auth::id();

        Expenses::create($validated);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function update(ExpenseRequest $request, Expenses $expense): RedirectResponse
    {
        $this->authorizePermission('expenses.update');
        abort_unless($expense->created_by === Auth::id(), 404);

        $validated = $this->normalizeExpensePayload($request->validated());

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')
                ->store('expenses', 'public');
        } else {
            unset($validated['attachment']);
        }

        $expense->update($validated);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function show(Expenses $expense): JsonResponse
    {
        $this->authorizePermission('expenses.view');
        abort_unless($expense->created_by === Auth::id(), 404);

        return response()->json($expense);
    }

    public function edit(Expenses $expense): JsonResponse
    {
        $this->authorizePermission('expenses.update');
        abort_unless($expense->created_by === Auth::id(), 404);

        return response()->json($expense);
    }

    public function destroy(Expenses $expense): RedirectResponse
    {
        $this->authorizePermission('expenses.delete');
        abort_unless($expense->created_by === Auth::id(), 404);

        if ($expense->attachment) {
            Storage::disk('public')->delete($expense->attachment);
        }

        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeExpensePayload(array $validated): array
    {
        $validated['payment_mode'] = $validated['payment_mode'] ?? null;

        if ($validated['type'] === 'loan') {
            if ($validated['payment_mode'] === 'installment') {
                $validated['is_recurring'] = true;
                $validated['recurring_cycle'] = 'monthly';
            } else {
                $validated['is_recurring'] = false;
                $validated['recurring_cycle'] = null;
                $validated['date_end'] = null;
            }
        }

        if ($validated['type'] === 'utilities') {
            $validated['is_recurring'] = true;
            $validated['recurring_cycle'] = 'monthly';
            $validated['date_end'] = null;
        }

        if (! in_array($validated['type'], ['loan', 'utilities'], true)) {
            $validated['payment_mode'] = null;
        }

        if (($validated['is_recurring'] ?? false) === false || (int) ($validated['is_recurring'] ?? 0) === 0) {
            $validated['recurring_cycle'] = null;
        }

        return $validated;
    }
}
