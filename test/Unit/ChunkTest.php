<?php
namespace Unit;

use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStream;
use Flow\Exception;
use Flow\Chunk;


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
            'flowChunkNumber' => 1,
            'flowChunkSize' => 1048576,
            'flowCurrentChunkSize' => 13632,
            'flowTotalSize' => 13632,
            'flowIdentifier' => '13632-prettifyjs',
            'flowFilename' => 'prettify.js',
            'flowRelativePath' => 'home/prettify.js',
            'flowTotalChunks' => 3
        ];

        vfsStreamWrapper::register();
        $this->root = new vfsStreamDirectory('chunks');
        vfsStreamWrapper::setRoot($this->root);
    }

    public function testRequest()
    {
        $chunk = new Chunk($this->request);
        $this->assertEquals($chunk->index, 1);
        $this->assertEquals($chunk->size, 13632);
    }

    public function testExists()
    {
        $chunk = new Chunk($this->request);
        $this->assertFalse($chunk->exists(vfsStream::url('chunks')));
        $file = vfsStream::newFile('1');
        $this->root->addChild($file);
        $this->assertTrue($chunk->exists(vfsStream::url('chunks')));
    }

    public function testSave()
    {
        $chunk = $this->getMock('\Flow\Chunk', array('move_uploaded_file'), [$this->request]);
        $chunk->expects($this->any())
            ->method('move_uploaded_file')
            ->will($this->returnCallback(function ($filename, $destination) {
                return rename($filename, $destination);
            }));

        $this->assertFalse($chunk->exists(vfsStream::url('chunks')));
        $file = vfsStream::newFile('tmp', 0777);
        $this->root->addChild($file);
        $this->assertTrue($chunk->save([
            'tmp_name' => $file->url()
        ], vfsStream::url('chunks')));
        $this->assertTrue($chunk->exists(vfsStream::url('chunks')));

        // overwrite
        $file = vfsStream::newFile('tmp', 0777);
        $this->root->addChild($file);
        $this->assertTrue($chunk->save([
            'tmp_name' => $file->url()
        ], vfsStream::url('chunks')));
    }

    public function testValidate()
    {
        $this->request['flowCurrentChunkSize'] = 10;
        $chunk = new Chunk($this->request);
        $this->assertTrue($chunk->validate([
            'size' => 10,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => ''
        ]));
        $this->assertFalse($chunk->validate([
            'size' => 9,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => ''
        ]));
        $this->assertFalse($chunk->validate([
            'size' => 10,
            'error' => UPLOAD_ERR_EXTENSION,
            'tmp_name' => ''
        ]));
        try {
            $this->assertFalse($chunk->validate([]));
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function testDelete()
    {
        $chunk = new Chunk($this->request);
        $file = vfsStream::newFile('1');
        $this->root->addChild($file);
        $this->assertTrue($chunk->exists($this->root->url()));
        $this->assertTrue($chunk->delete($this->root->url()));
        $this->assertFalse($chunk->exists($this->root->url()));
    }

    public function testPrefixSave()
    {
        $chunk = new Chunk($this->request, 'pre_');
        $this->assertFalse($chunk->exists($this->root->url()));
        $file = vfsStream::newFile('pre_1');
        $this->root->addChild($file);
        $this->assertTrue($chunk->exists($this->root->url()));
    }
}