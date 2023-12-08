<?php

namespace App\Test\Entity;

abstract class SomethingAbstract
{
    // Force Extending class to define this method
    abstract protected function getValue();

    abstract protected function prefixValue($prefix);

    // Common method
    public function printOut()
    {
        echo $this->getValue()."\n";
    }
}
