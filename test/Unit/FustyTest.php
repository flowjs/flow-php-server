<?php
namespace Unit;

use Flow\FustyRequest;
use Flow\Config;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class FustyTest extends \PHPUnit_Framework_TestCase
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

    public function testValidateUpload()
    {
        $firstChunk = vfsStream::newFile('temp_file');
        $firstChunk->setContent('1234567890');
        $this->root->addChild($firstChunk);
        $fileInfo = new \ArrayObject([
            'size' => 10,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => $firstChunk->url()
        ]);
        $request =  new \ArrayObject([
            'flowIdentifier' => '13632-prettifyjs',
            'flowFilename' => 'prettify.js',
            'flowRelativePath' => 'home/prettify.js'
        ]);
        $fustyRequest = new FustyRequest($request, $fileInfo);

        $config = new Config();
        $config->setTempDir($this->root->url());

        $file = $this->getMock('Flow\File', array('_move_uploaded_file'), [$config, $fustyRequest]);
        $file->expects($this->once())
            ->method('_move_uploaded_file')
            ->will($this->returnCallback(function ($filename, $destination) {
                return rename($filename, $destination);
            }));
        $this->assertTrue($file->validateChunk());
        $this->assertFalse($file->validateFile());

        $this->assertTrue($file->saveChunk());
        $this->assertTrue($file->validateFile());
        $path = $this->root->url() . DIRECTORY_SEPARATOR . 'new';
        $this->assertTrue($file->save($path));
        $this->assertEquals(10, filesize($path));

    }

} 