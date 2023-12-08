<?php

namespace CodeBuds\GenerateTsBundle\Service;

class FileGenerationService
{
    public function __construct(private readonly FileInformationService $fileInformationService)
    {
    }

    /**
     * @throws \Exception
     */
    public function generateTypescriptInterfaceFileContent(string $file, string $inputDirectory, string $namespace): string
    {
        [
            'imports' => $imports,
            'name' => $name,
            'properties' => $properties,
        ] = $this->getFileInformation(file: $file, inputDirectory: $inputDirectory, namespace: $namespace);

        $output = $this->initOutput($imports);
        $output .= "export interface {$name} {\n".implode("\n", $properties)."\n}\n";

        return $output;
    }

    /**
     * @throws \Exception
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
                phpType: $property['type'],
                target: $property['target'],
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

    public function generateTypescriptTypeFileContent(string $file, string $inputDirectory, string $namespace): string
    {
        [
            'imports' => $imports,
            'name' => $name,
            'properties' => $properties,
        ] = $this->getFileInformation(file: $file, inputDirectory: $inputDirectory, namespace: $namespace);

        $output = $this->initOutput($imports);
        $output .= "export type {$name} = {\n".implode("\n", $properties)."\n}\n";

        return $output;
    }

    /**
     * @throws \Exception
     */
    public function generateTypescriptEnumFileContent(string $file, string $inputDirectory, string $namespace): string
    {
        [
            'interface' => $interface,
            'properties' => $properties,
        ] = $this->fileInformationService->getEnumInformation(
            file: $file,
            inputDirectory: $inputDirectory,
            namespace: $namespace,
        );
        $output = "export enum {$interface['shortName']} {\n";

        foreach ($properties as $property) {
            if (property_exists($property, 'value')) {
                $value = $property->value;
                if (!is_int($value)) {
                    $value = sprintf('"%s"', $value);
                }

                $output .= sprintf("\t%s = %s,\n", $property->name, $value);
            } else {
                $output .= sprintf("\t%s,\n", $property->name);
            }
        }

        $output .= "}\n";

        return $output;
    }
}
