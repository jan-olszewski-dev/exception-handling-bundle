<?php
declare(strict_types=1);

namespace Jolszewski\ExceptionHandlingBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Jolszewski\ExceptionHandlingBundle\Exception\ExceptionRedirectingInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[AsEventListener]
class ExceptionListener
{
    public function __construct(private readonly LoggerInterface $logger, private readonly RouterInterface $router) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof HttpExceptionInterface) {
            $this->logHttpException($throwable);
        } elseif ($throwable instanceof ExceptionRedirectingInterface) {
            $this->handleContextException($event);
        }
    }

    private function logHttpException(HttpExceptionInterface $throwable): void
    {
        $this->logger->error(
            $throwable->getMessage(),
            $this->getThrowableContext($throwable) + [
                'status' => $throwable->getStatusCode()
            ]
        );
    }

    private function handleContextException(ExceptionEvent $event): void
    {
        /** @var ExceptionRedirectingInterface $throwable */
        $throwable = $event->getThrowable();
        $this->logger->error($throwable->getMessage(), $this->getThrowableContext($throwable));
        $redirect = $this->getRedirect($throwable);
        $event->setResponse($redirect);
    }

    private function getRedirect(ExceptionRedirectingInterface $exceptionRedirecting): RedirectResponse
    {
        try {
            $route = $this->router->generate($exceptionRedirecting->getRoute(), $exceptionRedirecting->getParameters());
        } catch (RouteNotFoundException) {
            $routeInfo = $this->router->match($exceptionRedirecting->getRoute());
            $route = $this->router->generate(array_shift($routeInfo), $routeInfo);
        }

        return new RedirectResponse($route);
    }

    private function getThrowableContext(\Throwable $throwable): array
    {
        return [
            'trace' => $throwable->getTrace(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ];
    }
}
