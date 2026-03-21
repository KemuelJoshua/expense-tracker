<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Accounts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AccountsController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePermission('accounts.view');

        $userId = Auth::id();
        $search = trim((string) $request->input('search', ''));

        $accounts = Accounts::query()
            ->where('user_id', $userId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $expenseQuery) use ($search): void {
                    $expenseQuery
                        ->where('account_name', 'like', "%{$search}%")
                        ->orWhere('account_type', 'like', "%{$search}%")
                        ->orWhere('balance', 'like', "%{$search}%")
                        ->orWhere('initial_balance', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('accounts/Index', [
            'accounts' => $accounts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorizePermission('accounts.create');

        return redirect()->route('accounts.index');
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        $this->authorizePermission('accounts.create');

        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        if ((bool) $validated['is_default']) {
            Accounts::query()
                ->where('user_id', $validated['user_id'])
                ->update(['is_default' => false]);
        }

        Accounts::create($validated);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(Accounts $account): JsonResponse
    {
        $this->authorizePermission('accounts.view');
        abort_unless($account->user_id === Auth::id(), 404);

        return response()->json($account);
    }

    public function edit(Accounts $account): JsonResponse
    {
        $this->authorizePermission('accounts.update');
        abort_unless($account->user_id === Auth::id(), 404);

        return response()->json($account);
    }

    public function update(AccountRequest $request, Accounts $account): RedirectResponse
    {
        $this->authorizePermission('accounts.update');
        abort_unless($account->user_id === Auth::id(), 404);

        $validated = $request->validated();

        if ((bool) $validated['is_default']) {
            Accounts::query()
                ->where('user_id', $account->user_id)
                ->whereKeyNot($account->id)
                ->update(['is_default' => false]);
        }

        $account->update($validated);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Accounts $account): RedirectResponse
    {
        $this->authorizePermission('accounts.delete');
        abort_unless($account->user_id === Auth::id(), 404);

        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
