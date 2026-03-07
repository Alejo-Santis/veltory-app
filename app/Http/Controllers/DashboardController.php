<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_products'    => Product::active()->count(),
            'total_value'       => Product::active()->selectRaw('SUM(stock_quantity * cost_price) as total')->value('total') ?? 0,
            'low_stock_count'   => Product::active()->lowStock()->count(),
            'out_of_stock_count' => Product::active()->outOfStock()->count(),
        ];

        return Inertia::render('Dashboard', compact('stats'));
    }
}
