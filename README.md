# Exception handling bundle

## Description

Bundle that provide automatically exception listening for symfony projects to logging them

## How it works

### Logging

Using of that bundle is simple. Just throw an exception that implement one of bellow interfaces

```php
use Jolszewski\ExceptionHandlingBundle\Exception\ExceptionRedirectingInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
```

Then listener will catch it and log with error level.

### Redirecting

All exceptions which implements `ExceptionRedirectingInterface` allow also redirect response after exception occurs

```php
use Jolszewski\ExceptionHandlingBundle\Exception\ExceptionRedirecting;

// ...
// Redirect using route name and parameters
throw new ExceptionRedirecting("message", "route_name", ["parameter1" => "value"])

// ...
// Redirect using route full path

throw new ExceptionRedirecting("message", "/route/endpoint")
```

