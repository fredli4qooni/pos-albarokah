<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceivablePaymentRequest;
use App\Models\Customer;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceivableController extends Controller
{
    /**
     * Display a listing of customer receivables with KPI stats and filters.
     */
    public function index(Request $request)
    {
        $query = Receivable::with(['customer', 'sale'])->latest();

        // Filter search (Invoice No, Customer Name, Customer Phone)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_no', 'like', "%{$search}%");
                })->orWhereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        // Filter status
        if ($request->filled('status') && in_array($request->status, ['belum_lunas', 'lunas'])) {
            $query->where('status', $request->status);
        }

        // Filter customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $receivables = $query->paginate(15)->withQueryString();

        // KPI Summary Metrics
        $stats = [
            'total_active_debt' => (float) Receivable::where('status', 'belum_lunas')->sum('remaining_balance'),
            'total_paid' => (float) Receivable::sum('paid_amount'),
            'unpaid_count' => Receivable::where('status', 'belum_lunas')->count(),
            'paid_count' => Receivable::where('status', 'lunas')->count(),
        ];

        $customers = Customer::has('receivables')->orderBy('name')->get(['id', 'name']);

        return view('receivables.index', compact('receivables', 'stats', 'customers'));
    }

    /**
     * Display the specified receivable details and payment history.
     */
    public function show(Receivable $receivable)
    {
        $receivable->load([
            'customer',
            'sale.items.product',
            'payments' => fn ($q) => $q->latest('paid_at'),
        ]);

        return view('receivables.show', compact('receivable'));
    }

    /**
     * Store a payment against the customer receivable.
     * Enforces PRD §2.3 business rules:
     * - amount > 0
     * - amount <= remaining_balance
     * - automatic status update to 'lunas' when balance reaches 0.
     */
    public function storePayment(StoreReceivablePaymentRequest $request, Receivable $receivable)
    {
        if ($receivable->isPaidOff()) {
            throw ValidationException::withMessages([
                'amount' => 'Piutang ini sudah lunas. Tidak dapat menerima pembayaran tambahan.',
            ]);
        }

        $amount = (float) $request->amount;
        $remaining = (float) $receivable->remaining_balance;

        if ($amount > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran (Rp '.number_format($amount, 0, ',', '.').') melebihi sisa saldo tagihan (Rp '.number_format($remaining, 0, ',', '.').').',
            ]);
        }

        $payment = DB::transaction(function () use ($receivable, $amount, $request) {
            return $receivable->recordPayment(
                $amount,
                $request->paid_at,
                $request->notes
            );
        });

        $message = 'Pembayaran sebesar Rp '.number_format($amount, 0, ',', '.').' berhasil dicatat. Sisa saldo piutang: Rp '.number_format($receivable->fresh()->remaining_balance, 0, ',', '.');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'payment_id' => $payment->id,
                'remaining_balance' => (float) $receivable->remaining_balance,
                'status' => $receivable->status,
                'print_url' => route('receivables.payments.print', [$receivable, $payment]),
            ]);
        }

        return redirect()->route('receivables.show', $receivable)
            ->with('success', $message);
    }

    /**
     * Print payment receipt for a specific instalment / full payoff.
     */
    public function printPaymentReceipt(Receivable $receivable, ReceivablePayment $payment)
    {
        abort_if($payment->receivable_id !== $receivable->id, 404);

        $receivable->load(['customer', 'sale']);

        return view('receivables.receipt', compact('receivable', 'payment'));
    }
}
