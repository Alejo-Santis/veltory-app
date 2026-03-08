<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'images.*.image'  => 'Solo se permiten imágenes.',
            'images.*.max'    => 'Cada imagen no puede superar 4 MB.',
            'images.*.mimes'  => 'Formato permitido: jpg, jpeg, png, webp.',
        ]);

        $nextOrder = $product->images()->max('sort_order') + 1;
        $hasCover  = $product->images()->where('is_cover', true)->exists();

        foreach ($request->file('images') as $i => $file) {
            $path = $file->store("products/{$product->uuid}", 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'alt_text'   => $product->name,
                'sort_order' => $nextOrder + $i,
                'is_cover'   => ! $hasCover && $i === 0,
            ]);

            if (! $hasCover && $i === 0) {
                $hasCover = true;
            }
        }

        return back()->with('success', 'Imágenes subidas correctamente.');
    }

    public function destroy(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $wasCover = $image->is_cover;
        $image->delete();

        // Si era la portada, asignar la siguiente
        if ($wasCover) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_cover' => true]);
        }

        return back()->with('success', 'Imagen eliminada.');
    }

    public function setCover(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_cover' => false]);
        $image->update(['is_cover' => true]);

        return back()->with('success', 'Imagen de portada actualizada.');
    }
}
