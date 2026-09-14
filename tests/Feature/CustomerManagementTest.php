<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_view_customers_list(): void
    {
        Customer::create([
            'name' => 'Petani Sukses',
            'phone' => '08123456789',
            'address' => 'Desa Makmur',
        ]);

        $response = $this->actingAs($this->admin)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Petani Sukses');
    }

    public function test_admin_can_create_customer(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => 'Pak Harun',
            'phone' => '085233445566',
            'address' => 'RT 03 Dusun Krajan',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('customers', [
            'name' => 'Pak Harun',
            'phone' => '085233445566',
        ]);
    }

    public function test_customer_can_be_created_via_json_request(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('customers.store'), [
            'name' => 'Petani Kilat',
            'phone' => '089900112233',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('customer.name', 'Petani Kilat');
    }

    public function test_admin_cannot_delete_customer_with_unpaid_debt(): void
    {
        $customer = Customer::create([
            'name' => 'Petani Berutang',
            'phone' => '0811223344',
        ]);

        $sale = Sale::create([
            'invoice_no' => 'INV-CR-DEBT-01',
            'user_id' => $this->admin->id,
            'customer_id' => $customer->id,
            'payment_method' => 'credit',
            'subtotal' => 200000,
            'total' => 200000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        Receivable::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'total_amount' => 200000,
            'paid_amount' => 0,
            'remaining_balance' => 200000,
            'status' => 'belum_lunas',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_admin_can_delete_customer_without_unpaid_debt(): void
    {
        $customer = Customer::create([
            'name' => 'Petani Bebas Utang',
            'phone' => '0811223355',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
