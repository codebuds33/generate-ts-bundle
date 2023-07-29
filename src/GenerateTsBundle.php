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
            ->scalarNode('entity_namespace')->defaultValue('App\Entity\\')->end()
            ->scalarNode('enum_namespace')->defaultValue('App\Enum\\')->end()
            ->scalarNode('interface_output_directory')->defaultValue('%kernel.project_dir%/assets/interfaces')->end()
            ->scalarNode('type_output_directory')->defaultValue('%kernel.project_dir%/assets/types')->end()
            ->scalarNode('enum_output_directory')->defaultValue('%kernel.project_dir%/assets/enums')->end()
            ->scalarNode('entity_input_directory')->defaultValue('%kernel.project_dir%/src/Entity')->end()
            ->scalarNode('enum_input_directory')->defaultValue('%kernel.project_dir%/src/Entity')->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $entityNamespace = $config['entity_namespace'];
        $enumNamespace = $config['enum_namespace'];
        $interfaceOutputDirectory = $config['interface_output_directory'];
        $typeOutputDirectory = $config['type_output_directory'];
        $enumOutputDirectory = $config['enum_output_directory'];
        $entityInputDirectory = $config['entity_input_directory'];
        $enumInputDirectory = $config['enum_input_directory'];

        // Set the namespace as a parameter in the service container
        $builder->setParameter('generate_ts.entity_namespace', $entityNamespace);
        $builder->setParameter('generate_ts.enum_namespace', $enumNamespace);
        $builder->setParameter('generate_ts.interface_output_directory', $interfaceOutputDirectory);
        $builder->setParameter('generate_ts.type_output_directory', $typeOutputDirectory);
        $builder->setParameter('generate_ts.enum_output_directory', $enumOutputDirectory);
        $builder->setParameter('generate_ts.entity_input_directory', $entityInputDirectory);
        $builder->setParameter('generate_ts.enum_input_directory', $enumInputDirectory);
    }
}
