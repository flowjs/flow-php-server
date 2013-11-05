<?php
namespace Unit;

use Flow\File;
use Flow\Request;
use Flow\Config;
use Flow\Exception;
use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStream;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    public $request;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var vfsStreamDirectory
     */
    public $root;

    protected function setUp()
    {
        $this->request = new \ArrayObject([
            'flowChunkNumber' => 1,
            'flowChunkSize' => 1048576,
            'flowCurrentChunkSize' => 10,
            'flowTotalSize' => 10,
            'flowIdentifier' => '13632-prettifyjs',
            'flowFilename' => 'prettify.js',
            'flowRelativePath' => 'home/prettify.js',
            'flowTotalChunks' => 1
        ]);
        $this->config = new Config();
        vfsStreamWrapper::register();
        $this->root = new vfsStreamDirectory('chunks');
        vfsStreamWrapper::setRoot($this->root);
        $this->config->setTempDir($this->root->url());
    }

    public function testRequest()
    {
        $request = new Request($this->request);
        $this->assertEquals($request->getFileName(), 'prettify.js');
        $this->assertEquals($request->getTotalSize(), 10);
        $this->assertEquals($request->getIdentifier(), '13632-prettifyjs');
        $this->assertEquals($request->getRelativePath(), 'home/prettify.js');
        $this->assertEquals($request->getTotalChunks(), 1);
        $this->assertEquals($request->getDefaultChunkSize(), 1048576);
        $this->assertEquals($request->getCurrentChunkNumber(), 1);
        $this->assertEquals($request->getCurrentChunkSize(), 10);
    }

    public function testCheckChunk()
    {
        $request = new Request($this->request);
        $file = new File($this->config, $request);
        $this->assertFalse($file->checkChunk());

        $chunkName = sha1($request->getIdentifier()) . '_' . $request->getCurrentChunkNumber();
        $firstChunk = vfsStream::newFile($chunkName);
        $this->root->addChild($firstChunk);
        $this->assertTrue($file->checkChunk());

    }

    public function testValidateChunk()
    {
        $fileInfo = new \ArrayObject();
        $request = new Request($this->request, $fileInfo);
        $file = new File($this->config, $request);
        $this->assertFalse($file->validateChunk());

        $fileInfo->exchangeArray([
            'size' => 10,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => ''
        ]);
        $this->assertTrue($file->validateChunk());

        $fileInfo->exchangeArray([
            'size' => 9,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => ''
        ]);
        $this->assertFalse($file->validateChunk());

        $fileInfo->exchangeArray([
            'size' => 10,
            'error' => UPLOAD_ERR_EXTENSION,
            'tmp_name' => ''
        ]);
        $this->assertFalse($file->validateChunk());
    }

    public function testValidateFile()
    {
        $this->request['flowTotalSize'] = 10;
        $this->request['flowTotalChunks'] = 3;
        $request = new Request($this->request);
        $file = new File($this->config, $request);

        $this->assertFalse($file->validateFile());

        $chunkPrefix = sha1($request->getIdentifier()) . '_';
        $firstChunk = vfsStream::newFile($chunkPrefix . '1');
        $firstChunk->setContent('123');
        $this->root->addChild($firstChunk);

        $secondChunk = vfsStream::newFile($chunkPrefix . '2');
        $secondChunk->setContent('456');
        $this->root->addChild($secondChunk);

        $lastChunk = vfsStream::newFile($chunkPrefix . '3');
        $lastChunk->setContent('7890');
        $this->root->addChild($lastChunk);

        $this->assertTrue($file->validateFile());

        $lastChunk->setContent('789');
        $this->assertFalse($file->validateFile());
        $this->request['flowTotalSize'] = 9;
        $this->assertTrue($file->validateFile());
        $secondChunk->rename('4');
        $this->assertFalse($file->validateFile());
        $secondChunk->rename($chunkPrefix . '2');//restore
        unlink($lastChunk->url());
        $this->assertFalse($file->validateFile());
    }

    public function testDeleteChunks()
    {
        $this->request['flowTotalChunks'] = 4;
        $fileInfo = new \ArrayObject();
        $request = new Request($this->request, $fileInfo);
        $file = new File($this->config, $request);

        $chunkPrefix = sha1($request->getIdentifier()) . '_';
        $firstChunk = vfsStream::newFile($chunkPrefix . 1);
        $this->root->addChild($firstChunk);
        $secondChunk = vfsStream::newFile($chunkPrefix . 3);
        $this->root->addChild($secondChunk);

        $thirdChunk = vfsStream::newFile('other');
        $this->root->addChild($thirdChunk);

        $this->assertTrue(file_exists($firstChunk->url()));
        $this->assertTrue(file_exists($secondChunk->url()));
        $this->assertTrue(file_exists($thirdChunk->url()));

        $file->deleteChunks();
        $this->assertFalse(file_exists($firstChunk->url()));
        $this->assertFalse(file_exists($secondChunk->url()));
        $this->assertTrue(file_exists($thirdChunk->url()));
    }

    public function testSave()
    {
        $this->request['flowTotalChunks'] = 3;
        $request = new Request($this->request);
        $file = new File($this->config, $request);

        $chunkPrefix = sha1($request->getIdentifier()) . '_';

        $chunk = vfsStream::newFile($chunkPrefix . '1', 0777);
        $chunk->setContent('0123');
        $this->root->addChild($chunk);

        $chunk = vfsStream::newFile($chunkPrefix . '2', 0777);
        $chunk->setContent('456');
        $this->root->addChild($chunk);

        $chunk = vfsStream::newFile($chunkPrefix . '3', 0777);
        $chunk->setContent('789');
        $this->root->addChild($chunk);

        $filePath = $this->root->url() . DIRECTORY_SEPARATOR . 'file';
        $this->assertTrue($file->save($filePath));
        $this->assertTrue(file_exists($filePath));
        $this->assertEquals($request->getTotalSize(), filesize($filePath));
    }

    public function testSaveLock()
    {
        $request = new Request($this->request);
        $file = new File($this->config, $request);
        $filePath = $this->root->url() . DIRECTORY_SEPARATOR . 'file';

        $fh = fopen($filePath, 'wb');
        $this->assertTrue(flock($fh, LOCK_EX));
        try {
            // practically on a normal file system exception would not be thrown, this happens
            // because vfsStreamWrapper does not support locking with block
            $file->save($filePath);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('Failed to lock file', $e->getMessage());
        }
    }
}