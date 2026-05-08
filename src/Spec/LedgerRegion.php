<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerRegion implements \JsonSerializable
{
    /**
     * @param list<LedgerField> $fields
     * @param array<string, mixed> $extraction
     */
    public function __construct(
        public string $id,
        public string $kind,
        public Bbox $bbox,
        public ?string $label = null,
        public array $fields = [],
        public array $extraction = [],
    ) {
        if ($id === '' || $kind === '') {
            throw new \InvalidArgumentException('A ledger region requires id and kind.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['kind'],
            Bbox::fromArray($data['bbox']),
            $data['label'] ?? null,
            array_map(static fn (array $field): LedgerField => LedgerField::fromArray($field), $data['fields'] ?? []),
            $data['extraction'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'kind' => $this->kind,
            'label' => $this->label,
            'bbox' => $this->bbox,
            'fields' => $this->fields,
            'extraction' => $this->extraction,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
