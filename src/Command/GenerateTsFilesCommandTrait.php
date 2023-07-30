<?php

namespace CodeBuds\GenerateTsBundle\Command;

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
}
