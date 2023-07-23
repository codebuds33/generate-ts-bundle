<?php

namespace CodeBuds\GenerateTsBundle\Command;

use CodeBuds\GenerateTsBundle\Service\FileInformationService;
use ReflectionException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codebuds:generate-ts:interfaces',
    description: 'Generate TS interfaces from Symfony Entities',
)]
class GenerateTsInterfacesCommand extends Command
{
    public function __construct(
        private string                          $inputDirectory,
        private string                          $outputDirectory,
        private string                          $namespace,
        private readonly FileInformationService $fileInformationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Create new TypeScript Interfaces')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Overwrite the default namespace')
            ->addOption('outputDirectory', null, InputOption::VALUE_OPTIONAL, 'Overwrite the default output directory')
            ->addOption('inputDirectory', null, InputOption::VALUE_OPTIONAL, 'Overwrite the default input directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate TypeScript Interfaces');

        $force = $input->getOption('force');

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

        try {
            $this->generateTsInterfaces($io, $force);
        } catch (ReflectionException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws ReflectionException
     */
    private function generateTsInterfaces(SymfonyStyle $io, bool $force): void
    {
        $files = $this->fileInformationService->getFiles($this->inputDirectory);

        if (!$force) {
            $fileNames = $this->fileInformationService->getFileNames($files);

            $io->info('Found the following entities : ');
            $io->listing($fileNames);

            $io->info('use --force to generate the typescript interfaces');
            return;
        }

        $io->progressStart(count($files));

        foreach ($files as $file) {
            $information = $this->fileInformationService->getClassInformation($file, $this->inputDirectory, $this->namespace);
            $typeScriptProperties = [];
            foreach ($information['properties'] as $property) {
                $tsType = $this->fileInformationService->mapPhpTypeToTsType(
                    namespace: $this->namespace,
                    phpType: $property["type"],
                    target: $property["target"],
                );
                $typeScriptProperties[] = "  {$property['name']}: {$tsType};";
            }

            $interfaceName = $information['interface']['shortName'];

            $outputFile = '';

            foreach ($information['imports'] as $target) {
                $outputFile .= sprintf("import {%s} from \"../%s\"\n", $target['name'], $target['path']);
            }

            if ($outputFile !== '') {
                $outputFile .= "\n";
            }

            $outputFile .= "export interface {$interfaceName} {\n" . implode("\n", $typeScriptProperties) . "\n}\n";

            $typeName = str_replace($this->inputDirectory, $this->outputDirectory, (string) $file);
            $typePath = str_replace('.php', '.ts', $typeName);

            if (file_exists($typePath)) {
                $existingContent = file_get_contents($typePath);
                if ($existingContent === $outputFile) {
                    $io->note(sprintf('No changes for %s', $typePath));
                    $io->progressAdvance();
                    continue;
                }
            }

            if (!is_dir(dirname($typePath)) && !mkdir($concurrentDirectory = dirname($typePath), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            file_put_contents($typePath, $outputFile);
            $io->info(sprintf('%s generated for %s', $typePath, $information['interface']['className']));
        }
        $io->progressFinish();
    }
}
