<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerTemplate implements \JsonSerializable
{
    /**
     * @param list<LedgerField> $pageFields
     * @param list<LedgerRegion> $regions
     * @param list<LedgerTable> $tables
     * @param list<array<string, mixed>> $rules
     * @param array<string, mixed> $alignment
     * @param array<string, mixed> $vocabularies
     * @param array<string, mixed> $output
     */
    public function __construct(
        public string $id,
        public string $title,
        public CoordinateSpace $coordinateSpace,
        public string $schema = 'https://schemas.survos.com/ledger/v1/template.json',
        public string $kind = 'ledger_template',
        public ?string $version = null,
        public array $alignment = [],
        public array $pageFields = [],
        public array $regions = [],
        public array $tables = [],
        public array $rules = [],
        public array $vocabularies = [],
        public array $output = [],
    ) {
        if ($id === '' || $title === '') {
            throw new \InvalidArgumentException('A ledger template requires id and title.');
        }
        if ($kind !== 'ledger_template') {
            throw new \InvalidArgumentException('LedgerTemplate kind must be "ledger_template".');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['title'],
            CoordinateSpace::fromArray($data['coordinateSpace']),
            (string) ($data['schema'] ?? 'https://schemas.survos.com/ledger/v1/template.json'),
            (string) ($data['kind'] ?? 'ledger_template'),
            $data['version'] ?? null,
            $data['alignment'] ?? [],
            array_map(static fn (array $field): LedgerField => LedgerField::fromArray($field), $data['pageFields'] ?? []),
            array_map(static fn (array $region): LedgerRegion => LedgerRegion::fromArray($region), $data['regions'] ?? []),
            array_map(static fn (array $table): LedgerTable => LedgerTable::fromArray($table), $data['tables'] ?? []),
            $data['rules'] ?? [],
            $data['vocabularies'] ?? [],
            $data['output'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'schema' => $this->schema,
            'kind' => $this->kind,
            'id' => $this->id,
            'version' => $this->version,
            'title' => $this->title,
            'coordinateSpace' => $this->coordinateSpace,
            'alignment' => $this->alignment,
            'pageFields' => $this->pageFields,
            'regions' => $this->regions,
            'tables' => $this->tables,
            'rules' => $this->rules,
            'vocabularies' => $this->vocabularies,
            'output' => $this->output,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
