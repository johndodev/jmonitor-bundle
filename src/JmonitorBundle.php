<?php

namespace Johndodev\JmonitorBundle;

use Johndodev\JmonitorBundle\Collector\MysqlQueriesCountCollector;
use Johndodev\JmonitorBundle\Collector\MysqlSlowQueriesCollector;
use Johndodev\JmonitorBundle\Collector\NodeCollector;
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

        if (!$config['project_api_key']) {
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

        $container->services()->set(Jmonitor::class)
            ->args([
                // service(Client::class),
                tagged_iterator('jmonitor.collector', 'name')
            ])
        ;

        $container->services()->set(CollectorCommand::class)
            ->args([
                service(Jmonitor::class),
                service($config['cache']),
                service(Client::class),
                service('logger')->ignoreOnInvalid(),
            ])
            ->tag('console.command')
            ->tag('scheduler.task', [
                'frequency' => 15,
                'schedule' => $config['schedule'],
                'trigger' => 'every',
            ])
        ;

        if ($config['collectors']['mysql']['enabled'] ?? false) {
            $container->services()->set(MysqlQueriesCountCollector::class)
                ->args([
                    service('doctrine.dbal.default_connection')
                ])
                ->tag('jmonitor.collector', ['name' => 'mysql.queries_count'])
            ;

            $container->services()->set(MysqlSlowQueriesCollector::class)
                ->args([
                    service('doctrine.dbal.default_connection')
                ])
                ->tag('jmonitor.collector', ['name' => 'mysql.slow_queries'])
            ;
        }

        if ($config['collectors']['apache']['enabled'] ?? false) {

        }

        if ($config['collectors']['node']['enabled'] ?? false) {
            $container->services()->set(NodeCollector::class)
                ->args([

                ])
                ->tag('jmonitor.collector', ['name' => 'mysql.slow_queries'])
            ;
        }
    }

    /**
     * https://symfony.com/doc/current/components/config/definition.html
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children() // jmonitor
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('project_api_key')->defaultNull()->info('You can find it in your jmonitor.io settings.')->end()
                ->scalarNode('base_url')->defaultValue('https://collector.jmonitor.io')->cannotBeEmpty()->end()
                ->scalarNode('http_client')->info('Name of a Psr\Http\Client\ClientInterface service')->end()
                ->scalarNode('cache')->cannotBeEmpty()->defaultValue('cache.app')->info('Name of a Psr\Cache\CacheItemPoolInterface service, default is "cache.app"')->end()
                ->scalarNode('schedule')->cannotBeEmpty()->defaultValue('default')->info('Name of the schedule used to handle the recurring metrics collection, default is "default"')->end()
                ->arrayNode('collectors')
                    ->addDefaultsIfNotSet() // permet de récup un tableau vide si pas de config
                    // ->useAttributeAsKey()
                    ->children()
                        ->arrayNode('mysql')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                // ->scalarNode('connection')->defaultValue('doctrine.dbal.default_connection')->end()
//                                ->booleanNode('mysql_queries_count')->defaultTrue()->end()
//                                ->booleanNode('mysql_slow_queries')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('apache')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('server_status_url')->defaultValue('https://localhost/server-status')->info('Url of apache mod_status. See Google.')->end()
                            ->end()
                        ->end()
                        ->arrayNode('node')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('redis')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('php')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(fn($config) => is_array($config) && $config['enabled'] && empty($config['project_api_key']))
                ->thenInvalid('The "project_api_key" must be set if "enabled" is true.')
            ->end()
        ;
    }
}
