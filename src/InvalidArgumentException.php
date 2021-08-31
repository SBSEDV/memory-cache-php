<?php declare(strict_types=1);

namespace SBSEDV\Component\Cache\InMemory;

use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentException
{
}
