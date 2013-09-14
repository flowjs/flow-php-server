<?php
namespace Unit;

use \org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;
use Resumable\Uploader;

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
        $newDir->lastModified(time());
        $newFile = vfsStream::newFile('file_1');
        $newDir->addChild($newFile);

        $oldDir = vfsStream::newDirectory('2');
        $oldFile = vfsStream::newFile('file_1');
        $oldDir->addChild($oldFile);
        $oldDir->lastModified(time() - 60);


        $randomFile = vfsStream::newFile('random_file');

        $this->root->addChild($newDir);
        $this->root->addChild($oldDir);
        $this->root->addChild($randomFile);

        Uploader::pruneChunks($this->root->url(), 30);
        $this->assertTrue(file_exists($newDir->url()));
        $this->assertTrue(file_exists($newFile->url()));
        $this->assertFalse(file_exists($oldDir->url()));
        $this->assertFalse(file_exists($oldFile->url()));
        $this->assertTrue(file_exists($randomFile->url()));
    }

}