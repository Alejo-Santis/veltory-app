<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Enums\TypeStockMovement;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['unit', 'supplier', 'categories', 'coverImage'])
            ->withCount('categories');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%")
                  ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->input('low_stock')) {
            $query->lowStock();
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        // Añadir URL pública de imagen de portada
        $products->getCollection()->transform(function ($product) {
            if ($product->coverImage) {
                $product->coverImage->url = Storage::disk('public')->url($product->coverImage->path);
            }
            return $product;
        });

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters'  => $request->only(['search', 'status', 'low_stock']),
            'statuses'       => collect(ProductStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'movementTypes'  => collect(TypeStockMovement::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
            'warehouses'     => Warehouse::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'units'      => Unit::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'suppliers'  => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->with('children')->root()->orderBy('name')->get(['id', 'name']),
            'statuses'   => collect(ProductStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Generar slug único
        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        $data['slug'] = $slug;

        // Generar SKU si no se proporcionó
        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(Str::random(3)) . '-' . strtoupper(Str::slug(substr($data['name'], 0, 6))) . '-' . rand(100, 999);
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $product = Product::create($data);

        if (!empty($categories)) {
            $product->categories()->sync($categories);
        }

        return redirect("/products/{$product->uuid}/edit")->with('success', "Producto \"{$product->name}\" creado. Ahora puedes agregar imágenes.");
    }

    public function show(Product $product)
    {
        $product->load([
            'unit', 'supplier', 'categories',
            'images' => fn ($q) => $q->orderBy('sort_order'),
            'createdBy', 'updatedBy',
        ]);

        $images = $product->images->map(fn ($img) => [
            'id'       => $img->id,
            'uuid'     => $img->uuid,
            'url'      => Storage::disk('public')->url($img->path),
            'alt_text' => $img->alt_text,
            'is_cover' => $img->is_cover,
        ]);

        $recentMovements = $product->stockMovements()
            ->with(['user', 'warehouse'])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Products/Show', [
            'product'          => array_merge($product->toArray(), [
                'status_label'  => $product->status->label(),
                'status_value'  => $product->status->value,
                'images'        => $images,
            ]),
            'recentMovements'  => $recentMovements,
        ]);
    }

    public function edit(Product $product)
    {
        $product->load(['categories', 'images' => fn ($q) => $q->orderBy('sort_order')]);

        $images = $product->images->map(fn ($img) => [
            'id'       => $img->id,
            'uuid'     => $img->uuid,
            'url'      => Storage::disk('public')->url($img->path),
            'alt_text' => $img->alt_text,
            'is_cover' => $img->is_cover,
        ]);

        return Inertia::render('Products/Edit', [
            'product'    => array_merge($product->toArray(), [
                'category_ids' => $product->categories->pluck('id'),
                'status'       => $product->status->value,
                'images'       => $images,
            ]),
            'units'      => Unit::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'suppliers'  => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->with('children')->root()->orderBy('name')->get(['id', 'name']),
            'statuses'   => collect(ProductStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Regenerar slug solo si cambió el nombre
        if ($data['name'] !== $product->name) {
            $slug = Str::slug($data['name']);
            $base = $slug;
            $i = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        $data['updated_by'] = auth()->id();

        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $product->update($data);
        $product->categories()->sync($categories);

        return redirect('/products')->with('success', "Producto \"{$product->name}\" actualizado correctamente.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect('/products')->with('success', "Producto \"{$name}\" eliminado correctamente.");
    }
}
