<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expenses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ExpensesController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePermission('expenses.view');

        $search = trim((string) $request->input('search', ''));

        $expenses = Expenses::query()
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

        $validated = $request->validated();

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

        $validated = $request->validated();

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

        return response()->json($expense);
    }

    public function edit(Expenses $expense): JsonResponse
    {
        $this->authorizePermission('expenses.update');

        return response()->json($expense);
    }
}
