<?php

namespace Webgefaehrten\AutoNotes\Observers;

use Illuminate\Database\Eloquent\Model;
use Webgefaehrten\AutoNotes\Support\OwnerResolver;
use Webgefaehrten\AutoNotes\Support\DiffRenderer;

class UniversalAutoNoteObserver extends BaseAutoNoteObserver
{
    public function created(Model $m): void
    {
        [$context, $name, $owner] = $this->cfg($m);

        $titleContext = ucfirst($context);
        $titleAction  = autoNotesTrans('created', ['context' => $titleContext]);
        $title        = '['.$titleContext.'] '.($name ? "{$titleAction}: {$name}" : $titleAction);

        $body = '**'.$titleContext.'** '
              . autoNotesTrans('created', ['context' => $titleContext])
              . ' '
              . autoNotesTrans('by_user_at', [
                    'user' => $this->authorName(),
                    'date' => now()->format('d.m.Y H:i'),
                ]);

        $this->write($m, $title, $body, null, $context, $owner);
    }

    public function updated(Model $m): void
    {
        [$context, $name, $owner, $include, $labels] = $this->cfg($m, needInclude: true, needLabels: true);

        $raw = DiffRenderer::diffs($m, $include);
        if (empty($raw)) return;

        // Labels anwenden
        $diffs = [];
        foreach ($raw as $field => $val) {
            $label = $labels[$field] ?? $field;
            $diffs[$label] = $val;
        }

        $titleContext = ucfirst($context);
        $titleAction  = autoNotesTrans('updated', ['context' => $titleContext]);
        $title        = '['.$titleContext.'] '.($name ? "{$titleAction}: {$name}" : $titleAction);

        $body = '**'.$titleContext.'** '
              . autoNotesTrans('updated', ['context' => $titleContext])
              . ' '
              . autoNotesTrans('by_user_at', [
                    'user' => $this->authorName(),
                    'date' => now()->format('d.m.Y H:i'),
                ])
              . "\n\n"
              . DiffRenderer::render($diffs); // nutzt intern ggf. diff_from_to/empty

        $this->write($m, $title, $body, ['changes' => $diffs], $context, $owner);
    }

    public function deleted(Model $m): void
    {
        [$context, $name, $owner] = $this->cfg($m);

        $titleContext = ucfirst($context);
        $titleAction  = autoNotesTrans('deleted', ['context' => $titleContext]);
        $title        = '['.$titleContext.'] '.($name ? "{$titleAction}: {$name}" : $titleAction);

        $body = '**'.$titleContext.'** '
              . autoNotesTrans('deleted', ['context' => $titleContext])
              . ' '
              . autoNotesTrans('by_user_at', [
                    'user' => $this->authorName(),
                    'date' => now()->format('d.m.Y H:i'),
                ]);

        // Beispiel-Meta
        $meta = ['changes' => ['Status' => ['from' => 'vorhanden', 'to' => 'entfernt']]];

        $this->write($m, $title, $body, $meta, $context, $owner);
    }

    protected function cfg(Model $m, bool $needInclude = false, bool $needLabels = false): array
    {
        $context = method_exists($m, 'autoNoteContext')
            ? ($m->autoNoteContext() ?? \Illuminate\Support\Str::kebab(class_basename($m)))
            : \Illuminate\Support\Str::kebab(class_basename($m));

        $name = method_exists($m, 'autoNoteDisplayName') ? ($m->autoNoteDisplayName() ?? '') : '';

        // Owner robust ermitteln (Model | Class | [Class, id] | Closure | null)
        $owner = OwnerResolver::resolve($m);

        $include = $needInclude && method_exists($m, 'autoNoteInclude') ? ($m->autoNoteInclude() ?? []) : [];
        $labels  = $needLabels && method_exists($m, 'autoNoteLabels')  ? ($m->autoNoteLabels()  ?? []) : [];

        return [$context, $name, $owner, $include, $labels];
    }
}
