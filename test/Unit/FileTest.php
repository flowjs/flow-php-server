<?php
namespace Unit;
use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStream;
use Resumable\Exception;

class FileTest extends \PHPUnit_Framework_TestCase
{
    public $request;
    /**
     * @var vfsStreamDirectory
     */
    public $root;

    protected function setUp()
    {
        $this->request = [
            'resumableChunkNumber' => 1,
            'resumableChunkSize' => 1048576,
            'resumableCurrentChunkSize' => 13632,
            'resumableTotalSize' => 13632,
            'resumableIdentifier' => '13632-prettifyjs',
            'resumableFilename' => 'prettify.js',
            'resumableRelativePath' => 'home/prettify.js',
            'resumableTotalChunks' => 3
        ];
        vfsStreamWrapper::register();
        $this->root = new vfsStreamDirectory('chunks');
        vfsStreamWrapper::setRoot($this->root);
    }

    public function testRequest()
    {
        $file = new \Resumable\File($this->request);
        $this->assertEquals($file->name, 'prettify.js');
        $this->assertEquals($file->size, 13632);
        $this->assertEquals($file->identifier, '13632-prettifyjs');
        $this->assertEquals($file->relativePath, 'home/prettify.js');
        $this->assertEquals($file->totalChunks, 3);
        $this->assertEquals($file->defaultChunkSize, 1048576);
    }

    public function testInit()
    {
        $file = new \Resumable\File($this->request);
        $dir = $file->init($this->root->url());
        $this->assertNotEquals($dir, $this->root->url());
        $this->assertTrue(is_dir($dir));
    }

    public function testValidate()
    {
        $this->request['resumableTotalSize'] = 10;
        $file = new \Resumable\File($this->request);
        $dir = $file->init($this->root->url(), 0777);
        $vfsDir = $this->root->getChild($file->chunksDir);

        $this->assertTrue(file_exists($dir));
        $this->assertTrue(is_dir($dir));
        $this->assertNotNull($vfsDir);
        $this->assertFalse($file->validate());

        $firstChunk = vfsStream::newFile('1');
        $firstChunk->setContent('123');
        $vfsDir->addChild($firstChunk);

        $secondChunk = vfsStream::newFile('2');
        $secondChunk->setContent('456');
        $vfsDir->addChild($secondChunk);

        $lastChunk = vfsStream::newFile('3');
        $lastChunk->setContent('7890');
        $vfsDir->addChild($lastChunk);

        $this->assertTrue($file->validate());

        $lastChunk->setContent('789');
        $this->assertFalse($file->validate());
        $file->size = 9;
        $this->assertTrue($file->validate());
        $secondChunk->rename('4');
        $this->assertFalse($file->validate());
        $secondChunk->rename('2');//restore
        unlink($lastChunk->url());
        $this->assertFalse($file->validate());
    }

    public function testDeleteChunks()
    {
        $file = new \Resumable\File($this->request);
        $dir = $file->init($this->root->url(), 0777);
        $vfsDir = $this->root->getChild($file->chunksDir);
        $chunk = vfsStream::newFile('1', 0777);
        $chunk->setContent('123');
        $vfsDir->addChild($chunk);

        $this->assertTrue(file_exists($dir));
        $this->assertTrue(is_dir($dir));

        $this->assertTrue($file->deleteChunks());
        $this->assertFalse(file_exists($dir));
        $this->assertFalse(is_dir($dir));
    }

    public function testSave()
    {
        $this->request['resumableTotalSize'] = 9;
        $file = new \Resumable\File($this->request);
        $dir = $file->init($this->root->url(), 0777);
        $vfsDir = $this->root->getChild($file->chunksDir);
        $chunk = vfsStream::newFile('1', 0777);
        $chunk->setContent('123');
        $vfsDir->addChild($chunk);

        $chunk = vfsStream::newFile('2', 0777);
        $chunk->setContent('456');
        $vfsDir->addChild($chunk);

        $chunk = vfsStream::newFile('3', 0777);
        $chunk->setContent('789');
        $vfsDir->addChild($chunk);

        $filePath = $this->root->url() . DIRECTORY_SEPARATOR . 'file';
        $file->save($filePath);
        $this->assertTrue(file_exists($filePath));
        $this->assertEquals($this->request['resumableTotalSize'], filesize($filePath));
    }

    public function testSaveLock()
    {
        $this->request['resumableTotalSize'] = 9;
        $file = new \Resumable\File($this->request);
        $dir = $file->init($this->root->url(), 0777);
        $filePath = $this->root->url() . DIRECTORY_SEPARATOR . 'file';

        $fh = fopen($filePath, 'wb');
        $this->assertTrue(flock($fh, LOCK_EX));
        try {
            $file->save($filePath);
            $this->fail();
        } catch (Exception $e) {

        }
    }
}