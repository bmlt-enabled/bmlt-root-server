<?php

namespace App\Repositories\External;

abstract class ExternalObject
{
    abstract protected function throwInvalidObjectException(): void;

    protected function validateInt(array $values, string $key): int
    {
        $value = $values[$key] ?? null;
        $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if (!is_null($value)) {
            return $value;
        }
        $this->throwInvalidObjectException();
    }

    protected function validateString(array $values, string $key): string
    {
        $value = $values[$key] ?? null;
        if (is_string($value)) {
            return trim($value);
        }
        $this->throwInvalidObjectException();
    }

    protected function validateNullableString(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;
        if (is_null($value)) {
            return null;
        }
        $value = $this->validateString($values, $key);
        if (empty($value)) {
            return null;
        }
        return $value;
    }

    protected function validateUrl(array $values, string $key): string
    {
        $value = $this->validateString($values, $key);
        $value = filter_var($value, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
        if (!is_null($value)) {
            return $value;
        }
        $this->throwInvalidObjectException();
    }
}
