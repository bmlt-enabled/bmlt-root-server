<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class NAWSImportServiceBodiesExistException extends Exception
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    private $worldIds = null;

    public function __construct($worldIds)
    {
        $this->worldIds = $worldIds;

        parent::__construct('Service bodies with the following World IDs already exist: ' . implode(', ', $this->getWorldIds()));
    }

    public function getWorldIds()
    {
        return $this->worldIds;
    }
}
