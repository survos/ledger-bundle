<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class CoordinateSpace implements \JsonSerializable
{
    public function __construct(
        public int $width,
        public int $height,
        public string $origin = 'topLeft',
        public string $units = 'normalized',
    ) {
        if ($width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('Coordinate space requires positive width and height.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['width'] ?? 0),
            (int) ($data['height'] ?? 0),
            (string) ($data['origin'] ?? 'topLeft'),
            (string) ($data['units'] ?? 'normalized'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'origin' => $this->origin,
            'units' => $this->units,
        ];
    }
}
