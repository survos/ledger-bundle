<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerBand implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public Bbox $bbox,
        public ?string $label = null,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('A ledger band requires an id.');
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
            $data['label'] ?? null,
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
        ], static fn (mixed $value): bool => $value !== null);
    }
}
