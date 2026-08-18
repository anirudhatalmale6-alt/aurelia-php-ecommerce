<?php
namespace App\Core;

/**
 * Minimal rule-based validator.
 *
 *   $v = new Validator($request->all());
 *   $v->required('name')->email('email')->min('password', 8);
 *   if ($v->fails()) { ... $v->errors() ... }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private function value(string $field)
    {
        $v = $this->data[$field] ?? null;
        return is_string($v) ? trim($v) : $v;
    }

    private function fail(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function required(string $field, string $label = null): self
    {
        $label ??= $this->label($field);
        if ($this->value($field) === null || $this->value($field) === '') {
            $this->fail($field, "$label is required.");
        }
        return $this;
    }

    public function email(string $field): self
    {
        $v = $this->value($field);
        if ($v !== null && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->fail($field, 'Enter a valid email address.');
        }
        return $this;
    }

    public function min(string $field, int $length): self
    {
        $v = $this->value($field);
        if ($v !== null && $v !== '' && mb_strlen((string) $v) < $length) {
            $this->fail($field, $this->label($field) . " must be at least $length characters.");
        }
        return $this;
    }

    public function max(string $field, int $length): self
    {
        $v = $this->value($field);
        if ($v !== null && mb_strlen((string) $v) > $length) {
            $this->fail($field, $this->label($field) . " must be $length characters or fewer.");
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        $v = $this->value($field);
        if ($v !== null && $v !== '' && !is_numeric($v)) {
            $this->fail($field, $this->label($field) . ' must be a number.');
        }
        return $this;
    }

    public function positive(string $field): self
    {
        $v = $this->value($field);
        if (is_numeric($v) && (float) $v < 0) {
            $this->fail($field, $this->label($field) . ' cannot be negative.');
        }
        return $this;
    }

    public function matches(string $field, string $other): self
    {
        if ($this->value($field) !== $this->value($other)) {
            $this->fail($field, 'The two passwords do not match.');
        }
        return $this;
    }

    /** Reject passwords that are trivially guessable. */
    public function strongPassword(string $field): self
    {
        $v = (string) $this->value($field);
        if ($v === '') {
            return $this;
        }
        if (!preg_match('/[A-Za-z]/', $v) || !preg_match('/\d/', $v)) {
            $this->fail($field, 'Password must contain at least one letter and one number.');
        }
        return $this;
    }

    public function custom(string $field, bool $passes, string $message): self
    {
        if (!$passes) {
            $this->fail($field, $message);
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function label(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
