# Survos Ledger Bundle

Template-aware extraction for typed or printed archival records with handwritten values.

LedgerSpec describes the known structure of a document before AI extraction runs: page fields, fixed regions, repeated tables, column types, row counts, and normalization rules. The extraction task should fill a known structure from prior OCR/layout results rather than ask a model to invent a table.

Initial scope:

- JSON-serializable PHP DTOs for LedgerSpec.
- A template registry and codec.
- A first `us-census-1870-schedule-1` template draft.
- An `extract_ledger` task for `survos/ai-pipeline-bundle` that consumes prior OCR/layout output.

Example pipeline entry:

```json
{
  "url": "file:///path/to/sample.png",
  "title": "1870 census page",
  "pipeline": ["ocr_mistral", "layout", "extract_ledger"],
  "ledger_template": "us-census-1870-schedule-1"
}
```

`extract_ledger` is intentionally prior-results-first. Whole-page vision fallback and low-confidence crop verification should be separate later tasks.
