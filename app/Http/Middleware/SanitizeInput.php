<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that should NOT be sanitized (passwords, tokens, etc.)
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        '_token',
        '_method',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        $sanitized = $this->sanitize($input);

        $request->merge($sanitized);

        return $next($request);
    }

    protected function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->cleanString($value);
            }
        }

        return $data;
    }

    protected function cleanString(string $value): string
    {
        // Strip HTML/PHP tags
        $value = strip_tags($value);

        // Trim whitespace
        $value = trim($value);

        return $value;
    }
}