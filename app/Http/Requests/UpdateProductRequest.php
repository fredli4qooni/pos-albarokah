<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($productId)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'code.required' => 'Kode SKU produk wajib diisi.',
            'code.unique' => 'Kode SKU sudah digunakan produk lain.',
            'barcode.unique' => 'Barcode sudah terdaftar pada produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'unit.required' => 'Satuan produk wajib diisi.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.min' => 'Harga beli tidak boleh bernilai negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.min' => 'Harga jual tidak boleh bernilai negatif.',
            'stock.required' => 'Jumlah stok awal wajib diisi.',
            'stock.min' => 'Stok tidak boleh bernilai negatif.',
            'min_stock.required' => 'Ambang stok minimum wajib diisi.',
            'min_stock.min' => 'Stok minimum tidak boleh bernilai negatif.',
        ];
    }
}
