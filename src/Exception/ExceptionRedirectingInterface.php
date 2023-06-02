<?php

namespace Jolszewski\ExceptionHandlingBundle\Exception;

interface ExceptionRedirectingInterface extends \Throwable
{
    public function getRoute(): ?string;

    public function getParameters(): array;
}
