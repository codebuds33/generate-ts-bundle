<?php

namespace CodeBuds\GenerateTsBundle\Service;

use Exception;

readonly class FileGenerationService
{
    public function __construct(private FileInformationService $fileInformationService)
    {
    }

    /**
     * @throws Exception
     */
    public function generateTypescriptInterfaceFileContent(string $file, string $inputDirectory, string $namespace): string
    {
        [
            'imports' => $imports,
            'name' => $name,
            'properties' => $properties,
        ] = $this->getFileInformation(file: $file, inputDirectory: $inputDirectory, namespace: $namespace);

        $output = $this->initOutput($imports);
        $output .= "export interface {$name} {\n" . implode("\n", $properties) . "\n}\n";
        return $output;
    }

    public function generateTypescriptTypeFileContent(string $file, string $inputDirectory, string $namespace): string
    {
        [
            'imports' => $imports,
            'name' => $name,
            'properties' => $properties,
        ] = $this->getFileInformation(file: $file, inputDirectory: $inputDirectory, namespace: $namespace);

        $output = $this->initOutput($imports);
        $output .= "export type {$name} = {\n" . implode("\n", $properties) . "\n}\n";
        return $output;
    }

    /**
     * @throws Exception
     */
    private function getFileInformation(string $file, string $inputDirectory, string $namespace): array
    {
        $information = $this->fileInformationService->getClassInformation(
            file: $file,
            inputDirectory: $inputDirectory,
            namespace: $namespace,
        );
        $typeScriptProperties = [];
        foreach ($information['properties'] as $property) {
            $tsType = $this->fileInformationService->mapPhpTypeToTsType(
                namespace: $namespace,
                phpType: $property["type"],
                target: $property["target"],
            );
            $typeScriptProperties[] = "  {$property['name']}: {$tsType};";
        }

        $interfaceName = $information['interface']['shortName'];

        return [
            'imports' => $information['imports'],
            'name' => $interfaceName,
            'properties' => $typeScriptProperties,
        ];
    }

    private function initOutput(array $imports): string
    {
        $output = '';

        foreach ($imports as $target) {
            $output .= sprintf("import {%s} from \"%s\"\n", $target['name'], $target['path']);
        }

        if ($output !== '') {
            $output .= "\n";
        }
        return $output;
    }
}
