<?php

namespace App\Repositories\External;

use App\Models\RootServer;

class ExternalRootServer extends ExternalObject
{
    public int $id;
    public string $name;
    public string $url;

    public function __construct(array $values)
    {
        $this->id = $this->validateInt($values, 'id');
        $this->name = $this->validateString($values, 'name');
        $this->url = $this->validateUrl($values, 'rootURL');
    }

    public function compare(RootServer $rootServer): bool
    {
        if ($this->id != $rootServer->source_id) {
            return false;
        }
        if ($this->name != $rootServer->name) {
            return false;
        }
        if ($this->url != $rootServer->url) {
            return false;
        }
        return true;
    }

    protected function throwInvalidObjectException(): void
    {
        throw new InvalidRootServerException();
    }
}
