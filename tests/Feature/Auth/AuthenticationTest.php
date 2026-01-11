<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // test owner can login
    public function test_owner_can_login(): void
    {
        // Arrange: create owner user
        $owner = User::factory()->create([
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'OWNER',
            'status' => 'ACTIVE',
        ]);

        // Act: attempt to login
        $response = $this->post('/login', [
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        // Assert: check if login was successful
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    // test staff can login
    public function test_staff_can_login(): void
    {
        // Arrange: create staff user
        $staff = User::factory()->create([
            'email' => 'stafff@test.com',
            'password' => bcrypt('password'),
            'role' => 'STAFF',
            'status' => 'ACTIVE',
        ]);

        // Act: attempt to login
        $response = $this->post('/login', [
            'email' => 'stafff@test.com',
            'password' => 'password',
        ]);

        // Assert: check if login was successful
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($staff);
    }

    // test login fail with wrong password
    public function test_login_fails_with_wrong_password(): void
    {
        // Arrange: create user
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('correct_password'),
        ]);

        // Act: attempt to login with wrong password
        $response = $this->post('/login', [
            'email' => 'user@test.com',
            'password' => 'wrong_password',
        ]);

        // Assert: check if login failed
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    // test inactive user cannot login
     public function test_inactive_user_is_logged_out(): void
    {
         /** @var User $user */
        $user = User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => bcrypt('password'),
            'role' => 'STAFF',
            'status' => 'INACTIVE',
        ]);

        // Login first
        $this->actingAs($user);

        // Try to access dashboard (middleware will check active status)
        $response = $this->get('/dashboard');

        // Should be redirected to login because inactive
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // test only owner can access owner routes
    public function test_only_owner_can_access_staff_routes(): void
    {
         /** @var User $staff */
        $staff = User::factory()->create([
            'role' => 'STAFF',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($staff);

        // Assuming route /staff is exist and protected by role:OWNER 
        $response = $this->get('/staff');

        // should get 403 forbidden
        $response->assertStatus(403);

    }

    /**
     * Test owner can access staff management routes.
     */
    public function test_owner_can_access_staff_routes(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create([
            'role' => 'OWNER',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($owner);

        // Assuming route /staff is exist and protected by role:OWNER 
        $response = $this->get('/staff');
       
        // Will be 200 OK or redirect, but NOT 403
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test user helper methods work correctly.
     */
    public function test_user_role_helper_methods(): void
    {
        $owner = User::factory()->create(['role' => 'OWNER']);
        $staff = User::factory()->create(['role' => 'STAFF']);

        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->isStaff());

        $this->assertTrue($staff->isStaff());
        $this->assertFalse($staff->isOwner());
    }

}