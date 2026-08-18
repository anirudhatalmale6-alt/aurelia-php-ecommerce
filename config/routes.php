<?php
/**
 * Route table.
 *
 * Third argument is the middleware stack: 'guest', 'auth' or 'admin'.
 */

use App\Core\Router;
use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\MediaController;
use App\Controllers\ShopController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\ProductController as AdminProductController;

$router = new Router();

/* -------------------------------------------------- Storefront */
$router->get('/',                  [ShopController::class, 'home']);
$router->get('/shop',              [ShopController::class, 'catalogue']);
$router->get('/search',            [ShopController::class, 'catalogue']);
$router->get('/api/suggest',       [ShopController::class, 'suggest']);
$router->get('/product/{slug}',    [ShopController::class, 'product']);
$router->get('/media/{file}',      [MediaController::class, 'show']);

/* -------------------------------------------------- Basket */
$router->get('/cart',              [CartController::class, 'show']);
$router->post('/cart/add',         [CartController::class, 'add']);
$router->post('/cart/update',      [CartController::class, 'update']);
$router->post('/cart/remove',      [CartController::class, 'remove']);

/* -------------------------------------------------- Checkout */
$router->get('/checkout',              [CheckoutController::class, 'show']);
$router->post('/checkout',             [CheckoutController::class, 'place']);
$router->get('/checkout/sandbox',      [CheckoutController::class, 'sandbox']);
$router->post('/checkout/sandbox',     [CheckoutController::class, 'sandboxPay']);
$router->get('/checkout/complete',     [CheckoutController::class, 'complete']);
$router->post('/webhooks/stripe',      [CheckoutController::class, 'webhook']);

/* -------------------------------------------------- Authentication */
$router->get('/register',          [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register',         [AuthController::class, 'register'],     ['guest']);
$router->get('/login',             [AuthController::class, 'showLogin'],    ['guest']);
$router->post('/login',            [AuthController::class, 'login'],        ['guest']);
$router->post('/logout',           [AuthController::class, 'logout']);
$router->get('/forgot-password',   [AuthController::class, 'showForgot']);
$router->post('/forgot-password',  [AuthController::class, 'sendReset']);
$router->get('/reset-password',    [AuthController::class, 'showReset']);
$router->post('/reset-password',   [AuthController::class, 'resetPassword']);

/* -------------------------------------------------- Customer account */
$router->get('/account',                    [AccountController::class, 'dashboard'],      ['auth']);
$router->get('/account/profile',            [AccountController::class, 'profile'],        ['auth']);
$router->post('/account/profile',           [AccountController::class, 'updateProfile'],  ['auth']);
$router->post('/account/password',          [AccountController::class, 'changePassword'], ['auth']);
$router->get('/account/orders/{reference}', [AccountController::class, 'order'],          ['auth']);

/* -------------------------------------------------- Admin */
$router->get('/admin',                          [AdminDashboardController::class, 'index'],   ['admin']);
$router->get('/admin/products',                 [AdminProductController::class, 'index'],     ['admin']);
$router->get('/admin/products/create',          [AdminProductController::class, 'create'],    ['admin']);
$router->post('/admin/products',                [AdminProductController::class, 'store'],     ['admin']);
$router->get('/admin/products/{id}/edit',       [AdminProductController::class, 'edit'],      ['admin']);
$router->post('/admin/products/{id}',           [AdminProductController::class, 'update'],    ['admin']);
$router->post('/admin/products/{id}/delete',    [AdminProductController::class, 'destroy'],   ['admin']);

$router->get('/admin/categories',               [AdminCategoryController::class, 'index'],    ['admin']);
$router->post('/admin/categories',              [AdminCategoryController::class, 'store'],    ['admin']);
$router->post('/admin/categories/{id}',         [AdminCategoryController::class, 'update'],   ['admin']);
$router->post('/admin/categories/{id}/delete',  [AdminCategoryController::class, 'destroy'],  ['admin']);

$router->get('/admin/orders',                   [AdminOrderController::class, 'index'],        ['admin']);
$router->get('/admin/orders/{id}',              [AdminOrderController::class, 'show'],         ['admin']);
$router->post('/admin/orders/{id}/status',      [AdminOrderController::class, 'updateStatus'], ['admin']);
$router->get('/admin/customers',                [AdminOrderController::class, 'customers'],    ['admin']);

return $router;
