<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerTable implements \JsonSerializable
{
    /**
     * @param list<LedgerBand> $headerBands
     * @param list<LedgerColumn> $columns
     */
    public function __construct(
        public string $id,
        public Bbox $bbox,
        public LedgerTableBody $body,
        public array $columns,
        public ?string $label = null,
        public array $headerBands = [],
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('A ledger table requires an id.');
        }
        if ($columns === []) {
            throw new \InvalidArgumentException('A ledger table requires at least one column.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            Bbox::fromArray($data['bbox']),
            LedgerTableBody::fromArray($data['body']),
            array_map(static fn (array $column): LedgerColumn => LedgerColumn::fromArray($column), $data['columns']),
            $data['label'] ?? null,
            array_map(static fn (array $band): LedgerBand => LedgerBand::fromArray($band), $data['headerBands'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'label' => $this->label,
            'bbox' => $this->bbox,
            'headerBands' => $this->headerBands,
            'body' => $this->body,
            'columns' => $this->columns,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
