@php
    $statusCode = $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
        ? $exception->getStatusCode()
        : 404;
@endphp

@include('errors.layout', ['statusCode' => $statusCode])
