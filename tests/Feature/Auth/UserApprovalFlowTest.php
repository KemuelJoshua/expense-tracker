<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unapproved_end_users_are_redirected_to_the_pending_approval_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->pendingApproval()->endUser()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('approval.pending'));
    }

    public function test_pending_approval_page_can_be_rendered_for_unapproved_end_users(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->pendingApproval()->endUser()->create();

        $this->actingAs($user)
            ->get(route('approval.pending'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/PendingApproval'),
            );
    }
}
