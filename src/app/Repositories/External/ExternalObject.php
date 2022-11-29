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

    protected function validateNullableInt(array $values, string $key): ?int
    {
        $value = $values[$key] ?? null;
        if (is_null($value)) {
            return null;
        }
        if (is_string($value) && $value == '') {
            return null;
        }
        return $this->validateInt($values, $key);
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

    protected function validateTime(array $values, string $key): string
    {
        $value = $this->validateString($values, $key);
        $value = (\DateTime::createFromFormat('H:i:s', $value) ?: \DateTime::createFromFormat('H:i', $value) ?: null)?->format('H:i:s');
        if (!is_null($value)) {
            return $value;
        }
        $this->throwInvalidObjectException();
    }

    protected function validateNullableFloat(array $values, string $key): ?float
    {
        $value = $values[$key] ?? null;
        $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        return $value;
    }

    protected function validateBool(array $values, string $key): bool
    {
        $value = $values[$key] ?? null;
        if (!is_null($value)) {
            return boolval($value);
        }
        $this->throwInvalidObjectException();
    }

    protected function validateIntArray(array $values, string $key): array
    {
        $value = $this->validateString($values, $key);
        if (empty($value)) {
            return [];
        }
        return collect(explode(',', $value))
            ->map(fn ($v) => intval(trim($v)))
            ->sort()
            ->unique()
            ->values()
            ->toArray();
    }
}
