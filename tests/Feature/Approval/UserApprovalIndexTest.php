<?php

namespace Tests\Feature\Approval;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserApprovalIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_end_user_approvals(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $approvedEndUser = User::factory()->endUser()->create([
            'name' => 'Approved User',
            'email' => 'approved@example.com',
            'is_approved' => true,
        ]);
        $pendingEndUser = User::factory()->pendingApproval()->endUser()->create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
        ]);
        User::factory()->admin()->create([
            'name' => 'Another Admin',
            'email' => 'another-admin@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('approvals.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('approvals/Index')
                ->has('users', 2)
                ->where('users.0.name', 'Approved User')
                ->where('users.0.is_approved', true)
                ->where('users.1.name', 'Pending User')
                ->where('users.1.is_approved', false),
            );
    }

    public function test_admin_can_update_an_end_user_approval_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $endUser = User::factory()->pendingApproval()->endUser()->create();

        $this->actingAs($admin)
            ->put(route('approvals.update', $endUser), [
                'is_approved' => true,
            ])
            ->assertRedirect(route('approvals.index'));

        $this->assertTrue($endUser->fresh()->is_approved);
    }

    public function test_admin_cannot_update_a_non_end_user_approval_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $anotherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('approvals.update', $anotherAdmin), [
                'is_approved' => false,
            ])
            ->assertNotFound();
    }
}
