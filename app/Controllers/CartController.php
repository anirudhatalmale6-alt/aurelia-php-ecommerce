<?php
namespace App\Controllers;

use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Request;
use App\Models\Category;
use App\Models\Product;

class CartController extends Controller
{
    public function show(Request $request): void
    {
        $this->view('shop/cart', [
            'title'      => 'Your basket',
            'summary'    => Cart::summary(),
            'categories' => Category::all(),
        ]);
    }

    public function add(Request $request): void
    {
        Csrf::verify();

        $product = Product::find($request->int('product_id'));
        if (!$product || $product['is_active'] != 1) {
            Flash::add('error', 'That product is no longer available.');
            $this->back();
        }
        if ((int) $product['stock'] < 1) {
            Flash::add('error', $product['name'] . ' is out of stock.');
            $this->back();
        }

        Cart::add((int) $product['id'], max(1, $request->int('quantity', 1)));
        Flash::add('success', $product['name'] . ' added to your basket.');

        if ($request->input('redirect') === 'cart') {
            redirect('/cart');
        }
        $this->back();
    }

    public function update(Request $request): void
    {
        Csrf::verify();
        foreach ((array) ($_POST['quantities'] ?? []) as $productId => $quantity) {
            Cart::set((int) $productId, (int) $quantity);
        }
        Flash::add('success', 'Basket updated.');
        redirect('/cart');
    }

    public function remove(Request $request): void
    {
        Csrf::verify();
        Cart::remove($request->int('product_id'));
        Flash::add('success', 'Item removed.');
        redirect('/cart');
    }
}
