<?php

namespace CodeBuds\GenerateTsBundle\Tests;

use CodeBuds\GenerateTsBundle\Command\GenerateTsInterfacesCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class GenerateTsTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $generatedFilePath = __DIR__ . '/output/IngredientCategory.ts';
        $expectedFilePath = __DIR__ . '/expected/IngredientCategory.ts';

        # Remove any previously generated files before running the test
        $filesystem = new Filesystem();
        $filesystem->remove($generatedFilePath);

        $application = new Application();
        $application->add(new GenerateTsInterfacesCommand(
            inputDirectory: __DIR__ . '/data',
            outputDirectory: __DIR__ . '/output',
            namespace: 'App\Test\\'
        ));

        $command = $application->find('codebuds:generate-ts:interfaces');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('IngredientCategory.ts generated for App\Test\IngredientCategory', $this->trimOutput($output));

        $this->assertTrue($filesystem->exists($generatedFilePath));
        $this->assertFileEquals($generatedFilePath, $expectedFilePath);
    }

    private function trimOutput(string $output): string
    {
        $cleanedString = preg_replace('/\s+/', ' ', $output);
        return str_replace(["\n", "\r", "\t"], '', $cleanedString);
    }
}
