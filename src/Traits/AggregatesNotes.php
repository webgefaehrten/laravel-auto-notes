<?php

namespace Webgefaehrten\AutoNotes\Traits;

use Webgefaehrten\AutoNotes\Models\Note;

/**
 * Für OWNER-Modelle (z. B. Customer).
 * - ->allNotes(): gibt alle Notizen zurück, die per owner-Verknüpfung angehängt sind
 */
trait AggregatesNotes
{
    public function allNotes()
    {
        return $this->morphMany(Note::class, 'owner')->latest('id');
    }
}
