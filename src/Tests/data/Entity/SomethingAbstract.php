<?php

namespace App\Test\Entity;

abstract class SomethingAbstract
{
    // Force Extending class to define this method
    public function printOut()
    {
        echo $this->getValue()."\n";
    }

    abstract protected function getValue();

    // Common method

    abstract protected function prefixValue($prefix);
}
