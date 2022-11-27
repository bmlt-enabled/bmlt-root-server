<?php

namespace App\Repositories\External;

use App\Models\ServiceBody;

class ExternalServiceBody extends ExternalObject
{
    public int $id;
    public int $parentId;
    public string $name;
    public string $description;
    public ?string $type;
    public ?string $url;
    public ?string $helpline;
    public ?string $worldId;

    public function __construct(array $values)
    {
        $this->id = $this->validateInt($values, 'id');
        $this->parentId = $this->validateInt($values, 'parent_id');
        $this->name = $this->validateString($values, 'name');
        $this->description = $this->validateString($values, 'description');
        $this->type = $this->validateNullableString($values, 'type');
        $this->url = $this->validateNullableString($values, 'url');
        $this->helpline = $this->validateNullableString($values, 'helpline');
        $this->worldId = $this->validateNullableString($values, 'world_id');
    }

    public function isEqual(ServiceBody $serviceBody): bool
    {
        if ($this->id != $serviceBody->source_id) {
            return false;
        }
        if ($this->name != $serviceBody->name_string) {
            return false;
        }
        if ($this->description != $serviceBody->description_string) {
            return false;
        }
        if ($this->type != $serviceBody->sb_type) {
            return false;
        }
        if ($this->url != $serviceBody->uri_string) {
            return false;
        }
        if ($this->helpline != $serviceBody->kml_file_uri_string) {
            return false;
        }
        if ($this->worldId != $serviceBody->worldid_mixed) {
            return false;
        }
        return true;
    }

    protected function throwInvalidObjectException(): void
    {
        throw new InvalidServiceBodyException();
    }
}
