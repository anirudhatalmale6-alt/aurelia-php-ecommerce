<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Category;
use App\Models\PasswordReset;
use App\Models\User;

/** Registration, sign-in, sign-out and the password reset flow. */
class AuthController extends Controller
{
    public function showRegister(Request $request): void
    {
        $this->view('auth/register', ['title' => 'Create an account', 'categories' => Category::all()]);
    }

    public function register(Request $request): void
    {
        Csrf::verify();

        $v = new Validator($request->all());
        $v->required('name')->max('name', 120)
          ->required('email')->email('email')
          ->required('password')->min('password', 8)->strongPassword('password')
          ->matches('password_confirmation', 'password')
          ->custom('email', !User::emailTaken((string) $request->input('email')), 'That email already has an account.');

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            redirect('/register');
        }

        $id = User::create(
            (string) $request->input('name'),
            (string) $request->input('email'),
            (string) $request->input('password')
        );

        Auth::login(User::find($id));
        Mailer::send((string) $request->input('email'), 'Welcome to ' . App::config('app.name'), 'welcome', [
            'name' => $request->input('name'),
        ]);

        Flash::add('success', 'Welcome aboard! Your account is ready.');
        redirect($_SESSION['_intended'] ?? '/account');
    }

    public function showLogin(Request $request): void
    {
        $this->view('auth/login', ['title' => 'Sign in', 'categories' => Category::all()]);
    }

    public function login(Request $request): void
    {
        Csrf::verify();

        $email = (string) $request->input('email', '');

        if (!Auth::throttle('login:' . mb_strtolower($email), 5, 300)) {
            Flash::add('error', 'Too many attempts. Please wait a few minutes and try again.');
            redirect('/login');
        }

        if (!Auth::attempt($email, (string) $request->input('password', ''))) {
            // Deliberately vague: never reveal whether the email exists.
            Flash::withInput($request->all(), ['email' => 'Those details do not match our records.']);
            redirect('/login');
        }

        $intended = $_SESSION['_intended'] ?? null;
        unset($_SESSION['_intended']);

        Flash::add('success', 'Signed in.');
        redirect($intended ?? (Auth::isAdmin() ? '/admin' : '/account'));
    }

    public function logout(Request $request): void
    {
        Csrf::verify();
        Auth::logout();
        Flash::add('success', 'Signed out.');
        redirect('/');
    }

    public function showForgot(Request $request): void
    {
        $this->view('auth/forgot', ['title' => 'Reset your password', 'categories' => Category::all()]);
    }

    public function sendReset(Request $request): void
    {
        Csrf::verify();

        $email = mb_strtolower((string) $request->input('email', ''));
        $v     = new Validator($request->all());
        $v->required('email')->email('email');

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            redirect('/forgot-password');
        }

        if (!Auth::throttle('reset:' . $email, 3, 900)) {
            Flash::add('error', 'Too many reset requests. Please try again later.');
            redirect('/forgot-password');
        }

        // Only send when the account exists, but always show the same message
        // so the form cannot be used to enumerate registered addresses.
        if (User::findByEmail($email)) {
            $token = PasswordReset::issue($email);
            $link  = App::url('/reset-password?email=' . urlencode($email) . '&token=' . $token);
            Mailer::send($email, 'Reset your password', 'password_reset', ['link' => $link]);
        }

        Flash::add('success', 'If that email is registered, a reset link is on its way.');
        redirect('/forgot-password');
    }

    public function showReset(Request $request): void
    {
        $this->view('auth/reset', [
            'title'      => 'Choose a new password',
            'email'      => (string) $request->input('email', ''),
            'token'      => (string) $request->input('token', ''),
            'categories' => Category::all(),
        ]);
    }

    public function resetPassword(Request $request): void
    {
        Csrf::verify();

        $email = mb_strtolower((string) $request->input('email', ''));
        $token = (string) $request->input('token', '');

        $v = new Validator($request->all());
        $v->required('password')->min('password', 8)->strongPassword('password')
          ->matches('password_confirmation', 'password');

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            redirect('/reset-password?email=' . urlencode($email) . '&token=' . urlencode($token));
        }

        if (!PasswordReset::verify($email, $token)) {
            Flash::add('error', 'That reset link is invalid or has expired.');
            redirect('/forgot-password');
        }

        $user = User::findByEmail($email);
        User::updatePassword((int) $user['id'], (string) $request->input('password'));
        PasswordReset::consume($email);

        Flash::add('success', 'Password updated. You can sign in now.');
        redirect('/login');
    }
}
