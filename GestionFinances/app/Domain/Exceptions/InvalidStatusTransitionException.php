<?php

namespace App\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidStatusTransitionException extends HttpException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct(422, "Cannot transition from '{$from}' to '{$to}'.");
    }
}
