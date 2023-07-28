<?php

namespace CodeBuds\GenerateTsBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class GenerateTsBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('namespace')->defaultValue('App\Entity\\')->end()
            ->scalarNode('interface_output_directory')->defaultValue('%kernel.project_dir%/assets/interfaces')->end()
            ->scalarNode('type_output_directory')->defaultValue('%kernel.project_dir%/assets/types')->end()
            ->scalarNode('input_directory')->defaultValue('%kernel.project_dir%/src/Entity')->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $namespace = $config['namespace'];
        $interfaceOutputDirectory = $config['interface_output_directory'];
        $typeOutputDirectory = $config['type_output_directory'];
        $inputDirectory = $config['input_directory'];

        // Set the namespace as a parameter in the service container
        $builder->setParameter('generate_ts.namespace', $namespace);
        $builder->setParameter('generate_ts.interface_output_directory', $interfaceOutputDirectory);
        $builder->setParameter('generate_ts.type_output_directory', $typeOutputDirectory);
        $builder->setParameter('generate_ts.input_directory', $inputDirectory);
    }
}
