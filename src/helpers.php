<?php

use Illuminate\Support\Facades\Lang;

if (! function_exists('autoNotesTrans')) {
    /**
     * Übersetzt AutoNotes-Keys:
     * 1) bevorzugt App-Keys:     lang/{locale}/autonotes.php  -> "autonotes.$key"
     * 2) Fallback Paket-Keys:    pkg/lang/{locale}/autonotes.php -> "auto-notes::autonotes.$key"
     */
    function autoNotesTrans(string $key, array $replace = [], ?string $locale = null): string
    {
        if (Lang::has("autonotes.$key", $locale)) {
            return __("autonotes.$key", $replace, $locale);
        }
        return __("auto-notes::autonotes.$key", $replace, $locale);
    }
}