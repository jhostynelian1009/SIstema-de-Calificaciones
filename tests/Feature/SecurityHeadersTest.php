<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    #[Test]
    public function security_headers_are_attached_to_all_web_responses()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    #[Test]
    public function guest_cannot_access_protected_role_routes()
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    #[Test]
    public function role_cross_access_is_forbidden()
    {
        $student = User::where('role', UserRole::Student)->firstOrFail();
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();

        // Student trying to access Admin or Teacher routes
        $this->actingAs($student)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($student)->get(route('teacher.dashboard'))->assertStatus(403);

        // Teacher trying to access Admin or Student routes
        $this->actingAs($teacher)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($teacher)->get(route('student.dashboard'))->assertStatus(403);
    }
}
