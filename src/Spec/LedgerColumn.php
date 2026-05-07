<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerColumn implements \JsonSerializable
{
    /**
     * @param list<string> $values
     * @param array<string, mixed> $extraction
     */
    public function __construct(
        public string $id,
        public int $number,
        public string $label,
        public string $type,
        public int $x,
        public int $width,
        public array $values = [],
        public ?string $controlledVocab = null,
        public bool $sticky = false,
        public bool $dittoMarks = false,
        public array $extraction = [],
    ) {
        if ($id === '' || $width <= 0) {
            throw new \InvalidArgumentException('Ledger columns require an id and positive width.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (int) $data['number'],
            (string) ($data['label'] ?? $data['id']),
            (string) $data['type'],
            (int) $data['x'],
            (int) $data['width'],
            array_values($data['values'] ?? []),
            $data['controlledVocab'] ?? null,
            (bool) ($data['sticky'] ?? false),
            (bool) ($data['dittoMarks'] ?? false),
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
            'number' => $this->number,
            'label' => $this->label,
            'type' => $this->type,
            'x' => $this->x,
            'width' => $this->width,
            'values' => $this->values,
            'controlledVocab' => $this->controlledVocab,
            'sticky' => $this->sticky,
            'dittoMarks' => $this->dittoMarks,
            'extraction' => $this->extraction,
        ], static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== false);
    }
}
