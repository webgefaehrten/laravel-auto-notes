<?php

namespace Webgefaehrten\AutoNotes;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Webgefaehrten\AutoNotes\Models\Note;
use Webgefaehrten\AutoNotes\Observers\NoteIndexObserver;

class AutoNotesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auto-notes.php', 'auto-notes');
    }

    public function boot(): void
    {
        // Translations laden & publishen
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'auto-notes');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/auto-notes'),
        ], 'auto-notes-lang');

        // Publish Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/2025_01_01_000000_create_notes_table.php' =>
                database_path('migrations/'.date('Y_m_d_His').'_create_notes_table.php'),
        ], 'auto-notes-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/2025_01_02_000000_create_owner_notes_index_table.php' =>
                database_path('migrations/'.date('Y_m_d_His', time()+1).'_create_owner_notes_index_table.php'),
        ], 'auto-notes-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/2025_01_03_000000_create_notes_archive_table.php' =>
                database_path('migrations/'.date('Y_m_d_His', time()+2).'_create_notes_archive_table.php'),
        ], 'auto-notes-migrations');

        // Publish Config
        $this->publishes([
            __DIR__.'/../config/auto-notes.php' => config_path('auto-notes.php'),
        ], 'auto-notes-config');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webgefaehrten\AutoNotes\Console\PruneNotesCommand::class,
                \Webgefaehrten\AutoNotes\Console\ReindexOwnerNotesCommand::class,
            ]);
        }

        // Optionaler Owner-Index
        if (Schema::hasTable('owner_notes_index')) {
            Note::observe(NoteIndexObserver::class);
        }
    }
}
