<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Spec;

final class LedgerTemplateRegistry
{
    /**
     * @param list<string> $templatePaths
     */
    public function __construct(
        private readonly array $templatePaths,
        private readonly LedgerTemplateCodec $codec,
    ) {
    }

    public function get(string $templateId): LedgerTemplate
    {
        foreach ($this->candidateFiles($templateId) as $file) {
            if (!is_file($file)) {
                continue;
            }

            $template = $this->loadFile($file);
            if ($template->id === $templateId) {
                return $template;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Ledger template "%s" was not found in: %s',
            $templateId,
            implode(', ', $this->templatePaths),
        ));
    }

    /**
     * @return list<array{id: string, title: string, path: string}>
     */
    public function all(): array
    {
        $templates = [];
        foreach ($this->templatePaths as $path) {
            foreach (glob(rtrim($path, '/') . '/*.template.json') ?: [] as $file) {
                $template = $this->loadFile($file);
                $templates[] = [
                    'id' => $template->id,
                    'title' => $template->title,
                    'path' => $file,
                ];
            }
        }

        usort($templates, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        return $templates;
    }

    private function loadFile(string $file): LedgerTemplate
    {
        $json = file_get_contents($file);
        if ($json === false) {
            throw new \RuntimeException(sprintf('Unable to read LedgerSpec file "%s".', $file));
        }

        return $this->codec->decodeJson($json, $file);
    }

    /**
     * @return list<string>
     */
    private function candidateFiles(string $templateId): array
    {
        $files = [];
        foreach ($this->templatePaths as $path) {
            $base = rtrim($path, '/');
            $files[] = sprintf('%s/%s.template.json', $base, $templateId);
            $files[] = sprintf('%s/%s.json', $base, $templateId);
        }

        return $files;
    }
}
