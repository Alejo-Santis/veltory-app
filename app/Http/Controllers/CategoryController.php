<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('products')
            ->with('children:id,parent_id,name,color,icon,is_active,sort_order')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', compact('categories'));
    }

    public function show(Category $category): Response
    {
        $category->load(['parent', 'children']);

        $products = $category->products()
            ->with(['unit', 'coverImage'])
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(20);

        $products->getCollection()->transform(function ($p) {
            if ($p->coverImage) {
                $p->coverImage->url = \Illuminate\Support\Facades\Storage::disk('public')->url($p->coverImage->path);
            }
            return $p;
        });

        return Inertia::render('Categories/Show', compact('category', 'products'));
    }

    public function create(): Response
    {
        $parents = Category::whereNull('parent_id')
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return Inertia::render('Categories/Create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $base = $data['slug'];
        $count = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = "{$base}-{$count}";
            $count++;
        }

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', "Categoría \"{$data['name']}\" creada correctamente.");
    }

    public function edit(Category $category): Response
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return Inertia::render('Categories/Edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        if ($data['name'] !== $category->name) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $count = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = "{$base}-{$count}";
                $count++;
            }
            $data['slug'] = $slug;
        }

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', "Categoría \"{$category->name}\" actualizada.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene productos asignados.');
        }

        if ($category->children()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene subcategorías.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Categoría \"{$name}\" eliminada.");
    }
}
