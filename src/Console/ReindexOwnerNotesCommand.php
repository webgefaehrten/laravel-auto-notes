<?php

namespace Webgefaehrten\AutoNotes\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YourVendor\AutoNotes\Models\Note;

/**
 * Baut die owner_notes_index Tabelle neu auf.
 * Nützlich nach Migrationen oder wenn Index inkonsistent wurde.
 */
class ReindexOwnerNotesCommand extends Command
{
    protected $signature = 'auto-notes:reindex-owner {--truncate}';
    protected $description = 'Owner-Index-Tabelle neu aufbauen.';

    public function handle(): int
    {
        if (!Schema::hasTable('owner_notes_index')) {
            $this->error('owner_notes_index existiert nicht.');
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::table('owner_notes_index')->truncate();
            $this->info('owner_notes_index geleert.');
        }

        $count = 0;
        Note::query()
            ->whereNotNull('owner_type')
            ->whereNotNull('owner_id')
            ->orderBy('id')
            ->chunkById(10000, function($chunk) use (&$count) {
                $rows = $chunk->map(fn($n) => [
                    'note_id'    => $n->id,
                    'owner_type' => $n->owner_type,
                    'owner_id'   => $n->owner_id,
                    'context'    => $n->context,
                    'created_at' => $n->created_at,
                    'created_by' => $n->created_by,
                ])->all();

                DB::table('owner_notes_index')->upsert($rows, ['note_id']);
                $count += count($rows);
                $this->line("… reindexed +".count($rows)." (sum={$count})");
            });

        $this->info("Fertig. Indexierte Notizen: {$count}");
        return self::SUCCESS;
    }
}
