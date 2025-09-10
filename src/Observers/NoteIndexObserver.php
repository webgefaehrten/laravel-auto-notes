<?php

namespace Webgefaehrten\AutoNotes\Observers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YourVendor\AutoNotes\Models\Note;

/**
 * Hält die optionale owner_notes_index Tabelle aktuell.
 * - Nur aktiv, wenn die Tabelle existiert
 * - Vereinfacht schnelle Abfragen für Owner (z. B. alle Notizen eines Kunden)
 */
class NoteIndexObserver
{
    protected function enabled(): bool
    {
        static $has = null;
        return $has ??= Schema::hasTable('owner_notes_index');
    }

    public function created(Note $note): void
    {
        if (!$this->enabled() || !$note->owner_type || !$note->owner_id) return;

        DB::table('owner_notes_index')->insert([
            'note_id'    => $note->id,
            'owner_type' => $note->owner_type,
            'owner_id'   => $note->owner_id,
            'context'    => $note->context,
            'created_at' => $note->created_at,
            'created_by' => $note->created_by,
        ]);
    }

    public function updated(Note $note): void
    {
        if (!$this->enabled()) return;

        if ($note->owner_type && $note->owner_id) {
            DB::table('owner_notes_index')
                ->updateOrInsert(
                    ['note_id' => $note->id],
                    [
                        'owner_type' => $note->owner_type,
                        'owner_id'   => $note->owner_id,
                        'context'    => $note->context,
                        'created_at' => $note->created_at,
                        'created_by' => $note->created_by,
                    ]
                );
        } else {
            DB::table('owner_notes_index')->where('note_id', $note->id)->delete();
        }
    }

    public function deleted(Note $note): void
    {
        if (!$this->enabled()) return;
        DB::table('owner_notes_index')->where('note_id', $note->id)->delete();
    }
}
