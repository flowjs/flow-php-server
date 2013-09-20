<?php

namespace Unit;

class AutoloadTest extends \PHPUnit_Framework_TestCase
{
    public function testClassesExist()
    {
        $autoloader = new \Resumable\Autoloader();
        $autoloader->autoload('noclass');
        $this->assertFalse(class_exists('noclass', false));
        $autoloader->autoload('Resumable\NoClass');
        $this->assertFalse(class_exists('Resumable\NoClass', false));
        $autoloader->autoload('Resumable\File');
        $this->assertTrue(class_exists('Resumable\File'));
        \Resumable\Autoloader::register();
    }
}