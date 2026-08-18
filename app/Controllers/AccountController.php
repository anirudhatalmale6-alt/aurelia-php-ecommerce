<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;

/** Customer account area: order history, profile and password change. */
class AccountController extends Controller
{
    public function dashboard(Request $request): void
    {
        $this->view('account/dashboard', [
            'title'      => 'My account',
            'orders'     => Order::forUser((int) Auth::id()),
            'categories' => Category::all(),
        ]);
    }

    public function order(Request $request, string $reference): void
    {
        $order = Order::findByReference($reference);

        // Customers may only view their own orders; admins may view any.
        if (!$order || (!Auth::isAdmin() && (int) $order['user_id'] !== (int) Auth::id())) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Order not found']);
            return;
        }

        $this->view('account/order', [
            'title'      => 'Order ' . $order['reference'],
            'order'      => $order,
            'items'      => Order::items((int) $order['id']),
            'categories' => Category::all(),
        ]);
    }

    public function profile(Request $request): void
    {
        $this->view('account/profile', [
            'title'      => 'Profile settings',
            'categories' => Category::all(),
        ]);
    }

    public function updateProfile(Request $request): void
    {
        Csrf::verify();

        $user = Auth::user();
        $v    = new Validator($request->all());
        $v->required('name')->max('name', 120)
          ->required('email')->email('email')
          ->custom('email', !User::emailTaken((string) $request->input('email'), (int) $user['id']),
                   'That email is already in use.');

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            redirect('/account/profile');
        }

        User::updateProfile((int) $user['id'], [
            'name'  => $request->input('name'),
            'email' => mb_strtolower((string) $request->input('email')),
            'phone' => $request->input('phone', ''),
        ]);

        Flash::add('success', 'Profile updated.');
        redirect('/account/profile');
    }

    public function changePassword(Request $request): void
    {
        Csrf::verify();

        $user = Auth::user();
        $v    = new Validator($request->all());
        $v->required('current_password')
          ->required('password')->min('password', 8)->strongPassword('password')
          ->matches('password_confirmation', 'password')
          ->custom('current_password',
                   password_verify((string) $request->input('current_password', ''), $user['password_hash']),
                   'Your current password is not correct.');

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            redirect('/account/profile');
        }

        User::updatePassword((int) $user['id'], (string) $request->input('password'));
        Flash::add('success', 'Password changed.');
        redirect('/account/profile');
    }
}
