<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Topping;
use App\Models\Inventory;
use App\Models\MenuRecipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Dashboard Admin Menu Management
     */
    public function index(Request $request): View
    {
        $user = $request->session()->get('auth_user');
        $branchId = $user['branch_id'] ?? null;

        $menus = Menu::with(['category', 'toppings'])->where('branch_id', $branchId)->latest()->get();
        $categories = Category::where('branch_id', $branchId)->orderBy('sort_order', 'asc')->get();
        $toppings = Topping::with(['inventory', 'menus'])->where('branch_id', $branchId)->get();
        $inventories = Inventory::all(); // Load raw materials for recipe config

        // Fetch recipes mapped by menu_id
        $recipes = MenuRecipe::with('inventory')
            ->get()
            ->groupBy('menu_id');

        return view('pages.dashboard.admin-menu', [
            'user' => $user,
            'menus' => $menus,
            'categories' => $categories,
            'toppings' => $toppings,
            'inventories' => $inventories,
            'recipes' => $recipes,
        ]);
    }

    /**
     * Tambah Menu Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:120'],
            'description' => ['nullable'],
            'price' => ['required', 'integer', 'min:0'],
            'toppings' => ['nullable', 'array'],
            'toppings.*' => ['integer', 'exists:toppings,id'],
        ]);

        $toppingIds = $data['toppings'] ?? [];
        unset($data['toppings']);

        $menu = Menu::create($data + [
            'branch_id' => $request->session()->get('auth_user.branch_id'),
            'is_available' => true,
            'image_path' => 'images/cafe/customer-page.svg'
        ]);
        $menu->toppings()->sync($toppingIds);

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Perbarui Menu
     */
    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:120'],
            'description' => ['nullable'],
            'price' => ['required', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'toppings' => ['nullable', 'array'],
            'toppings.*' => ['integer', 'exists:toppings,id'],
        ]);

        $toppingIds = $data['toppings'] ?? [];
        unset($data['toppings']);

        $menu->update($data);
        $menu->toppings()->sync($toppingIds);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Aktif / Nonaktifkan Produk (Toggle Ketersediaan)
     */
    public function toggleStatus(Menu $menu): RedirectResponse
    {
        $menu->update(['is_available' => ! $menu->is_available]);

        return back()->with('success', 'Status ketersediaan menu "' . $menu->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus Menu
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    /**
     * Tambah Kategori
     */
    public function category(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:120']
        ]);

        Category::create($data + [
            'branch_id' => $request->session()->get('auth_user.branch_id'),
            'is_active' => true
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Kelola Topping
     */
    public function storeTopping(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'inventory_id' => ['nullable', 'exists:inventories,id'],
            'inventory_quantity' => ['nullable', 'numeric', 'min:0'],
            'menu_ids' => ['nullable', 'array'],
            'menu_ids.*' => ['integer', 'exists:menus,id'],
        ]);

        $menuIds = $data['menu_ids'] ?? [];
        unset($data['menu_ids']);
        $data['inventory_quantity'] = $data['inventory_quantity'] ?? 1;

        $topping = Topping::create($data + [
            'branch_id' => $request->session()->get('auth_user.branch_id'),
            'is_available' => true,
            'inventory_quantity' => $data['inventory_quantity'] ?? 1,
        ]);
        $topping->menus()->sync($menuIds);

        return back()->with('success', 'Topping baru berhasil ditambahkan.');
    }

    public function updateTopping(Request $request, Topping $topping): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'inventory_id' => ['nullable', 'exists:inventories,id'],
            'inventory_quantity' => ['nullable', 'numeric', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'menu_ids' => ['nullable', 'array'],
            'menu_ids.*' => ['integer', 'exists:menus,id'],
        ]);

        $menuIds = $data['menu_ids'] ?? [];
        unset($data['menu_ids']);
        $data['inventory_quantity'] = $data['inventory_quantity'] ?? 1;

        $topping->update($data);
        $topping->menus()->sync($menuIds);

        return back()->with('success', 'Topping berhasil diperbarui.');
    }

    public function destroyTopping(Topping $topping): RedirectResponse
    {
        $topping->delete();

        return back()->with('success', 'Topping berhasil dihapus.');
    }

    /**
     * Mengatur Resep Menu
     */
    public function saveRecipe(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.inventory_id' => ['required', 'exists:inventories,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($menu, $data) {
            // Clear existing recipes
            MenuRecipe::where('menu_id', $menu->id)->delete();

            // Insert new recipe items
            foreach ($data['items'] as $item) {
                MenuRecipe::create([
                    'menu_id' => $menu->id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return back()->with('success', 'Resep menu "' . $menu->name . '" berhasil disimpan.');
    }
}
