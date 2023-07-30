<?php

namespace CodeBuds\GenerateTsBundle\Command;

use CodeBuds\GenerateTsBundle\Service\FileGenerationService;
use CodeBuds\GenerateTsBundle\Service\FileInformationService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codebuds:generate-ts:types',
    description: 'Generate TS types from Symfony Entities',
)]
class GenerateTsTypesCommand extends Command
{
    use GenerateTsFilesCommandTrait;

    public function __construct(
        private string $inputDirectory,
        private string $outputDirectory,
        private string $namespace,
        private readonly FileGenerationService $fileGenerationService,
        private readonly FileInformationService $fileInformationService,
        private bool $verbose = false,
        private ?bool $debug = false,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addDefaultConfiguration();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate TypeScript Types');

        $force = $input->getOption('force');
        $this->verbose = $input->getOption('verbose');
        $this->debug = $input->getOption('debug');

        $this->initArguments($input);

        try {
            $this->generateTsTypes($io, $force);
        } catch (\ReflectionException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \ReflectionException
     */
    private function generateTsTypes(SymfonyStyle $io, bool $force): void
    {
        $files = $this->fileInformationService->getFiles($this->inputDirectory);

        if (!$force) {
            $fileNames = $this->fileInformationService->getFileNames($files);

            $io->info('Found the following entities : ');
            $io->listing($fileNames);

            $io->info('use --force to generate the typescript types');

            return;
        }

        $io->progressStart(count($files));

        foreach ($files as $file) {
            if($this->debug) {
                $io->info(sprintf("Working on file %s", $file));
            }

            try {
                $output = $this->fileGenerationService->generateTypescriptTypeFileContent(
                    file: $file,
                    inputDirectory: $this->inputDirectory,
                    namespace: $this->namespace
                );
            } catch (Exception $e) {
                if($this->debug) {
                    $io->info(sprintf("Exception %s", $e->getMessage()));
                }
                continue;
            }

            $typeName = str_replace($this->inputDirectory, $this->outputDirectory, (string) $file);
            $typePath = str_replace('.php', '.ts', $typeName);

            if (file_exists($typePath)) {
                $existingContent = file_get_contents($typePath);
                if ($existingContent === $output) {
                    if($this->verbose) {
                        $io->note(sprintf('No changes for %s', $typePath));
                    }
                    $io->progressAdvance();
                    continue;
                }
            }

            if (!is_dir(dirname($typePath)) && !mkdir($concurrentDirectory = dirname($typePath), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            file_put_contents($typePath, $output);
            if($this->verbose) {
                $io->info(sprintf('%s generated for %s', $typePath, $file));
            }
        }
        $io->progressFinish();
    }
}
