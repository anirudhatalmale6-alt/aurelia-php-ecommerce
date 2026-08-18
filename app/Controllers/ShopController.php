<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;

/** Storefront: home, catalogue, product detail, search. */
class ShopController extends Controller
{
    public function home(Request $request): void
    {
        $this->view('shop/home', [
            'title'      => 'Home',
            'featured'   => Product::featured(8),
            'categories' => Category::all(),
            'latest'     => Product::browse(['sort' => 'newest'], 1, 4)['rows'],
        ]);
    }

    public function catalogue(Request $request): void
    {
        $filters = [
            'category'  => $request->input('category', ''),
            'q'         => $request->input('q', ''),
            'sort'      => $request->input('sort', ''),
            'max_price' => $request->input('max_price', ''),
            'in_stock'  => $request->has('in_stock') ? 1 : 0,
        ];

        $result   = Product::browse($filters, $request->int('page', 1), 12);
        $category = $filters['category'] ? Category::findBySlug($filters['category']) : null;

        $this->view('shop/catalogue', [
            'title'      => $category['name'] ?? ($filters['q'] ? 'Search results' : 'Shop all'),
            'result'     => $result,
            'filters'    => $filters,
            'category'   => $category,
            'categories' => Category::all(),
        ]);
    }

    public function product(Request $request, string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Product not found']);
            return;
        }

        $this->view('shop/product', [
            'title'      => $product['name'],
            'product'    => $product,
            'related'    => Product::related($product, 4),
            'categories' => Category::all(),
        ]);
    }

    /** Typeahead endpoint for the header search box. */
    public function suggest(Request $request): void
    {
        $q = $request->input('q', '');
        if (mb_strlen($q) < 2) {
            $this->json(['results' => []]);
            return;
        }
        $rows = Product::browse(['q' => $q], 1, 6)['rows'];
        $this->json([
            'results' => array_map(fn($p) => [
                'name'  => $p['name'],
                'slug'  => $p['slug'],
                'price' => money(Product::price($p)),
                'image' => media($p['image']),
            ], $rows),
        ]);
    }
}
