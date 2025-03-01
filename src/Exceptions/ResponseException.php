<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

class ResponseException extends JmonitorException implements ClientExceptionInterface
{
    private string $response;

    public function __construct(string $message, string $response, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->response = $response;
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
