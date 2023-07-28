<?php

namespace CodeBuds\GenerateTsBundle\Service;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;

class FileInformationService
{

    public function getFiles(string $inputDirectory): array
    {
        $directory = new RecursiveDirectoryIterator($inputDirectory);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = [];
        foreach ($iterator as $info) {
            if ($info->isFile() && $info->getExtension() === 'php') {
                $files[] = $info->getPathname();
            }
        }
        return $files;
    }

    public function getFileNames(array $files): array
    {
        return array_map(static fn($file) => $file, $files);
    }

    public function getClassInformation(string $file, string $inputDirectory, string $namespace): ?array
    {
        $relativePath = ltrim(str_replace($inputDirectory, '', $file), '/');
        $className = $namespace . str_replace(['.php', '/'], ['', '\\'], $relativePath);
        $className = str_replace('/', '\\', $className);

        //We need to require the file to make sure the ReflectionClass will be able to create the class
        require_once $file;

        $reflector = new ReflectionClass($className);
        $shortName = $reflector->getShortName();

        if ($reflector->isAbstract()) {
            return null;
        }

        $properties = $reflector->getProperties();

        $data = [
            'interface' => [],
            'properties' => [],
            'imports' => [],
        ];

        $imports = [];


        foreach ($properties as $property) {
            $columnAttributes = $property->getAttributes(Column::class);
            $manyToOneAttributes = $property->getAttributes(ManyToOne::class);
            $manyToManyAttributes = $property->getAttributes(ManyToMany::class);
            $oneToManyAttributes = $property->getAttributes(OneToMany::class);
            $oneToOneAttributes = $property->getAttributes(OneToOne::class);
            $joinColumnAttributes = $property->getAttributes(JoinColumn::class);

            if (
                empty($columnAttributes) &&
                empty($manyToOneAttributes) &&
                empty($oneToManyAttributes) &&
                empty($manyToManyAttributes) &&
                empty($oneToOneAttributes) &&
                empty($joinColumnAttributes)
            ) {
                continue;
            }

            $propertyName = $property->getName();
            $propertyType = $property->getType();
            $propertyType = $propertyType?->getName();

            $target = null;

            if ($propertyType === 'Doctrine\Common\Collections\Collection') {
                //If it is a one to many get the target from the OneToMany Attributes, if it is a ManyToMany get it from those attributes
                if ($oneToManyAttributes) {
                    $reflexionAttribute = $oneToManyAttributes[0];
                } elseif ($manyToManyAttributes) {
                    $reflexionAttribute = $manyToManyAttributes[0];
                } else {
                    throw new Exception(printf('No target found for the %s Collection on %s', $propertyName, $className));
                }
                $target = $reflexionAttribute->getArguments()['targetEntity'];
            }

            //If the property does not have a type but an attribute of Doctrine\ORM\Mapping\Column get the type from that attribute
            if ($propertyType === null) {
                /** @var ?ReflectionAttribute $mappingColumnAttribute */
                $mappingColumnAttributeArray = (array_filter(
                    $property->getAttributes(),
                    static fn($attribute) => $attribute->getName() === 'Doctrine\ORM\Mapping\Column'
                ));

                if ($mappingColumnAttributeArray) {
                    $mappingColumnAttribute = reset($mappingColumnAttributeArray);
                    $mappingColumnAttributeArguments = $mappingColumnAttribute->getArguments();
                    if (array_key_exists('type', $mappingColumnAttributeArguments)) {
                        $propertyType = $mappingColumnAttributeArguments['type'];
                    }
                }
            }

            //If the property is still not set see if it is Gedmo Blameable, if so it will be a string,
            // if the blameable was set as an entity the target will have been set
            if ($propertyType === null) {
                /** @var ?ReflectionAttribute $mappingColumnAttribute */
                $blameableAttributeArray = (array_filter(
                    $property->getAttributes(),
                    static fn($attribute) => $attribute->getName() === "Gedmo\Mapping\Annotation\Blameable"
                ));

                if ($blameableAttributeArray) {
                    $propertyType = 'string';
                }
            }

            //For a ManyToOne set the property as the target
            if ($this->checkIfTypeIsEntity(namespace: $namespace, phpType: $propertyType)) {
                $imports[] = $propertyType;
            }

            $data['properties'][] = [
                'type' => $propertyType,
                'target' => $target,
                'name' => $propertyName
            ];


            if ($target) {
                $imports[] = $target;
            }
        }

        foreach ($imports as $import) {
            $importData = $this->getImportData(targetClass: $import, namespace: $namespace, className: $className);
            if ($importData) {
                $data['imports'][$importData['name']] = $importData;
            }
        }

        $data['interface'] = [
            'shortName' => $shortName,
            'className' => $className,
        ];

        return $data;
    }

    private function checkIfTypeIsEntity(string $namespace, string $phpType): bool
    {
        $quotedNamespace = preg_quote(str_replace('/', '\\', $namespace), '/');

        if (preg_match(
            '/^' .
            $quotedNamespace .
            '(.+)$/',
            $phpType,
            $matches
        )) {
            return true;
        }

        return false;
    }

    private function getImportData(string $targetClass, string $namespace, string $className): ?array
    {
        $classNamePrefix = str_replace('/', '\\', $namespace);
        $target = str_replace($classNamePrefix, '', $targetClass);
        $class = str_replace($classNamePrefix, '', $className);

        //No need to import a class that references itself
        if ($target === $class) {
            return null;
        }

        $targetPathLevels = explode('\\', $targetClass);

        $targetName = end($targetPathLevels);

        $importPath = $this->generateImportPath($className, $targetClass);

        return [
            'name' => $targetName,
            'path' => $importPath
        ];
    }

    public function generateImportPath(string $className, string $targetClassName): string
    {
        $currentPath = str_replace('\\', '/', $className);
        $targetPath = str_replace('\\', '/', $targetClassName);

        $currentParts = explode('/', $currentPath);
        $targetParts = explode('/', $targetPath);

        // Calculate the common prefix of both paths
        $commonPrefix = [];
        for ($i = 0; $i < min(count($currentParts), count($targetParts)); $i++) {
            if ($currentParts[$i] === $targetParts[$i]) {
                $commonPrefix[] = $currentParts[$i];
            } else {
                break;
            }
        }

        // Calculate the relative path from the current class to the target class
        $parentCount = count($currentParts) - count($commonPrefix) - 1;
        $relativePath = str_repeat('../', $parentCount);

        if ($relativePath === '') {
            $relativePath = './';
        }

        $targetSubPath = implode('/', array_slice($targetParts, count($commonPrefix)));
        $relativePath .= $targetSubPath;

        return $relativePath;
    }

    public function mapPhpTypeToTsType(string $namespace, ?string $phpType, ?string $target = null): string
    {
        if ($target) {
            $parts = explode('\\', $target);
            return sprintf('Array<%s>', end($parts));
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
            'DateTimeInterface' => 'Date',
            'datetime' => 'Date',
        ];

        if (isset($mapping[$phpType])) {
            return $mapping[$phpType];
        }

        $quotedNamespace = preg_quote(str_replace('/', '\\', $namespace), '/');

        // Check if the PHP type is an entity and extract the class name
        if (preg_match(
            '/^' .
            $quotedNamespace .
            '(.+)$/',
            $phpType,
            $matches
        )) {
            $parts = explode('\\', $matches[1]);
            return end($parts);
        }

        return 'unknown';
    }
}
