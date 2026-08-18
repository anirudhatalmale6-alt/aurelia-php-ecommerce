<?php
namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::instance();

        $this->view('admin/dashboard', [
            'title'      => 'Dashboard',
            'stats'      => Order::stats(),
            'series'     => Order::revenueSeries(14),
            'recent'     => $db->all('SELECT * FROM orders ORDER BY id DESC LIMIT 8'),
            'lowStock'   => $db->all('SELECT * FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC LIMIT 8'),
            'topSellers' => $db->all(
                'SELECT product_name, SUM(quantity) AS units, SUM(line_total) AS revenue
                   FROM order_items
               GROUP BY product_name
               ORDER BY units DESC
                  LIMIT 5'
            ),
        ], 'layouts/admin');
    }
}
