<?php

namespace Webgefaehrten\AutoNotes\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * @return (Model|string|array{0:class-string<Model>,1:int|string}|Closure(Model):(?Model))|null
 */
interface ProvidesAutoNotesConfig
{
    public function autoNoteContext(): ?string;          // z. B. 'site','contact','order'
    public function autoNoteLabels(): array;             // ['field' => 'Label']
    public function autoNoteInclude(): array;            // Whitelist-Felder für Diffs
    public function autoNoteOwner(): Model|string|array|Closure|null|int;            // Aggregations-Owner (oder null)
    public function autoNoteDisplayName(): ?string;      // Anzeigename im Titel

    public static function autoNotesObserverClass(): ?string;   // eigener Observer (FQCN)
    public static function autoNotesObserverEnabled(): bool;    // Auto-Attach ja/nein
}
