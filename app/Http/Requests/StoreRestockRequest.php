<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRestockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'restock_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom error messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk pertanian wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak ditemukan dalam sistem.',
            'quantity.required' => 'Jumlah kuantitas barang masuk wajib diisi.',
            'quantity.integer' => 'Jumlah kuantitas harus berupa bilangan bulat.',
            'quantity.min' => 'Jumlah kuantitas barang masuk minimal 1.',
            'restock_date.required' => 'Tanggal penerimaan barang masuk wajib ditentukan.',
            'restock_date.date' => 'Format tanggal penerimaan barang tidak valid.',
            'notes.max' => 'Catatan penerimaan maksimal 255 karakter.',
        ];
    }
}
