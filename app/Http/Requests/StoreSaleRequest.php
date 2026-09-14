<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
            'payment_method' => ['required', 'in:cash,credit'],
            'customer_id' => [
                'nullable',
                'exists:customers,id',
                'required_if:payment_method,credit',
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran harus berupa Tunai atau Kredit.',
            'customer_id.required_if' => 'Pelanggan petani wajib dipilih apabila metode pembayaran adalah kredit (piutang).',
            'customer_id.exists' => 'Data pelanggan yang dipilih tidak ditemukan.',
            'items.required' => 'Keranjang transaksi tidak boleh kosong.',
            'items.min' => 'Pilih minimal 1 produk untuk melakukan transaksi.',
            'items.*.product_id.required' => 'Item produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak valid atau tidak ditemukan.',
            'items.*.quantity.required' => 'Kuantitas barang wajib diisi.',
            'items.*.quantity.min' => 'Kuantitas barang minimal 1.',
        ];
    }
}
