<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserApprovalRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserApprovalController extends Controller
{
    public function index(): Response
    {
        $this->authorizePermission('approvals.view');

        $users = User::query()
            ->role('EndUser')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_approved' => $user->is_approved,
                'created_at' => $user->created_at?->toDateTimeString(),
                'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
            ])
            ->values()
            ->all();

        return Inertia::render('approvals/Index', [
            'users' => $users,
        ]);
    }

    public function update(UpdateUserApprovalRequest $request, User $approval): RedirectResponse
    {
        $this->authorizePermission('approvals.update');

        abort_unless($approval->hasRole('EndUser'), 404);

        $approval->forceFill([
            'is_approved' => $request->boolean('is_approved'),
        ])->save();

        $status = $approval->is_approved ? 'approved' : 'set to pending';

        return redirect()
            ->route('approvals.index')
            ->with('success', "{$approval->name} has been {$status}.");
    }
}
