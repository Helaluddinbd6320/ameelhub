<?php

if (! function_exists('setting')) {
    /**
     * Settings table থেকে value পাও
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            $value = \App\Models\Setting::where('key', $key)->value('value');
            return $value ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('wallet')) {
    /**
     * WalletService instance পাও (global shortcut)
     */
    function wallet(): \App\Services\WalletService
    {
        return app(\App\Services\WalletService::class);
    }
}