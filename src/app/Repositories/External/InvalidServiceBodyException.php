<?php

namespace App\Repositories\External;

class InvalidServiceBodyException extends InvalidObjectException
{
    public function __construct()
    {
        parent::__construct('ServiceBody');
    }
}
