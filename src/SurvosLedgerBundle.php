<?php
declare(strict_types=1);

namespace Survos\LedgerBundle;

use Survos\LedgerBundle\Spec\LedgerTemplateCodec;
use Survos\LedgerBundle\Spec\LedgerTemplateRegistry;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class SurvosLedgerBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('template_paths')
                    ->info('Directories containing LedgerSpec *.template.json files.')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container): void
    {
        $templatePaths = array_values(array_unique(array_merge(
            [dirname(__DIR__) . '/resources/templates'],
            $config['template_paths'] ?? [],
        )));

        $container->parameters()
            ->set('survos_ledger.template_paths', $templatePaths);

        $services = $container->services()
            ->defaults()
            ->autowire()
            ->autoconfigure();

        $services->set(LedgerTemplateCodec::class);
        $services->set(LedgerTemplateRegistry::class)
            ->arg('$templatePaths', '%survos_ledger.template_paths%')
            ->arg('$codec', service(LedgerTemplateCodec::class));

        // ExtractLedgerTask is not registered yet — it needs porting from the
        // removed ai-pipeline-bundle AiTaskInterface to an ai-workflow-bundle task.
    }
}
