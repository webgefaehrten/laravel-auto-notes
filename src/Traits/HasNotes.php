<?php

namespace Webgefaehrten\AutoNotes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webgefaehrten\AutoNotes\Contracts\ProvidesAutoNotesConfig;
use Webgefaehrten\AutoNotes\Models\Note;

/**
 * Für SUBJECT-Modelle (z. B. Contact, Site, Order).
 * - Fügt ->notes() Relation hinzu
 * - ->addNote() Helper
 * - Automatisches Observer-Attach (Universal oder Custom)
 */
trait HasNotes
{
    protected static bool $autoNotesObserverEnabled = true;
    protected static ?string $autoNotesObserverClass = null;

    public static function bootHasNotes(): void
    {
        $enabled = static::$autoNotesObserverEnabled;
        if (method_exists(static::class, 'autoNotesObserverEnabled')) {
            $tmp = new static();
            $enabled = (bool) $tmp::autoNotesObserverEnabled();
        }
        if (!$enabled) return;

        $observer = static::$autoNotesObserverClass;
        if (method_exists(static::class, 'autoNotesObserverClass')) {
            $tmp = new static();
            $observer = $tmp::autoNotesObserverClass() ?: $observer;
        }
        if (!$observer) {
            $observer = config('auto-notes.default_observer');
        }
        if ($observer && class_exists($observer)) {
            static::observe($observer);
        }
    }

    /** Notizen am Subjekt */
    public function notes()
    {
        return $this->morphMany(Note::class, 'subject')->latest('id');
    }

    /** Manuell Notiz anlegen */
    public function addNote(
        string $title,
        ?string $body = null,
        ?array $meta = null,
        ?string $context = null,
        Model|array|null $owner = null
    ): Note {
        $ctx = $context ?? (method_exists($this, 'autoNoteContext')
            ? ($this->autoNoteContext() ?? Str::kebab(class_basename(static::class)))
            : Str::kebab(class_basename(static::class)));

        $data = [
            'context'    => $ctx,
            'title'      => $title,
            'body'       => $body,
            'meta'       => $meta,
            'created_by' => auth()->id(),
        ];

        if ($owner instanceof Model) {
            $data['owner_type'] = $owner->getMorphClass();
            $data['owner_id']   = $owner->getKey();
        } elseif (is_array($owner) && count($owner) === 2) {
            [$type, $id] = $owner;
            $data['owner_type'] = $type;
            $data['owner_id']   = $id;
        }

        return $this->notes()->create($data);
    }

    /* Optionale Konfigurations-Stubs – implementierbar im Model */
    public function autoNoteContext(): ?string { return null; }
    public function autoNoteLabels(): array { return []; }
    public function autoNoteInclude(): array { return []; }
    public function autoNoteOwner(): ?Model { return null; }
    public function autoNoteDisplayName(): ?string { return null; }
    public static function autoNotesObserverClass(): ?string { return null; }
    public static function autoNotesObserverEnabled(): bool { return true; }
}
