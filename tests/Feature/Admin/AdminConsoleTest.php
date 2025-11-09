<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default admin user is created for tests as needed
    }

    public function test_non_admin_cannot_access_admin_console(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        User::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Admin Console');
        $response->assertViewHas('metrics');
        $response->assertViewHas('storage');
        $response->assertViewHas('users');
    }

    public function test_admin_can_update_user_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'timezone' => null,
            'currency' => null,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'is_admin' => '1',
        ];

        $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}", $payload);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('status');

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('UTC', $user->timezone);
        $this->assertSame('USD', $user->currency);
        $this->assertTrue($user->is_admin);
    }

    public function test_admin_can_trigger_password_reset(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/password-reset");

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }
}

