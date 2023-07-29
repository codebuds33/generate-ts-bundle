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

#[AsCommand(
    name: 'codebuds:generate-ts:enums',
    description: 'Generate TS enums from Php Enums',
)]
class GenerateTsEnumsCommand extends Command
{
    use GenerateTsFilesCommandTrait;

    public function __construct(
        private string                          $inputDirectory,
        private string                          $outputDirectory,
        private string                          $namespace,
        private readonly FileGenerationService  $fileGenerationService,
        private readonly FileInformationService $fileInformationService,
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

        $io->title('Generate TypeScript Enums');

        $force = $input->getOption('force');

        $this->initArguments($input);

        try {
            $this->generateTsEnums($io, $force);
        } catch (ReflectionException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws ReflectionException
     */
    private function generateTsEnums(SymfonyStyle $io, bool $force): void
    {
        $files = $this->fileInformationService->getFiles($this->inputDirectory);

        if (!$force) {
            $fileNames = $this->fileInformationService->getFileNames($files);

            $io->info('Found the following enums : ');
            $io->listing($fileNames);

            $io->info('use --force to generate the typescript enums');
            return;
        }

        $io->progressStart(count($files));

        foreach ($files as $file) {
            $output = $this->fileGenerationService->generateTypescriptEnumFileContent(
                file: $file,
                inputDirectory: $this->inputDirectory,
                namespace: $this->namespace
            );

            $typeName = str_replace($this->inputDirectory, $this->outputDirectory, (string)$file);
            $typePath = str_replace('.php', '.ts', $typeName);

            if (file_exists($typePath)) {
                $existingContent = file_get_contents($typePath);
                if ($existingContent === $output) {
                    $io->note(sprintf('No changes for %s', $typePath));
                    $io->progressAdvance();
                    continue;
                }
            }

            if (!is_dir(dirname($typePath)) && !mkdir($concurrentDirectory = dirname($typePath), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            file_put_contents($typePath, $output);
            $io->info(sprintf('%s generated for %s', $typePath, $file));
        }
        $io->progressFinish();
    }
}
