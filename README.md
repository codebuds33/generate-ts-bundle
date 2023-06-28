# CodeBuds TypeScript Generator Bundle

The CodeBuds TypeScript Generator Bundle is a Symfony bundle designed to improve developer experience (DX) by automatically generating TypeScript (TS) files based on PHP files. This bundle scans your PHP entities and creates corresponding TypeScript interfaces, significantly reducing the amount of time required to define these interfaces manually.
Installation

To install the bundle, use composer:

```bash
composer require codebuds/ts-generator-bundle
```

##Configuration

This bundle provides three configurable parameters with default values:

- namespace: The PHP namespace for your entities. Default value is 'App\Entity\'.
- output_directory: The directory where the generated TypeScript files will be stored. Default value is '%kernel.project_dir%/assets/types'.
- input_directory: The directory containing the PHP files that will be used for TypeScript generation. Default value is '%kernel.project_dir%/src/Entity'.

You can overwrite these default configurations by creating a YAML file named inside your config directory:

```yaml
#config/generate_ts.yaml
generate_ts:
    namespace: 'App\CustomNamespace\'
    output_directory: '%kernel.project_dir%/custom/types'
    input_directory: '%kernel.project_dir%/custom/Entity'
```

## Usage

To generate TypeScript interfaces, run the following command:

```bash
php bin/console codebuds:generate-ts:interfaces --force
```

Here is the output when the command generates new interfaces:

```bash
Generate TypeScript Interfaces
==============================

 0/3 [░░░░░░░░░░░░░░░░░░░░░░░░░░░░]   0%                                                                                                                        
 [INFO] generated /srv/app/assets/types/IngredientCategory.ts                                                           
                                                                                                                        

                                                                                                                        
 [INFO] generated /srv/app/assets/types/User.ts                                                                         
                                                                                                                        

                                                                                                                        
 [INFO] generated /srv/app/assets/types/Ingredients/Tomato.ts                                                           
                                                                                                                        

 3/3 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
```

If nothing changes in the output you will get 


```bash
Generate TypeScript Interfaces
==============================

 0/3 [░░░░░░░░░░░░░░░░░░░░░░░░░░░░]   0% 
 ! [NOTE] No changes for /srv/app/assets/types/IngredientCategory.ts                                                    

 ! [NOTE] No changes for /srv/app/assets/types/User.ts                                                                  

 ! [NOTE] No changes for /srv/app/assets/types/Ingredients/Tomato.ts                                                    

 3/3 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

```

## Output 

The script will try to match the types. If you have an entity that has relations it will map those to the TypeScript Interfaces.

With an Entity like : 

```php
<?php

namespace App\Entity;

use App\Repository\IngredientCategoryRepository;
use App\State\IngredientCategory\CategoryChildrenProvider;
use App\State\IngredientCategory\RootCategoriesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: IngredientCategoryRepository::class)]
class IngredientCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft')]
    private ?int $lft = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl')]
    private ?int $lvl = null;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt')]
    private ?int $rgt = null;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: 'IngredientCategory')]
    #[ORM\JoinColumn(nullable: true)]
    private ?IngredientCategory $root = null;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'IngredientCategory', inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true)]
    private ?IngredientCategory $parent = null;

    #[MaxDepth(1)]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: 'IngredientCategory')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private ?Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): IngredientCategory
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): IngredientCategory
    {
        $this->name = $name;
        return $this;
    }

    public function getRoot(): ?IngredientCategory
    {
        return $this->root;
    }

    public function getParent(): ?IngredientCategory
    {
        return $this->parent;
    }

    public function setParent(?IngredientCategory $parent): IngredientCategory
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function setChildren(?Collection $children): IngredientCategory
    {
        $this->children = $children;
        return $this;
    }
}
```

The output will be :

```ts
export interface IngredientCategory {
  id: number;
  name: string;
  lft: number;
  lvl: number;
  rgt: number;
  root: IngredientCategory;
  parent: IngredientCategory;
  children: Array<IngredientCategory>;
}
```

### Subdirectories

The bundle will automatically scan through all PHP files in the input_directory, including those in subdirectories. The resulting TypeScript files will maintain the same directory structure as your PHP entities.

For example, if you have a PHP entity at src/Entity/User/Details.php, the corresponding TypeScript file would be located at assets/types/User/Details.ts.
