<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof UnprocessableEntityHttpException && $e->getPrevious() instanceof ValidationFailedException) {
            $violations = $e->getPrevious()->getViolations();
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $event->setResponse(new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422));

            return;
        }

        if ($e instanceof NotFoundHttpException && str_contains($e->getMessage(), 'App\\Entity\\User')) {
            $event->setResponse(new JsonResponse([
                'message' => 'User not found',
            ], 404));

            return;
        }

        if ($e instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse([
                'message' => $e->getMessage() ?: 'Request error',
            ], $e->getStatusCode()));

            return;
        }

        $event->setResponse(new JsonResponse([
            'message' => 'Internal server error',
        ], 500));
    }
}
