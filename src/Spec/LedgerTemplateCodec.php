<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final class LedgerTemplateCodec
{
    public function decodeJson(string $json, ?string $source = null): LedgerTemplate
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid LedgerSpec JSON%s: %s',
                $source ? sprintf(' in "%s"', $source) : '',
                $e->getMessage(),
            ), previous: $e);
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException('LedgerSpec JSON must decode to an object.');
        }

        return $this->fromArray($data, $source);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data, ?string $source = null): LedgerTemplate
    {
        try {
            return LedgerTemplate::fromArray($data);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid LedgerSpec%s: %s',
                $source ? sprintf(' "%s"', $source) : '',
                $e->getMessage(),
            ), previous: $e);
        }
    }

    public function encodeJson(LedgerTemplate $template, int $flags = 0): string
    {
        return json_encode(
            $template,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | $flags,
        );
    }
}
