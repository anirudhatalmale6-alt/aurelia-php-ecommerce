<?php
namespace App\Core;

/**
 * One-shot session messages plus old-input / validation-error carry-over.
 *
 * shift() is called once per request during boot: it lifts the previous
 * request's payload out of the session into memory, so views can read it any
 * number of times while the session copy is already cleared.
 */
class Flash
{
    private static array $messages = [];
    private static array $old      = [];
    private static array $errors   = [];

    /** Move last request's payload into memory and clear it from the session. */
    public static function shift(): void
    {
        self::$messages = $_SESSION['_flash']  ?? [];
        self::$old      = $_SESSION['_old']    ?? [];
        self::$errors   = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_flash'], $_SESSION['_old'], $_SESSION['_errors']);
    }

    public static function add(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function messages(): array
    {
        return self::$messages;
    }

    /** Persist form input + errors for the next request so a form can repopulate. */
    public static function withInput(array $input, array $errors = []): void
    {
        unset($input['password'], $input['password_confirmation'], $input['_token']);
        $_SESSION['_old']    = $input;
        $_SESSION['_errors'] = $errors;
    }

    public static function errors(): array
    {
        return self::$errors;
    }

    public static function error(string $field): ?string
    {
        return self::$errors[$field] ?? null;
    }

    public static function old(string $key, $default = '')
    {
        return self::$old[$key] ?? $default;
    }
}
