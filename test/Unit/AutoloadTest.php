<?php

namespace Unit;

class AutoloadTest extends \PHPUnit_Framework_TestCase
{
    public function testClassesExist()
    {
        $autoloader = new \Flow\Autoloader();
        $autoloader->autoload('noclass');
        $this->assertFalse(class_exists('noclass', false));
        $autoloader->autoload('Flow\NoClass');
        $this->assertFalse(class_exists('Flow\NoClass', false));
        $autoloader->autoload('Flow\File');
        $this->assertTrue(class_exists('Flow\File'));
        \Flow\Autoloader::register();
    }
}