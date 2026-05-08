<?php
declare(strict_types=1);

namespace Survos\LedgerBundle\Task;

use Survos\AiPipelineBundle\Task\AiTaskInterface;
use Survos\LedgerBundle\Spec\LedgerTemplate;
use Survos\LedgerBundle\Spec\LedgerTemplateCodec;
use Survos\LedgerBundle\Spec\LedgerTemplateRegistry;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment as TwigEnvironment;

final class ExtractLedgerTask implements AiTaskInterface
{
    public const TASK = 'extract_ledger';

    public function __construct(
        #[Autowire(service: 'ai.agent.metadata')]
        private readonly AgentInterface $agent,
        private readonly TwigEnvironment $twig,
        private readonly LedgerTemplateRegistry $templateRegistry,
        private readonly LedgerTemplateCodec $codec,
    ) {
    }

    public function getTask(): string
    {
        return self::TASK;
    }

    public function supports(array $inputs, array $context = []): bool
    {
        return ($inputs['ledger_template'] ?? $context['ledger_template'] ?? null) !== null
            || ($inputs['ledger_template_spec'] ?? $context['ledger_template_spec'] ?? null) !== null;
    }

    public function run(array $inputs, array $priorResults = [], array $context = []): array
    {
        $template = $this->resolveTemplate($inputs, $context);
        $ocrText = $this->ocrText($inputs, $priorResults);
        $layout = $priorResults['layout'] ?? null;

        if ($ocrText === null && $layout === null) {
            throw new \InvalidArgumentException('extract_ledger requires prior OCR text, layout output, or inputs["text"].');
        }

        $templateJson = $this->codec->encodeJson($template);
        $layoutJson = $layout === null
            ? null
            : json_encode($layout, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $promptContext = [
            'template' => $template,
            'templateJson' => $templateJson,
            'ocrText' => $ocrText,
            'layoutJson' => $layoutJson,
            'context' => $context,
            'inputs' => $inputs,
            'prior' => $priorResults,
        ];

        $messages = new MessageBag(
            Message::forSystem(trim($this->twig->render('@SurvosLedger/prompt/extract_ledger/system.html.twig', $promptContext))),
            Message::ofUser(trim($this->twig->render('@SurvosLedger/prompt/extract_ledger/user.html.twig', $promptContext))),
        );

        $result = $this->agent->call($messages);
        $content = $result->getContent();
        $data = is_array($content) ? $content : $this->parseRawContent((string) $content);

        $data['_ledger_template'] = [
            'id' => $template->id,
            'version' => $template->version,
        ];
        $data['_extraction_mode'] = 'prior_results';

        return $data;
    }

    public function getMeta(): array
    {
        return [
            'agent' => 'metadata',
            'description' => 'Fills a LedgerSpec template from prior OCR/layout output without another image call.',
            'template' => '@SurvosLedger/prompt/extract_ledger/system.html.twig',
        ];
    }

    /**
     * @param array<string, mixed> $inputs
     * @param array<string, mixed> $context
     */
    private function resolveTemplate(array $inputs, array $context): LedgerTemplate
    {
        $inline = $inputs['ledger_template_spec'] ?? $context['ledger_template_spec'] ?? null;
        if (is_array($inline)) {
            return $this->codec->fromArray($inline);
        }
        if (is_string($inline)) {
            return $this->codec->decodeJson($inline);
        }

        $templateId = $inputs['ledger_template'] ?? $context['ledger_template'] ?? null;
        if (!is_string($templateId) || $templateId === '') {
            throw new \InvalidArgumentException('extract_ledger requires ledger_template or ledger_template_spec.');
        }

        return $this->templateRegistry->get($templateId);
    }

    /**
     * @param array<string, mixed> $inputs
     * @param array<string, mixed> $priorResults
     */
    private function ocrText(array $inputs, array $priorResults): ?string
    {
        $text = $inputs['text']
            ?? $priorResults['ocr_mistral']['text']
            ?? $priorResults['ocr']['text']
            ?? $priorResults['transcribe_handwriting']['text']
            ?? null;

        if (is_string($text) && trim($text) !== '') {
            return $text;
        }

        $pages = $priorResults['ocr_mistral']['raw_response']['pages'] ?? null;
        if (!is_array($pages)) {
            return null;
        }

        $pageTexts = [];
        foreach ($pages as $page) {
            if (is_array($page) && isset($page['markdown']) && is_string($page['markdown'])) {
                $pageTexts[] = trim($page['markdown']);
            }
        }

        $joined = trim(implode("\n\n--- page ---\n\n", array_filter($pageTexts)));

        return $joined === '' ? null : $joined;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseRawContent(string $content): array
    {
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
        $stripped = preg_replace('/\s*```\s*$/', '', $stripped ?? $content);
        $stripped = trim($stripped ?? $content);

        try {
            $decoded = json_decode($stripped, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return ['raw' => $content];
        }

        return is_array($decoded) ? $decoded : ['raw' => $content];
    }
}
