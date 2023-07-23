<?php

namespace CodeBuds\WebPConversionBundle\Tests;

use CodeBuds\GenerateTsBundle\GenerateTsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new GenerateTsBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // TODO: Implement registerContainerConfiguration() method.
    }
}
