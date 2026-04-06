<?php

namespace App\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Resource not found')
    {
        // parent::__construct(404, $message);
        parent::__construct($message, null, 404);

    }
}
