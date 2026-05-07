<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerTableBody implements \JsonSerializable
{
    public function __construct(
        public int $firstRowY,
        public int $rowCount,
        public int $rowHeight,
        public ?string $rowNumberSource = null,
    ) {
        if ($rowCount <= 0 || $rowHeight <= 0) {
            throw new \InvalidArgumentException('A ledger table body requires positive row count and row height.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['firstRowY'],
            (int) $data['rowCount'],
            (int) $data['rowHeight'],
            $data['rowNumberSource'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'firstRowY' => $this->firstRowY,
            'rowCount' => $this->rowCount,
            'rowHeight' => $this->rowHeight,
            'rowNumberSource' => $this->rowNumberSource,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
