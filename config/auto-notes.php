<?php

return [
    'ignore_fields' => ['created_at','updated_at','deleted_at','remember_token','created_by','updated_by'],

    // Darstellung (Fallbacks; bevorzugt werden Übersetzungen)
    'empty_symbol'   => '∅',
    'truncate'       => 300,

    'default_observer' => \YourVendor\AutoNotes\Observers\UniversalAutoNoteObserver::class,

    // Aufbewahrung / Pruning
    'retention_days'      => 730,
    'retention_overrides' => [],
    'prune_chunk'         => 5000,
    'archive_to_table'    => null, // z. B. 'notes_archive'
];