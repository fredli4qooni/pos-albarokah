<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $customers = Customer::with(['receivables' => function ($q) {
            $q->where('status', 'belum_lunas');
        }])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalCustomers = Customer::count();
        $totalDebtors = Customer::whereHas('receivables', fn ($q) => $q->where('status', 'belum_lunas'))->count();

        return view('customers.index', compact('customers', 'search', 'totalCustomers', 'totalDebtors'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $customer = Customer::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pelanggan '{$customer->name}' berhasil ditambahkan.",
                'customer' => $customer,
            ], 201);
        }

        return redirect()->route('customers.index')
            ->with('success', "Data pelanggan '{$customer->name}' berhasil disimpan.");
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($q) => $q->latest()->take(10),
            'receivables.payments',
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', "Data pelanggan '{$customer->name}' berhasil diperbarui.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        // Prevent deleting customer with unpaid receivables
        $unpaidReceivablesCount = $customer->receivables()->where('status', 'belum_lunas')->count();
        if ($unpaidReceivablesCount > 0) {
            return redirect()->route('customers.index')
                ->with('error', "Pelanggan '{$customer->name}' tidak dapat dihapus karena masih memiliki tagihan piutang belum lunas.");
        }

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Data pelanggan '{$name}' berhasil dihapus.");
    }
}
