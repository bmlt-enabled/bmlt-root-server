<?php

namespace App\Repositories\External;

use App\Models\Format;

class ExternalFormat extends ExternalObject
{
    public int $id;
    public string $key;
    public string $name;
    public string $description;
    public string $language;
    public ?string $type;
    public ?string $worldId;

    public function __construct(array $values)
    {
        $this->id = $this->validateInt($values, 'id');
        $this->key = $this->validateString($values, 'key_string');
        $this->name = $this->validateString($values, 'name_string');
        $this->description = $this->validateString($values, 'description_string');
        $this->language = $this->validateString($values, 'lang');
        $this->type = $this->validateNullableString($values, 'format_type_enum');
        $this->worldId = $this->validateNullableString($values, 'world_id');
    }

    public function isEqual(Format $serviceBody): bool
    {
        if ($this->id != $serviceBody->source_id) {
            return false;
        }
        if ($this->key != $serviceBody->key_string) {
            return false;
        }
        if ($this->name != $serviceBody->name_string) {
            return false;
        }
        if ($this->description != $serviceBody->description_string) {
            return false;
        }
        if ($this->language != $serviceBody->lang_enum) {
            return false;
        }
        if ($this->type != $serviceBody->format_type_enum) {
            return false;
        }
        if ($this->worldId != $serviceBody->worldid_mixed) {
            return false;
        }
        return true;
    }

    protected function throwInvalidObjectException(): void
    {
        throw new InvalidFormatException();
    }
}
