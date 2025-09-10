<?php

namespace Webgefaehrten\AutoNotes\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class DiffRenderer
{
    public static function diffs(Model $m, array $include = [], array $ignore = []): array
    {
        $globalIgnore = config('auto-notes.ignore_fields', []);
        $ignore = array_unique(array_merge($ignore, $globalIgnore));

        $allowed = $include ?: ($m->getFillable() ?: array_keys($m->getAttributes()));
        $allowed = array_diff($allowed, $ignore);

        $diffs = [];
        foreach ($m->getDirty() as $field => $to) {
            if (!in_array($field, $allowed, true)) continue;
            $from = $m->getOriginal($field);
            if ($from === $to) continue;

            $diffs[$field] = [
                'from' => self::short($from),
                'to'   => self::short($to),
            ];
        }
        return $diffs;
    }

    public static function render(array $diffs): string
    {
        if (empty($diffs)) return '';

        $empty = Lang::get('auto-notes::autonotes.empty');
        if (!$empty || $empty === 'auto-notes::autonotes.empty') {
            $empty = config('auto-notes.empty_symbol', '∅');
        }

        return collect($diffs)->map(function($ch, $field) use ($empty) {
            $from = $ch['from'] ?? null; $to = $ch['to'] ?? null;
            $from = ($from === null || $from === '') ? $empty : $from;
            $to   = ($to   === null || $to   === '') ? $empty : $to;

            $line = Lang::get('auto-notes::autonotes.diff_from_to', [
                'from' => $from,
                'to'   => $to,
            ]);

            return "• {$field}: {$line}";
        })->implode("\n");
    }

    protected static function short($v, ?int $limit = null)
    {
        $limit = $limit ?? (int) config('auto-notes.truncate', 300);
        if (!is_string($v)) return $v;
        return mb_strlen($v) > $limit ? mb_substr($v, 0, $limit).'…' : $v;
    }
}
