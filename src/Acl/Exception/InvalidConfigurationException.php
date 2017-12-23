<?php

namespace Sztyup\Acl\Exception;

use Exception;

class InvalidConfigurationException extends Exception
{
    public function __construct($key)
    {
        parent::__construct("The given configuration is invalid, faulty key: " . $key);
    }

}