<?php

namespace Johndodev\JmonitorBundle;

use Johndodev\JmonitorBundle\Jmonitor\Client;
use Johndodev\JmonitorBundle\Jmonitor\Jmonitor;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class JmonitorBundle extends AbstractBundle
{
    private const DEFAULT_ENDPOINT = 'https://collector.jmonitor.io';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->services()->set(Client::class)
            ->args([
                $config['base_url'] ?? self::DEFAULT_ENDPOINT,
                $config['project_api_key'] ?? null,
                service($config['http_client'] ?? '')->ignoreOnInvalid(),
            ])
        ;

        $container->services()->set(Jmonitor::class)
            ->args([
                service(Client::class)
            ])
        ;
    }

    /**
     * https://symfony.com/doc/current/components/config/definition.html
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children() // jmonitor
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('project_api_key')->end()
                ->scalarNode('base_url')->end()
                ->scalarNode('http_client')->end()
            ->end()
        ;
    }
}
