<?php

namespace Webgefaehrten\AutoNotes\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Optional in deinem Model implementieren,
 * um die Notiz-Konfiguration In-Class festzulegen.
 */
interface ProvidesAutoNotesConfig
{
    public function autoNoteContext(): ?string;          // z. B. 'site','contact','order'
    public function autoNoteLabels(): array;             // ['field' => 'Label']
    public function autoNoteInclude(): array;            // Whitelist-Felder für Diffs
    public function autoNoteOwner(): ?Model;             // Aggregations-Owner (oder null)
    public function autoNoteDisplayName(): ?string;      // Anzeigename im Titel

    public static function autoNotesObserverClass(): ?string;   // eigener Observer (FQCN)
    public static function autoNotesObserverEnabled(): bool;    // Auto-Attach ja/nein
}
