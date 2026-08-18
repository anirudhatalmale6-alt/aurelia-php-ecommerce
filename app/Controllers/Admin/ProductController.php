<?php
namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Uploader;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;

/** Inventory CRUD. */
class ProductController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/products/index', [
            'title'    => 'Products',
            'products' => Product::adminList((string) $request->input('q', '')),
            'search'   => (string) $request->input('q', ''),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/products/form', [
            'title'      => 'Add product',
            'product'    => null,
            'categories' => Category::all(),
        ], 'layouts/admin');
    }

    public function edit(Request $request, string $id): void
    {
        $product = Product::find((int) $id);
        if (!$product) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Product not found'], 'layouts/admin');
            return;
        }
        $this->view('admin/products/form', [
            'title'      => 'Edit ' . $product['name'],
            'product'    => $product,
            'categories' => Category::all(),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        Csrf::verify();
        $data = $this->validated($request, null);
        if ($data === null) {
            redirect('/admin/products/create');
        }

        $id = Product::create($data);
        Flash::add('success', 'Product "' . $data['name'] . '" is live on the storefront.');
        redirect('/admin/products/' . $id . '/edit');
    }

    public function update(Request $request, string $id): void
    {
        Csrf::verify();
        $product = Product::find((int) $id);
        if (!$product) {
            Flash::add('error', 'Product not found.');
            redirect('/admin/products');
        }

        $data = $this->validated($request, $product);
        if ($data === null) {
            redirect('/admin/products/' . $id . '/edit');
        }

        Product::update((int) $id, $data);
        Flash::add('success', 'Changes saved.');
        redirect('/admin/products/' . $id . '/edit');
    }

    public function destroy(Request $request, string $id): void
    {
        Csrf::verify();
        $product = Product::find((int) $id);
        if ($product) {
            Uploader::delete($product['image']);
            Product::delete((int) $id);
            Flash::add('success', 'Product deleted.');
        }
        redirect('/admin/products');
    }

    /** Shared validation + image handling for store/update. */
    private function validated(Request $request, ?array $existing): ?array
    {
        $v = new Validator($request->all());
        $v->required('name')->max('name', 180)
          ->required('price')->numeric('price')->positive('price')
          ->numeric('sale_price')->positive('sale_price')
          ->numeric('stock')
          ->max('sku', 60)
          ->max('short_description', 300);

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            Flash::add('error', 'Please correct the highlighted fields.');
            return null;
        }

        $name  = (string) $request->input('name');
        $image = $existing['image'] ?? null;

        if ($file = $request->file('image')) {
            [$stored, $error] = Uploader::image($file);
            if ($error) {
                Flash::withInput($request->all(), ['image' => $error]);
                Flash::add('error', $error);
                return null;
            }
            Uploader::delete($existing['image'] ?? null);
            $image = $stored;
        }

        $categoryId = $request->int('category_id');

        return [
            'name'              => $name,
            'slug'              => Product::uniqueSlug($name, $existing['id'] ?? null),
            'sku'               => $request->input('sku') ?: strtoupper(substr(md5($name), 0, 8)),
            'category_id'       => $categoryId > 0 ? $categoryId : null,
            'short_description' => $request->input('short_description', ''),
            'description'       => $request->input('description', ''),
            'price'             => $request->float('price'),
            'sale_price'        => $request->float('sale_price'),
            'stock'             => $request->int('stock'),
            'image'             => $image,
            'is_active'         => $request->has('is_active') ? 1 : 0,
            'is_featured'       => $request->has('is_featured') ? 1 : 0,
        ];
    }
}
