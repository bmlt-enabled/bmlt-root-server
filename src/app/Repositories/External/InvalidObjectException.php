<?php

namespace App\Repositories\External;

class InvalidObjectException extends \Exception
{
    public function __construct(string $objectType)
    {
        parent::__construct("Invalid object for $objectType");
    }
}
