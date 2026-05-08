# Ledger Bundle Plan

## Purpose

`survos/ledger-bundle` is a domain bundle for extracting data from archival records with known printed/typed structure and handwritten values. It is not a survey system. It owns the ledger/document template model, geometry, extraction contract, and later verification/export helpers.

`survos/ai-pipeline-bundle` remains the generic runner for OCR, HTR, layout, and model calls.

## Current Direction

- Author templates as PHP classes with attributes.
- Compile attributed PHP classes into DTOs.
- Serialize DTOs to JSON only at boundaries: prompts, APIs, exports, cached specs.
- Use normalized/proportional coordinates by default, not scan pixels or physical paper size.
- Keep extraction structured; flatten only in exporters.

## Package Boundary

`ledger-bundle` owns:

- Ledger template attributes.
- Ledger DTO/spec model.
- Reflection reader from attributed PHP classes.
- Template registry.
- `extract_ledger` ai-pipeline task.
- Prompt templates for `extract_ledger`.
- Later: flatteners, CSV/JSON exporters, verification helpers.

`ai-pipeline-bundle` owns:

- Task runner/registry.
- OCR and HTR tasks.
- Layout tasks.
- Model/provider wiring.
- Result storage.

`ai-pipeline-demo` should later consume both bundles and be rethought around Ledger workflows.

## Extraction Strategy

`extract_ledger` must be prior-results-first.

Default pipeline:

```text
ocr_mistral -> layout -> extract_ledger
```

`extract_ledger` consumes:

- prior OCR text/markdown;
- prior layout output, if available;
- a compiled `LedgerTemplate` DTO.

It should not attach the original high-resolution image by default.

Future separate tasks:

- `extract_ledger_vision`: whole-page vision fallback when prior OCR/layout is insufficient.
- `verify_ledger_cell`: targeted crop-level HTR/VLM for low-confidence cells.

## Canonical Result Shape

Keep extraction normalized:

```json
{
  "template_id": "us-census-1870-schedule-1",
  "page_fields": {},
  "regions": {},
  "tables": {
    "inhabitants": {
      "rows": [
        {
          "row_number": 1,
          "cells": {}
        }
      ]
    }
  }
}
```

Do not duplicate page header fields into each row in the canonical result. Exporters may flatten page fields into row records for CSV or downstream systems.

## Template Model

Planned PHP authoring shape:

```php
#[LedgerTemplate(
    id: 'us-census-1870-schedule-1',
    title: '1870 U.S. Federal Census, Schedule 1 - Inhabitants',
    width: 10000,
    height: 10000,
)]
final class UsCensus1870Schedule1
{
    #[LedgerField('page_no', type: 'integer', bbox: [1030, 760, 520, 220])]
    public ?int $pageNo = null;

    #[LedgerField('county', type: 'text', bbox: [6900, 1080, 1650, 260])]
    public ?string $county = null;

    #[LedgerTable(
        id: 'inhabitants',
        bbox: [450, 2950, 9100, 8900],
        firstRowY: 4470,
        rowCount: 40,
        rowHeight: 190,
        rowClass: Census1870PersonRow::class,
    )]
    public array $inhabitants = [];
}
```

Row class:

```php
final class Census1870PersonRow
{
    #[LedgerColumn(1, type: 'integer', x: 450, width: 300, sticky: true)]
    public ?int $dwellingNo = null;

    #[LedgerColumn(3, type: 'person_name', x: 1050, width: 1550, dittoMarks: true)]
    public ?string $name = null;

    #[LedgerColumn(4, type: 'age', x: 2600, width: 300)]
    public ?string $age = null;

    #[LedgerColumn(5, type: 'enum', x: 2900, width: 220, values: ['M', 'F'])]
    public ?string $sex = null;
}
```

## Coordinates

Coordinates are proportional/normalized by default.

Example with width/height `10000`:

```text
x = 6900 means 69.00% across the aligned template
y = 1080 means 10.80% down the aligned template
w = 1650 means 16.50% of template width
h = 260 means 2.60% of template height
```

Runtime flow:

```text
raw scan -> crop/deskew/align -> map normalized template bbox to image pixels -> crop/read field
```

## Non-Table Documents

The name "ledger" is broad enough for typed archival forms with handwritten data even when no repeated table exists.

Example: Civil War pension form:

- `pageFields`: claimant name, date, location, agency, etc.
- `regions`: long preamble/body/signature areas.
- `tables`: empty.

## Cleanup From Initial Spike

The initial spike started with JSON templates under `resources/`. Replace that with PHP attributed templates.

Next session:

1. Remove `resources/schema` and `resources/templates`.
2. Keep top-level `templates/` for Twig prompts.
3. Add `src/Attribute`.
4. Add `src/Reflection/LedgerClassReader.php`.
5. Replace JSON template registry with PHP class/template registry.
6. Keep DTO classes as compiled model.
7. Adjust `ExtractLedgerTask` to resolve a template class/id through the registry.
8. Add tests for attribute reader and DTO serialization.
