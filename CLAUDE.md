# filament-doc-studio

Plugin Filament pubblico (MIT): template builder PDF per utenti finali.
Pacchetto Packagist: `tommasomusetti/filament-doc-studio` — **nome deciso, non
modificabile dopo la pubblicazione**.

Valgono le regole di `~/projects/CLAUDE.md`. Qui solo le specifiche.

## Modalità di lavoro

Questo progetto è anche una palestra per imparare Laravel e Filament: spiegare
sempre le scelte e verificare la comprensione con domande, non consegnare solo
codice.

## Stack

- PHP 8.2+ · Filament `^4.0|^5.0` (un solo branch copre entrambi) · dompdf
- Package Laravel, non applicazione: **non esiste `php artisan serve`**. L'unico
  modo di eseguire codice è la suite di test, che avvia una app Laravel minima
  con `orchestra/testbench`.

## Comandi

```bash
composer test      # pest
composer analyse   # phpstan (serve --memory-limit=1G, già nello script)
composer lint      # pint
```

## Architettura

```
Editor (Builder field) → blocks JSON → DocumentRenderer → HTML + print CSS → dompdf → PDF
```

- `src/DocStudioServiceProvider.php` — aggancio a Laravel (viste, migration, comando).
- `src/DocStudioPlugin.php` — aggancio a **un singolo pannello** Filament. Cosa
  diversa dal provider: il renderer non deve mai dipendere da questo, perché i PDF
  si generano anche da job e comandi, dove nessun pannello è avviato.
- `src/Blocks/` — ogni blocco ha due facce: `make()` (campo editor) e `render()` (HTML).
- `src/Contracts/DocumentDataSource.php` — implementato **dal cliente**: whitelist
  dei campi esponibili.

## Vincoli non negoziabili

- **Merge field**: solo campi dichiarati da un `DocumentDataSource`. Mai
  `Blade::render` su testo scritto dall'utente, mai dot-notation libera su Eloquent.
- **Il dato non è fidato, il form sì**: i blocchi arrivano da una colonna JSON che
  il DB non può validare. Ogni `render()` fa default e clamp sui propri valori.
- **dompdf-first**: niente flexbox o grid nelle viste di stampa, layout a `<table>`
  e CSS conservativo.
- Le migration si distribuiscono come `.php.stub`, mai come `.php`.

## Scope v1 — chiuso

Dentro: 3 blocchi (titolo, paragrafo con merge field, tabella righe), solo dompdf,
anteprima su record di esempio, una azione "genera PDF".
Fuori (→ lista idee su Notion): versioning, Browsershot, bulk, invio email, gli
altri blocchi, stati draft/published, tabella documenti generati.

Regola: se durante lo sviluppo arriva un'idea, **non si implementa** — va in fondo
alla lista idee.
