<?php
namespace Unit;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;
use Flow\Uploader;

class UploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    public $root;

    protected function setUp()
    {
        vfsStreamWrapper::register();
        $this->root = new vfsStreamDirectory('chunks');
        vfsStreamWrapper::setRoot($this->root);
    }

    public function testPruneChunks()
    {
        $newDir = vfsStream::newDirectory('1');
        $newDir->lastModified(time()-31);
        $newDir->lastModified(time());
        $fileFirst = vfsStream::newFile('file31');
        $fileFirst->lastModified(time()-31);
        $fileSecond = vfsStream::newFile('random_file');
        $fileSecond->lastModified(time()-30);
        $this->root->addChild($newDir);
        $this->root->addChild($fileFirst);
        $this->root->addChild($fileSecond);

        Uploader::pruneChunks($this->root->url(), 30);
        $this->assertTrue(file_exists($newDir->url()));
        $this->assertFalse(file_exists($fileFirst->url()));
        $this->assertTrue(file_exists($fileSecond->url()));
    }

}