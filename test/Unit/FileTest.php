<?php
namespace Unit;
use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStream;
/**
 * Class FileTest
 * @package Unit
 *
$file = new \Resumable\File($this->request);
$chunksDir = $file->init('../chunks');
$chunk = new \Resumable\Chunk($this->request);
if ($chunk->validate($_FILES['input'])) {
$chunk->overwirte($_FILES['input'], $chunksDir);
} else {
// error
}
if ($file->validate()) {
$file->save('../files');
}
 */

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
        $dir = $file->init($this->root->url(), 777);
        $vfsDir = $this->root->getChild($file->identifier);

        $this->assertTrue(file_exists($dir));
        $this->assertTrue(is_dir($dir));
        $this->assertNotNull($vfsDir);
        $this->assertFalse($file->validate());

        $chunk = vfsStream::newFile('1');
        $chunk->setContent('123');
        $vfsDir->addChild($chunk);

        $chunk = vfsStream::newFile('2');
        $chunk->setContent('123');
        $vfsDir->addChild($chunk);

        $chunk = vfsStream::newFile('3');
        $chunk->setContent('1234');
        $vfsDir->addChild($chunk);

        $this->assertTrue($file->validate());

    }
}