<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceivablePaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal pembayaran piutang wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka valid.',
            'amount.min' => 'Nominal pembayaran minimal Rp 1.',
            'paid_at.required' => 'Tanggal pembayaran wajib ditentukan.',
            'paid_at.date' => 'Format tanggal pembayaran tidak valid.',
            'notes.max' => 'Catatan pembayaran maksimal 255 karakter.',
        ];
    }
}
