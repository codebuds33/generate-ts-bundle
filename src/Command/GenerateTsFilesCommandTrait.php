<?php

namespace CodeBuds\GenerateTsBundle\Command;

use CodeBuds\GenerateTsBundle\Service\FileGenerationService;
use CodeBuds\GenerateTsBundle\Service\FileInformationService;
use ReflectionException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        #If the namespace is defined with backslashes replace with forward slashes
        $this->namespace = str_replace('\\\\', '/', $this->namespace);
        $this->namespace = str_replace('\\', '/', $this->namespace);
        #If the namespace is defined with double forward slashes replace by single to then reset to double
        $this->namespace = str_replace('//', '/', $this->namespace);
    }
}
