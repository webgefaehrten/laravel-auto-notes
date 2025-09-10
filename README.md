# Laravel Auto Notes

Polymorphe Notizen für Laravel-Modelle mit optionalem Owner (Aggregation), automatischen Diffs (Feldänderungen), In-Class-Konfiguration, Retention/Pruning, optionalem Owner-Index und Mehrsprachigkeit (DE/EN).

---

## 🇩🇪 Deutsch

### ✨ Features
- Automatische Notizen bei `created`, `updated`, `deleted`
- Feld-Diffs: von `X` → `Y`
- Owner/Aggregation: z. B. Kunde ↔ Baustellen ↔ Kontakte
- In-Class-Konfiguration (kein zentrales Config-File nötig)
- Manuelles Hinzufügen von Notizen (`addNote()`)
- Observer pro Model deaktivierbar oder austauschbar
- Retention & Archivierung (alte Notizen löschen oder verschieben)
- Optionaler Owner-Index für schnelle Abfragen
- Mehrsprachigkeit (DE/EN) mit Publish-Option

### 🚀 Installation
```bash
composer require webgefaehrten/laravel-auto-notes
```

Publizieren:
```bash
php artisan vendor:publish --provider="Webgefaehrten\AutoNotes\AutoNotesServiceProvider" --tag=auto-notes-config
php artisan vendor:publish --provider="Webgefaehrten\AutoNotes\AutoNotesServiceProvider" --tag=auto-notes-migrations
php artisan vendor:publish --provider="Webgefaehrten\AutoNotes\AutoNotesServiceProvider" --tag=auto-notes-lang
```

Migrationen ausführen:
```bash
php artisan migrate
```

### 🛠 Verwendung

**Subject-Model (z. B. `CustomerSite`):**
```php
use Webgefaehrten\AutoNotes\Traits\HasNotes;
use Webgefaehrten\AutoNotes\Contracts\ProvidesAutoNotesConfig;
use Illuminate\Database\Eloquent\Model;

class CustomerSite extends Model implements ProvidesAutoNotesConfig
{
    use HasNotes;

    protected $fillable = ['customer_id','name','street','zip','city'];

    public function customer() { return $this->belongsTo(Customer::class); }

    // In-Class Konfiguration
    public function autoNoteContext(): ?string { return 'site'; }
    public function autoNoteLabels(): array { return ['name'=>'Name','city'=>'Ort']; }
    public function autoNoteInclude(): array { return ['name','city','zip','street']; }
    public function autoNoteOwner(): ?Model { return $this->customer; }
    public function autoNoteDisplayName(): ?string { return $this->name; }
}
```

**Owner-Model (z. B. `Customer`):**
```php
use Webgefaehrten\AutoNotes\Traits\AggregatesNotes;

class Customer extends Model
{
    use AggregatesNotes; // ->allNotes()
}
```

**Manuell Notiz anlegen:**
```php
$site->addNote(
    title: 'Adresse geändert',
    body:  'Von A-Straße nach B-Straße',
    context: 'site',
    owner:  $site->customer
);
```

**Alle Notizen abrufen:**
```php
$notes = $customer->allNotes; // alle Notizen des Customers
$notes = $site->notes;        // nur Notizen der Site
```

### ⚙️ Retention / Archivierung
Konfiguration in `config/auto-notes.php`:
```php
'retention_days'      => 730,
'retention_overrides' => [
    'order' => 1825, // 5 Jahre für Aufträge
],
'archive_to_table'    => 'notes_archive',
```

Prune-Command:
```bash
php artisan auto-notes:prune
```

### 🌍 Mehrsprachigkeit
```bash
php artisan vendor:publish --tag=auto-notes-lang
```

Verfügbare Sprachen: `de`, `en`.

---

## 🇬🇧 English

### ✨ Features
- Automatic notes on `created`, `updated`, `deleted`
- Field diffs: from `X` → `Y`
- Owner/Aggregation: e.g. Customer ↔ Sites ↔ Contacts
- In-class configuration (no central config file required)
- Add notes manually (`addNote()`)
- Observer per model can be disabled or replaced
- Retention & pruning (delete/archive old notes)
- Optional owner index for fast queries
- Multi-language (EN/DE) with publish option

### 🚀 Installation
```bash
composer require webgefaehrten/laravel-auto-notes
```

Publish:
```bash
php artisan vendor:publish --provider="YourVendor\AutoNotes\AutoNotesServiceProvider" --tag=auto-notes-config
php artisan vendor:publish --provider="YourVendor\AutoNotes\AutoNotesServiceProvider" --tag=auto-notes-migrations
php artisan vendor:publish --provider="YourVendor\AutoNotes\AutoNotesServiceProvider" --tag=auto-notes-lang
```

Run migrations:
```bash
php artisan migrate
```

### 🛠 Usage

**Subject model (e.g. `CustomerSite`):**
```php
use Webgefaehrten\AutoNotes\Traits\HasNotes;
use Webgefaehrten\AutoNotes\Contracts\ProvidesAutoNotesConfig;
use Illuminate\Database\Eloquent\Model;

class CustomerSite extends Model implements ProvidesAutoNotesConfig
{
    use HasNotes;

    protected $fillable = ['customer_id','name','street','zip','city'];

    public function customer() { return $this->belongsTo(Customer::class); }

    public function autoNoteContext(): ?string { return 'site'; }
    public function autoNoteLabels(): array { return ['name'=>'Name','city'=>'City']; }
    public function autoNoteInclude(): array { return ['name','city','zip','street']; }
    public function autoNoteOwner(): ?Model { return $this->customer; }
    public function autoNoteDisplayName(): ?string { return $this->name; }
}
```

**Owner model (e.g. `Customer`):**
```php
use Webgefaehrten\AutoNotes\Traits\AggregatesNotes;

class Customer extends Model
{
    use AggregatesNotes; // ->allNotes()
}
```

**Add note manually:**
```php
$site->addNote(
    title: 'Address changed',
    body:  'From A-Street to B-Street',
    context: 'site',
    owner:  $site->customer
);
```

**Fetch notes:**
```php
$notes = $customer->allNotes; // all notes of the customer
$notes = $site->notes;        // only notes of the site
```

### ⚙️ Retention / Archiving
Config in `config/auto-notes.php`:
```php
'retention_days'      => 730,
'retention_overrides' => [
    'order' => 1825, // 5 years for orders
],
'archive_to_table'    => 'notes_archive',
```

Prune command:
```bash
php artisan auto-notes:prune
```

### 🌍 Multi-language
```bash
php artisan vendor:publish --tag=auto-notes-lang
```

Available languages: `en`, `de`.
