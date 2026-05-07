<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final readonly class Bbox implements \JsonSerializable
{
    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height,
    ) {
        if ($width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('Bounding boxes require positive width and height.');
        }
    }

    /**
     * @param list<int> $data
     */
    public static function fromArray(array $data): self
    {
        if (count($data) !== 4) {
            throw new \InvalidArgumentException('A bbox must be [x, y, width, height].');
        }

        return new self((int) $data[0], (int) $data[1], (int) $data[2], (int) $data[3]);
    }

    /**
     * @return list<int>
     */
    public function jsonSerialize(): array
    {
        return [$this->x, $this->y, $this->width, $this->height];
    }
}
