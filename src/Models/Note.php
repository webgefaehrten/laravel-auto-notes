<?php

namespace Webgefaehrten\AutoNotes\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Das zentrale Note-Modell.
 * - subject: das konkrete Model (z. B. CustomerSite, Order)
 * - owner:   optionaler Aggregator (z. B. Customer, Project)
 * - author:  User, der die Notiz erzeugt hat
 */
class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = ['context','title','body','meta','created_by','owner_type','owner_id'];

    protected $casts = ['meta' => 'array'];

    /** Subjekt der Notiz */
    public function subject() { return $this->morphTo(); }

    /** Owner der Notiz (z. B. Kunde) */
    public function owner()   { return $this->morphTo(); }

    /** Ersteller (User) */
    public function author()
    {
        return $this->belongsTo(
            config('auth.providers.users.model', \App\Models\User::class),
            'created_by'
        );
    }

    /* ----------------- Praktische Scopes ----------------- */

    public function scopeForSubject($q, Model $m) {
        return $q->where('subject_type', $m->getMorphClass())
                 ->where('subject_id', $m->getKey());
    }

    public function scopeForOwner($q, Model $m) {
        return $q->where('owner_type', $m->getMorphClass())
                 ->where('owner_id', $m->getKey());
    }

    public function scopeContext($q, string $ctx) {
        return $q->where('context', $ctx);
    }

    public function scopeBefore($q, $ts) { return $q->where('created_at','<',$ts); }

    public function scopeAfter($q, $ts)  { return $q->where('created_at','>',$ts); }
}
