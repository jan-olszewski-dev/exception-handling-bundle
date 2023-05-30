<?php

namespace Jolszewski\ExceptionHandlingBundle\EventListener;

use Jolszewski\ExceptionHandlingBundle\Exception\ExceptionRedirecting;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener]
class ExceptionListener
{
    public function __construct(private readonly LoggerInterface $logger, private readonly RouterInterface $router)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        match ($throwable::class) {
            HttpExceptionInterface::class => $this->logHttpException($throwable),
            ExceptionRedirecting::class => $this->handleContextException($event),
        };
    }

    private function logHttpException(HttpExceptionInterface $throwable): void
    {
        $this->logger->debug($throwable->getMessage(), [
            'status' => $throwable->getStatusCode(),
            'trace' => $throwable->getTrace(),
        ]);
    }

    private function handleContextException(ExceptionEvent $event): void
    {
        /** @var ExceptionRedirecting $contextException */
        $contextException = $event->getThrowable();
        $this->logger->debug($contextException->getMessage());
        $route = $this->router->generate($contextException->getRoute(), $contextException->getParameters());
        $redirect = new RedirectResponse($route);
        $event->setResponse($redirect);
    }
}
