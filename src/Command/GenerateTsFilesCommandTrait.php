<?php

namespace CodeBuds\GenerateTsBundle\Command;

use Symfony\Component\Console\Input\InputOption;

trait GenerateTsFilesCommandTrait
{
    private function initArguments($input): void
    {
        if (null !== $outputDirectory = $input->getOption('outputDirectory')) {
            $this->outputDirectory = $outputDirectory;
        }

        if (null !== $inputDirectory = $input->getOption('inputDirectory')) {
            $this->inputDirectory = $inputDirectory;
        }

        if (null !== $namespace = $input->getOption('namespace')) {
            $this->namespace = $namespace;
        }

        // If the namespace is defined with backslashes replace with forward slashes
        $this->namespace = str_replace('\\\\', '/', $this->namespace);
        $this->namespace = str_replace('\\', '/', $this->namespace);
        // If the namespace is defined with double forward slashes replace by single to then reset to double
        $this->namespace = str_replace('//', '/', $this->namespace);
    }

    private function addDefaultConfiguration(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Create new TypeScript file')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Overwrite the default namespace')
            ->addOption('outputDirectory', null, InputOption::VALUE_OPTIONAL, 'Overwrite the default output directory')
            ->addOption('inputDirectory', null, InputOption::VALUE_OPTIONAL, 'Overwrite the default input directory')
            ->addOption('debug', 'd', InputOption::VALUE_OPTIONAL, 'Show information about the files being processed')
        ;
    }
}
