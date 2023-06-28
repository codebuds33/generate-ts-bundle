<?php

namespace CodeBuds\GenerateTsBundle\Command;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use ReflectionClass;
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
    private string $namespace;

    public function __construct(
        private readonly string $inputDirectory,
        private readonly string $outputDirectory,
        string                  $namespace
    )
    {
        $this->namespace = str_replace('/', '//', $namespace);
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Create new TypeScript Interfaces');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate TypeScript Interfaces');

        $force = $input->getOption('force');

        try {
            $this->generateTsInterfaces($io, $force);
        } catch (\ReflectionException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \ReflectionException
     */
    private function generateTsInterfaces(SymfonyStyle $io, bool $force): void
    {
        $directory = new \RecursiveDirectoryIterator($this->inputDirectory);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = [];
        foreach ($iterator as $info) {
            if ($info->isFile() && $info->getExtension() === 'php') {
                $files[] = $info->getPathname();
            }
        }

        if (!$force) {
            $fileNames = array_map(static function ($file) {
                return $file;
            }, $files);

            $io->info('Found the following entities : ');
            $io->listing($fileNames);

            $io->info('use --force to generate the typescript interfaces');
            return;
        }

        $io->progressStart(count($files));

        foreach ($files as $file) {
            $relativePath = ltrim(str_replace($this->inputDirectory, '', $file), '/');
            $className = $this->namespace . str_replace(['.php', '/'], ['', '\\'], $relativePath);

            $reflector = new ReflectionClass($className);

            if ($reflector->isAbstract()) {
                continue;
            }

            $properties = $reflector->getProperties();
            $typeScriptProperties = [];

            foreach ($properties as $property) {
                $columnAttributes = $property->getAttributes(Column::class);
                $manyToOneAttributes = $property->getAttributes(ManyToOne::class);
                $oneToManyAttributes = $property->getAttributes(OneToMany::class);
                $joinColumnAttributes = $property->getAttributes(JoinColumn::class);

                if (empty($columnAttributes) && empty($manyToOneAttributes) && empty($oneToManyAttributes) && empty($joinColumnAttributes)) {
                    continue;
                }

                $propertyName = $property->getName();
                $propertyType = $property->getType();
                $propertyType = $propertyType?->getName();

                $target = null;
                if ($propertyType === 'Doctrine\Common\Collections\Collection') {
                    $reflexionAttribute = $oneToManyAttributes[0];
                    $target = $reflexionAttribute->getArguments()['targetEntity'];
                }

                $tsType = $this->mapPhpTypeToTsType($propertyType, $target);
                $typeScriptProperties[] = "  {$propertyName}: {$tsType};";
            }

            $interfaceName = $reflector->getShortName();
            $typeScriptInterface = "export interface {$interfaceName} {\n" . implode("\n", $typeScriptProperties) . "\n}\n";

            $typeName = str_replace($this->inputDirectory, $this->outputDirectory, $file);
            $typePath = str_replace('.php', '.ts', $typeName);

            if (file_exists($typePath)) {
                $existingContent = file_get_contents($typePath);
                if ($existingContent === $typeScriptInterface) {
                    $io->note(sprintf('No changes for %s', $typePath));
                    $io->progressAdvance();
                    continue;
                }
            }

            if (!is_dir(dirname($typePath)) && !mkdir($concurrentDirectory = dirname($typePath), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            file_put_contents($typePath, $typeScriptInterface);
            $io->info(sprintf('generated %s', $typePath));
        }
        $io->progressFinish();
    }

    private function mapPhpTypeToTsType(?string $phpType, ?string $target = null): string
    {
        if ($target) {
            return sprintf('Array<%s>', $target);
        }

        if ($phpType === null) {
            return 'unknown';
        }

        $mapping = [
            'string' => 'string',
            'text' => 'string',
            'int' => 'number',
            'integer' => 'number',
            'float' => 'number',
            'double' => 'number',
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'DateTime' => 'Date',
            'DateTimeImmutable' => 'Date',
        ];

        if (isset($mapping[$phpType])) {
            return $mapping[$phpType];
        }

        // Check if the PHP type is an entity and extract the class name
        if (preg_match('/^' . preg_quote($this->namespace, '/') . '(.+)$/', $phpType, $matches)) {
            $parts = explode('\\',$matches[1]);
            return end($parts);
        }

        return 'unknown';
    }
}
