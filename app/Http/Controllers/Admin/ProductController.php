<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Store a newly created product in database.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $branchId = $request->session()->get('auth_user.branch_id') ?: (\App\Models\Branch::first()?->id);

        Product::create([
            'branch_id' => $branchId,
            'name' => $data['name'],
            'barcode' => $data['barcode'],
            'price' => $data['price'],
            'description' => $data['description'],
            'is_available' => true,
            'is_featured' => false,
        ]);

        return back()->with('success', 'Barang "' . $data['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Update the specified product in database.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode,' . $product->id],
            'price' => ['required', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $product->update($data);

        return back()->with('success', 'Barang "' . $product->name . '" berhasil diperbarui.');
    }

    /**
     * Toggle the availability status of the specified product.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update([
            'is_available' => !$product->is_available
        ]);

        return back()->with('success', 'Status ketersediaan barang "' . $product->name . '" berhasil diperbarui.');
    }

    /**
     * Remove the specified product from database.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return back()->with('success', 'Barang "' . $name . '" berhasil dihapus.');
    }
}
