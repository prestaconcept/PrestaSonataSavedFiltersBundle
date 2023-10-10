<?php

declare(strict_types=1);

namespace Presta\SonataSavedFiltersBundle\Exception;

final class UnexpectedTypeException extends \InvalidArgumentException
{
    public function __construct(mixed $value, string $expectedType)
    {
        $givenType = \is_object($value) ? \get_class($value) : \gettype($value);

        parent::__construct("Expected argument of type \"$expectedType\", \"$givenType\" given.");
    }
}
