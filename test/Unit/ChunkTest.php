<?php
namespace Unit;

use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStream;

class ChunkTest extends \PHPUnit_Framework_TestCase
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
        $chunk = new \Resumable\Chunk($this->request);
        $this->assertEquals($chunk->index, 1);
        $this->assertEquals($chunk->size, 13632);
    }

    public function testExists()
    {
        $chunk = new \Resumable\Chunk($this->request);
        $this->assertFalse($chunk->exists(vfsStream::url('chunks')));

        $file = vfsStream::newFile('1');
        $this->root->addChild($file);
        $this->assertTrue($chunk->exists(vfsStream::url('chunks')));
    }

    public function testSave()
    {
        $chunk = new \Resumable\Chunk($this->request);
        $this->assertFalse($chunk->exists(vfsStream::url('chunks')));
        $file = vfsStream::newFile('1');
        $this->root->addChild($file);
        $chunk->save([
            'tmp_name' => vfsStream::url('chunks/1')
        ], $this->root);
    }

    public function testValidate()
    {
        $this->request['resumableCurrentChunkSize'] = 10;
        $chunk = new \Resumable\Chunk($this->request);
        $this->assertTrue($chunk->validate([
            'size' => 10,
            'error' => UPLOAD_ERR_OK,
        ]));
        $this->assertFalse($chunk->validate([
            'size' => 9,
            'error' => UPLOAD_ERR_OK,
        ]));
        $this->assertFalse($chunk->validate([
            'size' => 10,
            'error' => UPLOAD_ERR_EXTENSION,
        ]));
        $this->assertFalse($chunk->validate([]));
    }

    public function testDelete()
    {
        $chunk = new \Resumable\Chunk($this->request);
        $file = vfsStream::newFile('1');
        $this->root->addChild($file);
        $this->assertTrue($chunk->exists(vfsStream::url('chunks')));
        $this->assertTrue($chunk->delete($this->root));
        $this->assertFalse($chunk->exists(vfsStream::url('chunks')));
    }

    public function testPrefixSave()
    {
        $chunk = new \Resumable\Chunk($this->request, 'pre_');
        $this->assertFalse($chunk->exists(vfsStream::url('chunks')));
        $file = vfsStream::newFile('pre_1');
        $this->root->addChild($file);
        $this->assertTrue($chunk->exists(vfsStream::url('chunks')));
    }
}