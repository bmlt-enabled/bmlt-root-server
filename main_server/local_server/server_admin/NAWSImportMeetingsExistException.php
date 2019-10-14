<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class NAWSImportMeetingsExistException extends Exception
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    private $worldIds = null;

    public function __construct($worldIds)
    {
        $this->worldIds = $worldIds;
        parent::__construct();
    }

    public function getWorldIds()
    {
        return $this->worldIds;
    }
}
