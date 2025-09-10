<?php

namespace Webgefaehrten\AutoNotes\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YourVendor\AutoNotes\Models\Note;

/**
 * Löscht oder archiviert alte Notizen gemäß Config.
 * - retention_days (Standard)
 * - retention_overrides (pro Context)
 * - optional archive_to_table (z. B. notes_archive)
 */
class PruneNotesCommand extends Command
{
    protected $signature = 'auto-notes:prune {--dry-run} {--context=}';
    protected $description = 'Alte Notizen gemäß Aufbewahrungsregeln löschen oder archivieren.';

    public function handle(): int
    {
        $ctxOpt    = $this->option('context');
        $default   = (int) config('auto-notes.retention_days', 730);
        $overrides = (array) config('auto-notes.retention_overrides', []);
        $days      = $ctxOpt && isset($overrides[$ctxOpt]) ? (int) $overrides[$ctxOpt] : $default;
        $cutoff    = Carbon::now()->subDays($days);
        $chunk     = (int) config('auto-notes.prune_chunk', 5000);
        $archive   = config('auto-notes.archive_to_table');

        $q = Note::query()->where('created_at', '<', $cutoff);
        if ($ctxOpt) $q->where('context', $ctxOpt);

        $total = (clone $q)->count();
        $this->info("Finde {$total} Notizen älter als {$days} Tage (vor {$cutoff->toDateTimeString()})".($ctxOpt ? " [context={$ctxOpt}]" : ''));

        if ($this->option('dry-run')) {
            $this->line('Dry-run: keine Änderungen vorgenommen.');
            return self::SUCCESS;
        }

        $deleted = 0;
        $q->orderBy('id')->chunkById($chunk, function($notes) use (&$deleted, $archive) {
            $ids = $notes->pluck('id');

            if ($archive) {
                DB::table($archive)->insertUsing(
                    ['id','subject_type','subject_id','owner_type','owner_id','context','title','body','meta','created_by','created_at','updated_at'],
                    Note::whereIn('id', $ids)->select(['id','subject_type','subject_id','owner_type','owner_id','context','title','body','meta','created_by','created_at','updated_at'])
                );
            }

            if (Schema::hasTable('owner_notes_index')) {
                DB::table('owner_notes_index')->whereIn('note_id', $ids)->delete();
            }

            Note::whereIn('id', $ids)->delete();
            $deleted += $ids->count();
            $this->line("… gelöscht: +".$ids->count()." (gesamt {$deleted})");
        });

        $this->info("Fertig. Gelöscht: {$deleted}");
        return self::SUCCESS;
    }
}
