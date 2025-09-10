<?php

namespace Webgefaehrten\AutoNotes\Support;

use Illuminate\Database\Eloquent\Model;

class OwnerResolver
{
    /**
     * Ermittelt das Owner-Model anhand verschiedenster Rückgaben aus autoNoteOwner():
     * - Model:     direkt verwenden
     * - string:    Klassenname → via FK oder Relation laden
     * - array:     ['Class', $id] → direkt laden
     * - \Closure:  Closure($model): ?Model
     * - null:      kein Owner
     */
    public static function resolve(Model $subject): ?Model
    {
        // 1) Holt die "Konfiguration" aus dem Subject (falls vorhanden)
        $candidate = method_exists($subject, 'autoNoteOwner') ? $subject->autoNoteOwner() : null;

        // 2) Direkt-Fälle
        if ($candidate instanceof Model) {
            return $candidate;
        }
        if ($candidate instanceof \Closure) {
            return self::normalize($candidate($subject));
        }
        if (is_array($candidate) && count($candidate) === 2 && is_string($candidate[0])) {
            [$class, $id] = $candidate;
            return self::findOrNull($class, $id);
        }
        if (is_string($candidate) && class_exists($candidate)) {
            // Klassenname → versuche per FK oder Relation zu laden
            return self::resolveByClass($subject, $candidate);
        }

        // 3) Kein Owner
        return null;
    }

    /** Versucht anhand Klasse + FK/Relation den Owner zu laden */
    protected static function resolveByClass(Model $subject, string $ownerClass): ?Model
    {
        // a) Explizit definierter FK?
        if (method_exists($subject, 'autoNoteOwnerKey')) {
            $fk = $subject->autoNoteOwnerKey();
            if ($fk && $subject->getAttribute($fk)) {
                return self::findOrNull($ownerClass, $subject->getAttribute($fk));
            }
        }

        // b) Explizit definierte Relation?
        if (method_exists($subject, 'autoNoteOwnerRelation')) {
            $rel = $subject->autoNoteOwnerRelation();
            if ($rel && method_exists($subject, $rel)) {
                $res = $subject->{$rel}()->getResults();
                return self::normalize($res);
            }
        }

        // c) Heuristik: FK-Namen raten und laden (customer_id, owner_id, {snake_class}_id)
        $guesses = self::guessForeignKeys($subject, $ownerClass);
        foreach ($guesses as $fk) {
            $val = $subject->getAttribute($fk);
            if (!empty($val)) {
                return self::findOrNull($ownerClass, $val);
            }
        }

        // d) Heuristik: mögliche belongsTo-Relationen testen (falls vorhanden)
        $rel = self::guessBelongsToRelation($subject, $ownerClass);
        if ($rel && method_exists($subject, $rel)) {
            $res = $subject->{$rel}()->getResults();
            return self::normalize($res);
        }

        return null;
    }

    protected static function findOrNull(string $class, $id): ?Model
    {
        try {
            return $class::query()->find($id);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected static function normalize($val): ?Model
    {
        return $val instanceof Model ? $val : null;
    }

    /** FK-Namen raten: customer_id, owner_id, {basename}_id */
    protected static function guessForeignKeys(Model $subject, string $ownerClass): array
    {
        $candidates = ['owner_id', 'customer_id', 'client_id', 'parent_id'];

        $base = class_basename($ownerClass);                // z. B. Customer
        $snake = \Illuminate\Support\Str::snake($base);     // customer
        $candidates[] = "{$snake}_id";

        // optional: anhand Relation-Namen, falls vorhanden
        if (method_exists($subject, 'autoNoteOwnerRelation')) {
            $rel = $subject->autoNoteOwnerRelation();
            if ($rel) $candidates[] = "{$rel}_id";
        }

        // Duplikate entfernen, Reihenfolge erhalten
        return array_values(array_unique($candidates));
    }

    /** Triviale Heuristik: wenn Methode existiert & Klassenname ähnlich ist */
    protected static function guessBelongsToRelation(Model $subject, string $ownerClass): ?string
    {
        $guesses = ['owner', 'customer', 'client', 'parent'];

        $base = class_basename($ownerClass);     // Customer
        $lc   = strtolower($base);               // customer
        $guesses[] = $lc;

        foreach (array_unique($guesses) as $r) {
            if (method_exists($subject, $r)) return $r;
        }
        return null;
    }
}
