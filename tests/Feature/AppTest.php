<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }
}

class SalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_sale(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::factory()->create(['current_stock' => 50]);

        $response = $this->actingAs($staff)->post('/sales', [
            'product_id' => $product->id,
            'quantity' => 5,
            'selling_price' => 100.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'staff_id' => $staff->id,
            'quantity' => 5,
            'total_amount' => 500.00,
            'status' => 'completed',
        ]);

        $product->refresh();
        $this->assertEquals(45, $product->current_stock);
    }

    public function test_cannot_sell_more_than_stock(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::factory()->create(['current_stock' => 3]);

        $response = $this->actingAs($staff)->post('/sales', [
            'product_id' => $product->id,
            'quantity' => 10,
            'selling_price' => 100.00,
        ]);

        $response->assertSessionHasErrors('quantity');
        $product->refresh();
        $this->assertEquals(3, $product->current_stock);
    }

    public function test_admin_can_cancel_sale(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $product = Product::factory()->create(['current_stock' => 40]);
        $sale = Sale::factory()->create([
            'product_id' => $product->id,
            'staff_id' => $admin->id,
            'quantity' => 10,
            'status' => 'completed',
        ]);
        $product->update(['current_stock' => 40]);

        $response = $this->actingAs($admin)->post("/sales/{$sale->id}/cancel");
        $response->assertRedirect();

        $sale->refresh();
        $this->assertEquals('cancelled', $sale->status);

        $product->refresh();
        $this->assertEquals(50, $product->current_stock);
    }
}

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->post('/inventory', [
            'name' => 'Test Wood',
            'current_stock' => 100,
            'low_stock_threshold' => 15,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Test Wood', 'current_stock' => 100]);
    }

    public function test_staff_cannot_create_product(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($staff)->post('/inventory', [
            'name' => 'Test Wood',
            'current_stock' => 100,
            'low_stock_threshold' => 15,
        ]);
        $response->assertStatus(403);
    }
}

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'TestDevice',
        ]);
        $response->assertStatus(200)->assertJsonStructure(['token', 'user']);
    }

    public function test_api_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/inventory');
        $response->assertStatus(401);
    }

    public function test_api_returns_inventory(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(5)->create(['created_by' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/inventory');
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }
}
