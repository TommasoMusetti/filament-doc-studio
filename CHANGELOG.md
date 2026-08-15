# Changelog

All notable changes to `filament-doc-studio` are documented here.

## [1.0.0] - 2026-08-15

First tagged release. The v1 scope end to end:

- Filament Builder field editor with three blocks: heading, paragraph (merge
  fields), and a line item table bound to a `DocumentDataSource` collection.
- `DocumentDataSource` contract: a per-model whitelist for the fields and
  collections a template is allowed to reach — the only way merge data enters
  a document.
- Live preview next to the editor, rendered against `DocumentDataSource::sample()`
  as you edit, before saving.
- dompdf rendering pipeline (`DocumentRenderer::html()` / `::pdf()`), usable
  from a panel, a queued job, or a console command.
- Host apps can register their own blocks and data sources, and narrow which
  blocks a given panel offers without breaking templates already saved with a
  disabled block.
