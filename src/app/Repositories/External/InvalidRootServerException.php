<?php

namespace App\Repositories\External;

class InvalidRootServerException extends InvalidObjectException
{
    public function __construct()
    {
        parent::__construct('RootServer');
    }
}
