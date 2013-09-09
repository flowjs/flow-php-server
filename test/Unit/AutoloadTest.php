<?php

namespace Unit;

class AutoloadTest extends \PHPUnit_Framework_TestCase
{
    public function testClassesExist()
    {
        $this->assertTrue(class_exists('Resumable\File'));
    }
}