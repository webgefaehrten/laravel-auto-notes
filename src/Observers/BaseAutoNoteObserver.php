<?php

namespace Webgefaehrten\AutoNotes\Observers;

use Illuminate\Database\Eloquent\Model;

abstract class BaseAutoNoteObserver
{
    protected function authorName(): string
    {
        return auth()->user()->name ?? 'System';
    }

    protected function write(Model $m, string $title, string $body, ?array $meta = null, ?string $context = null, ?Model $owner = null): void
    {
        if (!method_exists($m, 'addNote')) return;
        $m->addNote($title, $body, $meta, $context, $owner);
    }
}
