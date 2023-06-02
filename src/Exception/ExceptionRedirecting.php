<?php

namespace Jolszewski\ExceptionHandlingBundle\Exception;

final class ExceptionRedirecting extends \RuntimeException implements ExceptionRedirectingInterface
{
    public function __construct(
        string $message,
        private readonly ?string $route = null,
        private readonly array $parameters = []
    ) {
        parent::__construct($message);
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
