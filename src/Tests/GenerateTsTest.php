<?php

namespace CodeBuds\GenerateTsBundle\Tests;

use CodeBuds\GenerateTsBundle\Command\GenerateTsInterfacesCommand;
use CodeBuds\GenerateTsBundle\Service\FileInformationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class GenerateTsTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $files = [
            'IngredientCategory' => [
                'generatedPath' => '/output/IngredientCategory.ts',
                'expectedPath' => '/expected/IngredientCategory.ts',
            ],
            'Tomato' => [
                'generatedPath' => '/output/Ingredients/Tomato.ts',
                'expectedPath' => '/expected/Ingredients/Tomato.ts',
            ],
            'Cucumber' => [
                'generatedPath' => '/output/Ingredients/Cucumber.ts',
                'expectedPath' => '/expected/Ingredients/Cucumber.ts',
            ],
            'Salade' => [
                'generatedPath' => '/output/Meals/Salade.ts',
                'expectedPath' => '/expected/Meals/Salade.ts',
            ],
        ];


        # Remove any previously generated files before running the test
        $filesystem = new Filesystem();
        foreach ($files as $file) {
            $filesystem->remove(__DIR__ . $file['generatedPath']);
        }


        $application = new Application();
        $application->add(new GenerateTsInterfacesCommand(
            inputDirectory: __DIR__ . '/data',
            outputDirectory: __DIR__ . '/output',
            namespace: 'App\Test\\',
            fileInformationService: new FileInformationService()
        ));

        $command = $application->find('codebuds:generate-ts:interfaces');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true,]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('IngredientCategory.ts generated for App\Test\IngredientCategory', $this->trimOutput($output));

        foreach ($files as $file) {
            $this->assertTrue($filesystem->exists(__DIR__ . $file['generatedPath']));
            $this->assertFileEquals(__DIR__ . $file['generatedPath'], __DIR__ . $file['expectedPath']);
        }

    }

    private function trimOutput(string $output): string
    {
        $cleanedString = preg_replace('/\s+/', ' ', $output);
        return str_replace(["\n", "\r", "\t"], '', $cleanedString);
    }
}
