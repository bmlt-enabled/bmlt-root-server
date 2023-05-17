<?php

namespace App\Repositories;

use App\Interfaces\FormatTypeRepositoryInterface;
use App\Models\FormatType;
use Illuminate\Support\Collection;

class FormatTypeRepository implements FormatTypeRepositoryInterface
{
    private Collection $_formatTypesByKey;
    private array $_descriptions;
    public function __construct() 
    {
        $formatTypes = FormatType::query()->get();
        $this->_formatTypesByKey = $formatTypes->keyBy('key_string');
        $this->_descriptions = $formatTypes->keyBy('description_string')->keys()->all();
    }
    public function getKeyFromDescription($description)
    {
        $ret = $this->_formatTypesByKey->firstWhere('description_string', $description);
        if (is_null($ret)) {
            return null;
        }
        return $ret->key_string;
    }
    public function getDescriptionFromKey($key)
    {
        $ret = $this->_formatTypesByKey->firstWhere('key_string', $key);
        if (is_null($ret)) {
            return null;
        }
        return $ret->description_string;
    }
    public function getDescriptions():array
    {
        return $this->_descriptions;
    }
}
