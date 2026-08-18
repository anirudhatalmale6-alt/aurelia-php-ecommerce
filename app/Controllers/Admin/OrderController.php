<?php
namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\View;
use App\Models\Order;
use App\Models\User;

class OrderController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/orders/index', [
            'title'  => 'Orders',
            'orders' => Order::adminList(
                (string) $request->input('status', ''),
                (string) $request->input('q', '')
            ),
            'status' => (string) $request->input('status', ''),
            'search' => (string) $request->input('q', ''),
        ], 'layouts/admin');
    }

    public function show(Request $request, string $id): void
    {
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Order not found'], 'layouts/admin');
            return;
        }

        $this->view('admin/orders/show', [
            'title' => 'Order ' . $order['reference'],
            'order' => $order,
            'items' => Order::items((int) $order['id']),
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request, string $id): void
    {
        Csrf::verify();

        $order  = Order::find((int) $id);
        $status = (string) $request->input('status', '');

        if (!$order || !in_array($status, Order::STATUSES, true)) {
            Flash::add('error', 'Invalid status change.');
            redirect('/admin/orders');
        }

        Order::setStatus((int) $id, $status);

        // Keep the customer informed when the parcel moves.
        if (in_array($status, ['shipped', 'completed', 'refunded', 'cancelled'], true)) {
            Mailer::send($order['email'], 'Update on order ' . $order['reference'], 'order_status', [
                'order'  => Order::find((int) $id),
                'status' => $status,
            ]);
        }

        Flash::add('success', 'Order marked as ' . $status . '.');
        redirect('/admin/orders/' . $id);
    }

    public function customers(Request $request): void
    {
        $this->view('admin/customers', [
            'title'     => 'Customers',
            'customers' => User::all(),
        ], 'layouts/admin');
    }
}
