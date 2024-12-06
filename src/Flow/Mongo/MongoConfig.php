<?php

namespace Flow\Mongo;

use Flow\Config;
use MongoDB\GridFS\Bucket;

/**
 * @codeCoverageIgnore
 */
class MongoConfig extends Config implements MongoConfigInterface
{
    /**
     * @param Bucket $gridFS storage of the upload (and chunks)
     */
    function __construct(private Bucket $gridFS)
    {
        parent::__construct();
        $this->gridFs = $gridFS;
    }

    public function getGridFs() : Bucket
    {
        return $this->gridFs;
    }
}