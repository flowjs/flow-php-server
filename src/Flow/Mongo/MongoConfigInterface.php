<?php

namespace Flow\Mongo;

use Flow\ConfigInterface;

/**
 * @package Flow
 */
interface MongoConfigInterface extends ConfigInterface
{

    /**
     * @return \MongoGridFS
     */
    public function getGridFs();

}