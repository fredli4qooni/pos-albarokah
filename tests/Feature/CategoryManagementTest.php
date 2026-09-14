<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_view_category_index(): void
    {
        Category::create(['name' => 'Pupuk Organik', 'slug' => 'pupuk-organik']);

        $response = $this->actingAs($this->admin)->get(route('categories.index'));

        $response->assertOk();
        $response->assertSee('Pupuk Organik');
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Pestisida Nabati',
            'description' => 'Obat alami pembasmi hama',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', [
            'name' => 'Pestisida Nabati',
            'slug' => 'pestisida-nabati',
        ]);
    }

    public function test_category_validation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create(['name' => 'Herbisida Gulma', 'slug' => 'herbisida-gulma']);

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category), [
            'name' => 'Herbisida Selektif',
            'description' => 'Deskripsi diperbarui',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Herbisida Selektif',
        ]);
    }

    public function test_admin_cannot_delete_category_with_products(): void
    {
        $category = Category::create(['name' => 'Benih', 'slug' => 'benih']);
        Product::create([
            'category_id' => $category->id,
            'code' => 'BNH-001',
            'name' => 'Benih Padi',
            'unit' => 'Sak',
            'purchase_price' => 50000,
            'selling_price' => 60000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::create(['name' => 'Kategori Kosong', 'slug' => 'kategori-kosong']);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
