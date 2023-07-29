<?php

namespace CodeBuds\GenerateTsBundle\Tests;

use CodeBuds\GenerateTsBundle\Command\GenerateTsEnumsCommand;
use CodeBuds\GenerateTsBundle\Command\GenerateTsInterfacesCommand;
use CodeBuds\GenerateTsBundle\Command\GenerateTsTypesCommand;
use CodeBuds\GenerateTsBundle\Service\FileGenerationService;
use CodeBuds\GenerateTsBundle\Service\FileInformationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class GenerateTsTest extends KernelTestCase
{
    private const ENTITY_INPUT_FILES = [
        'Root',
        'Sub1/SubEntity1',
        'Sub1/SubSub1/SubSubEntity1',
        'Sub1/SubSub1/SubSubEntity2',
        'Sub2/SubEntity2',
        'Gedmo/Tree',
    ];

    private const ENUM_INPUT_FILES = [
        'BackedInt',
        'BackedString',
        'NotBacked',
    ];

    public function testGenerateInterfaces(): void
    {
        $filePaths = self::getFilePaths("interfaces");

        # Remove any previously generated files before running the test
        $filesystem = new Filesystem();
        foreach ($filePaths as $file) {
            $filesystem->remove(__DIR__ . $file['generatedPath']);
        }


        $application = new Application();
        $application->add(new GenerateTsInterfacesCommand(
            inputDirectory: __DIR__ . '/data/Entity',
            outputDirectory: __DIR__ . '/output/interfaces',
            namespace: 'App\Test\Entity\\',
            fileGenerationService: new FileGenerationService(
                fileInformationService: new FileInformationService()
            ),
            fileInformationService: new FileInformationService()
        ));

        $command = $application->find('codebuds:generate-ts:interfaces');

        //Test without force
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Generate TypeScript Interfaces ============================== [INFO] Found the following entities : *', $this->trimOutput($output));
        $this->assertStringContainsString(
            'use --force to generate the typescript interface', $this->trimOutput($output));

        //Test with force
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Root.ts generated', $this->trimOutput($output));

        foreach ($filePaths as $file => $paths) {
            $this->assertTrue($filesystem->exists(__DIR__ . $paths['generatedPath']));
            $this->assertFileEquals(
                __DIR__ . $paths['generatedPath'],
                __DIR__ . $paths['expectedPath'],
                sprintf('The output is different than expected for %s', $file)
            );
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] No changes', $this->trimOutput($output));

    }

    public function testGenerateTypes(): void
    {
        $filePaths = self::getFilePaths("types");

        # Remove any previously generated files before running the test
        $filesystem = new Filesystem();
        foreach ($filePaths as $file) {
            $filesystem->remove(__DIR__ . $file['generatedPath']);
        }


        $application = new Application();
        $application->add(new GenerateTsTypesCommand(
            inputDirectory: __DIR__ . '/data/Entity',
            outputDirectory: __DIR__ . '/output/types',
            namespace: 'App\Test\Entity\\',
            fileGenerationService: new FileGenerationService(
                fileInformationService: new FileInformationService()
            ),
            fileInformationService: new FileInformationService()
        ));

        $command = $application->find('codebuds:generate-ts:types');

        //Test without forcing
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Generate TypeScript Types ========================= [INFO] Found the following entities : *', $this->trimOutput($output));
        $this->assertStringContainsString(
            'use --force to generate the typescript types', $this->trimOutput($output));

        //Test with force
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Root.ts generated', $this->trimOutput($output));

        foreach ($filePaths as $file => $paths) {
            $this->assertTrue($filesystem->exists(__DIR__ . $paths['generatedPath']));
            $this->assertFileEquals(
                __DIR__ . $paths['generatedPath'],
                __DIR__ . $paths['expectedPath'],
                sprintf('The output is different than expected for %s', $file)
            );
        }

        //Run it again to see there are no changes
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] No changes', $this->trimOutput($output));
    }

    public function testGenerateEnums(): void
    {
        $filePaths = self::getFilePaths("enums", self::ENUM_INPUT_FILES);

        # Remove any previously generated files before running the test
        $filesystem = new Filesystem();
        foreach ($filePaths as $file) {
            $filesystem->remove(__DIR__ . $file['generatedPath']);
        }


        $application = new Application();
        $application->add(new GenerateTsEnumsCommand(
            inputDirectory: __DIR__ . '/data/Enum',
            outputDirectory: __DIR__ . '/output/enums',
            namespace: 'App\Test\Enum\\',
            fileGenerationService: new FileGenerationService(
                fileInformationService: new FileInformationService()
            ),
            fileInformationService: new FileInformationService()
        ));

        $command = $application->find('codebuds:generate-ts:enums');

        //Test without forcing
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Generate TypeScript Enums ========================= [INFO] Found the following enums : *', $this->trimOutput($output));
        $this->assertStringContainsString(
            'use --force to generate the typescript enums', $this->trimOutput($output));

        //Test with force
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('BackedString.ts generated', $this->trimOutput($output));

        foreach ($filePaths as $file => $paths) {
            $this->assertTrue($filesystem->exists(__DIR__ . $paths['generatedPath']));
            $this->assertFileEquals(
                __DIR__ . $paths['generatedPath'],
                __DIR__ . $paths['expectedPath'],
                sprintf('The output is different than expected for %s', $file)
            );
        }

        //Run it again to see there are no changes
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] No changes', $this->trimOutput($output));
    }
    private static function getFilePaths(string $subDir, array $files = self::ENTITY_INPUT_FILES): array
    {
        $filePaths = [];
        foreach ($files as $file) {
            $filePaths[$file] = [
                'generatedPath' => sprintf('/output/%s/%s.ts', $subDir, $file),
                'expectedPath' => sprintf('/expected/%s/%s.ts', $subDir, $file),
            ];
        }
        return $filePaths;
    }

    private function trimOutput(string $output): string
    {
        $cleanedString = preg_replace('/\s+/', ' ', $output);
        return str_replace(["\n", "\r", "\t"], '', $cleanedString);
    }
}
