<?php

namespace App\Repositories\External;

class InvalidMeetingException extends InvalidObjectException
{
    public function __construct()
    {
        parent::__construct('Meeting');
    }
}
