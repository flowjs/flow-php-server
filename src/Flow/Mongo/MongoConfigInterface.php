<?php

namespace Flow\Mongo;

use Flow\ConfigInterface;
use MongoDB\GridFS\Bucket;

/**
 * @codeCoverageIgnore
 */
interface MongoConfigInterface extends ConfigInterface
{
    public function getGridFs(): Bucket;
}
