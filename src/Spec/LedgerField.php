<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class LedgerField implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $extraction
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $type,
        public Bbox $bbox,
        public ?string $prompt = null,
        public ?string $controlledVocab = null,
        public array $extraction = [],
    ) {
        if ($id === '' || $type === '') {
            throw new \InvalidArgumentException('A ledger field requires id and type.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) ($data['label'] ?? $data['id']),
            (string) $data['type'],
            Bbox::fromArray($data['bbox']),
            $data['prompt'] ?? null,
            $data['controlledVocab'] ?? null,
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
            'label' => $this->label,
            'type' => $this->type,
            'bbox' => $this->bbox,
            'prompt' => $this->prompt,
            'controlledVocab' => $this->controlledVocab,
            'extraction' => $this->extraction,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
