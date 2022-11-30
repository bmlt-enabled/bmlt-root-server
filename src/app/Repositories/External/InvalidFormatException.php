<?php

namespace App\Repositories\External;

class InvalidFormatException extends InvalidObjectException
{
    public function __construct()
    {
        parent::__construct('Format');
    }
}
