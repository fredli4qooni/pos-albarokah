<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivableManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Customer $customer;

    private Sale $sale;

    private Receivable $receivable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin Keuangan',
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Pupuk Organik',
            'slug' => 'pupuk-organik',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PPK-001',
            'barcode' => '8998765432101',
            'name' => 'Pupuk NPK Mutiara 16-16-16 1kg',
            'unit' => 'Kg',
            'purchase_price' => 15000,
            'selling_price' => 20000,
            'stock' => 50,
            'min_stock' => 10,
        ]);

        $this->customer = Customer::create([
            'name' => 'Pak Haji Mansyur',
            'phone' => '081345678901',
            'address' => 'Desa Sinar Tani RT 03/01',
        ]);

        $this->sale = Sale::create([
            'invoice_no' => 'INV-20260914-9001',
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'payment_method' => 'credit',
            'subtotal' => 200000,
            'total' => 200000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        $this->sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 20000,
            'subtotal' => 200000,
        ]);

        $this->receivable = Receivable::create([
            'sale_id' => $this->sale->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 200000,
            'paid_amount' => 0,
            'remaining_balance' => 200000,
            'status' => 'belum_lunas',
        ]);
    }

    public function test_receivables_index_page_can_be_rendered_with_metrics(): void
    {
        $response = $this->actingAs($this->user)->get(route('receivables.index'));

        $response->assertOk();
        $response->assertSee('Pengelolaan Piutang Petani');
        $response->assertSee('Pak Haji Mansyur');
        $response->assertSee('INV-20260914-9001');
        $response->assertSee('200.000');
    }

    public function test_receivables_can_be_filtered_by_status(): void
    {
        // Create second sale and paid-off receivable
        $salePaid = Sale::create([
            'invoice_no' => 'INV-20260914-9002',
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'payment_method' => 'credit',
            'subtotal' => 100000,
            'total' => 100000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        Receivable::create([
            'sale_id' => $salePaid->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'remaining_balance' => 0,
            'status' => 'lunas',
        ]);

        // Filter belum_lunas
        $responseUnpaid = $this->actingAs($this->user)->get(route('receivables.index', ['status' => 'belum_lunas']));
        $responseUnpaid->assertOk();
        $responseUnpaid->assertSee('INV-20260914-9001');
        $responseUnpaid->assertDontSee('INV-20260914-9002');

        // Filter lunas
        $responsePaid = $this->actingAs($this->user)->get(route('receivables.index', ['status' => 'lunas']));
        $responsePaid->assertOk();
        $responsePaid->assertSee('INV-20260914-9002');
        $responsePaid->assertDontSee('INV-20260914-9001');
    }

    public function test_receivable_show_page_displays_details_and_ledger(): void
    {
        $response = $this->actingAs($this->user)->get(route('receivables.show', $this->receivable));

        $response->assertOk();
        $response->assertSee('Kartu Piutang Petani');
        $response->assertSee('Pak Haji Mansyur');
        $response->assertSee('INV-20260914-9001');
    }

    public function test_partial_payment_updates_balances_and_maintains_unpaid_status(): void
    {
        $payload = [
            'amount' => 75000,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Cicilan pertama dari panen semangka',
        ];

        $response = $this->actingAs($this->user)->post(route('receivables.payments.store', $this->receivable), $payload);

        $response->assertRedirect(route('receivables.show', $this->receivable));
        $response->assertSessionHas('success');

        $fresh = $this->receivable->fresh();
        $this->assertEquals(75000, (float) $fresh->paid_amount);
        $this->assertEquals(125000, (float) $fresh->remaining_balance);
        $this->assertEquals('belum_lunas', $fresh->status);

        $this->assertDatabaseHas('receivable_payments', [
            'receivable_id' => $this->receivable->id,
            'amount' => 75000,
            'notes' => 'Cicilan pertama dari panen semangka',
        ]);
    }

    public function test_full_payment_updates_balances_and_changes_status_to_lunas(): void
    {
        $payload = [
            'amount' => 200000, // Full payoff
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Pelunasan tunai penuh di toko',
        ];

        $response = $this->actingAs($this->user)->postJson(route('receivables.payments.store', $this->receivable), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'remaining_balance' => 0,
            'status' => 'lunas',
        ]);

        $fresh = $this->receivable->fresh();
        $this->assertEquals(200000, (float) $fresh->paid_amount);
        $this->assertEquals(0, (float) $fresh->remaining_balance);
        $this->assertEquals('lunas', $fresh->status);
    }

    public function test_payment_exceeding_remaining_balance_is_strictly_rejected(): void
    {
        $payload = [
            'amount' => 250000, // Exceeds 200.000 balance
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Mencoba bayar lebih',
        ];

        $response = $this->actingAs($this->user)->postJson(route('receivables.payments.store', $this->receivable), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);

        // Balances remain untouched
        $fresh = $this->receivable->fresh();
        $this->assertEquals(0, (float) $fresh->paid_amount);
        $this->assertEquals(200000, (float) $fresh->remaining_balance);
        $this->assertEquals('belum_lunas', $fresh->status);
        $this->assertDatabaseCount('receivable_payments', 0);
    }

    public function test_payment_with_zero_or_negative_amount_is_rejected(): void
    {
        $payload = [
            'amount' => 0,
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)->postJson(route('receivables.payments.store', $this->receivable), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_payment_on_already_paid_off_receivable_is_rejected(): void
    {
        // First payoff
        $this->receivable->recordPayment(200000, now()->format('Y-m-d H:i:s'));
        $this->assertEquals('lunas', $this->receivable->fresh()->status);

        // Try paying again
        $payload = [
            'amount' => 10000,
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)->postJson(route('receivables.payments.store', $this->receivable), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_payment_receipt_can_be_rendered(): void
    {
        $payment = $this->receivable->recordPayment(50000, now()->format('Y-m-d H:i:s'), 'Uang muka panen');

        $response = $this->actingAs($this->user)->get(route('receivables.payments.print', [$this->receivable, $payment]));

        $response->assertOk();
        $response->assertSee('BUKTI PEMBAYARAN PIUTANG');
        $response->assertSee('Pak Haji Mansyur');
        $response->assertSee('50.000');
    }
}
