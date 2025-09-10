<?php

use Illuminate\Support\Facades\Lang;

if (!function_exists('autonote')) {
    /**
     * Übersetzung aus dem Auto-Notes-Namespace holen, ohne 'messages' nutzen zu müssen.
     * Versucht nacheinander:
     * - auto-notes::autonotes.$key
     * - auto-notes::autonote.$key (Singular-Dateiname)
     * - $key (Fallback auf globale Keys)
     */
    function autonote(string $key, array $replace = [], ?string $locale = null): string
    {
        $candidates = [
            "auto-notes::autonotes.$key",
            "auto-notes::autonote.$key",
            $key,
        ];

        foreach ($candidates as $candidate) {
            if (Lang::has($candidate, $locale)) {
                return Lang::get($candidate, $replace, $locale);
            }
        }

        return Lang::get($key, $replace, $locale);
    }
}

if (!function_exists('an')) {
    /**
     * Kurzer Alias für autonote().
     */
    function an(string $key, array $replace = [], ?string $locale = null): string
    {
        return autonote($key, $replace, $locale);
    }
}


