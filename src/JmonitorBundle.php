<?php

namespace Johndodev\JmonitorBundle;

use Johndodev\JmonitorBundle\Collector\MysqlCollector;
use Johndodev\JmonitorBundle\Command\CollectorCommand;
use Johndodev\JmonitorBundle\Jmonitor\Client;
use Johndodev\JmonitorBundle\Jmonitor\Jmonitor;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class JmonitorBundle extends AbstractBundle
{
    private const VERSION = 'alpha';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->services()->set(Client::class)
            ->args([
                $config['base_url'],
                self::VERSION,
                $config['project_api_key'],
                service($config['http_client'] ?? '')->ignoreOnInvalid(),
            ])
        ;

        $container->services()->set(MysqlCollector::class)
            ->args([
                service('doctrine.dbal.default_connection')
            ])
            ->tag('jmonitor.collector', ['name' => 'mysql'])
        ;

//        // tag les collecteurs
//        $container->services()
//            ->instanceof(CollectorInterface::class)
//            ->tag('jmonitor.collector')
//        ;

        $container->services()->set(Jmonitor::class)
            ->args([
                service(Client::class),
                tagged_iterator('jmonitor.collector', 'name')
            ])
        ;

        $container->services()->set(CollectorCommand::class)
            ->args([
                service(Jmonitor::class),
                service(Client::class),
            ])
            ->tag('console.command')
            ->tag('scheduler.task', [
                'frequency' => 15,
                'schedule' => $config['schedule'],
                'trigger' => 'every',
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
                ->scalarNode('base_url')->defaultValue('https://collector.jmonitor.io')->cannotBeEmpty()->end()
                ->scalarNode('http_client')->end()
                ->scalarNode('schedule')->cannotBeEmpty()->defaultValue('default')->info('Name of the schedule used to handle the recurring metrics collection')->end()
            ->end()
            ->validate()
                ->ifTrue(fn($config) => is_array($config) && $config['enabled'] && empty($config['project_api_key']))
                ->thenInvalid('The "project_api_key" must be set if "enabled" is true.')
            ->end()
        ;
    }
}
