<?php

namespace Unit;


use ArrayObject;

class FlowUnitCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test request
	 *
	 * @var array
	 */
	protected $requestArr;

	/**
	 * $_FILES
	 *
	 * @var array
	 */
	protected $filesArr;

	protected function setUp()
	{
		$this->requestArr = new ArrayObject([
			'flowChunkNumber' => 1,
			'flowChunkSize' => 1048576,
			'flowCurrentChunkSize' => 10,
			'flowTotalSize' => 100,
			'flowIdentifier' => '13632-prettifyjs',
			'flowFilename' => 'prettify.js',
			'flowRelativePath' => 'home/prettify.js',
			'flowTotalChunks' => 42
		]);

		$this->filesArr = [
			'file' => [
				'name' => 'someFile.gif',
				'type' => 'image/gif',
				'size' => '10',
				'tmp_name' => '/tmp/abc1234',
				'error' => UPLOAD_ERR_OK
			]
		];
	}

	protected function tearDown()
	{
		$_REQUEST = [];
		$_FILES = [];
	}
}
