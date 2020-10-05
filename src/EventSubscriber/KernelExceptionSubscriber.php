<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use OpenApi\Annotations as OA;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @OA\Response(
 *     response="Error400",
 *     description="В запросе переданы некорректные данные",
 *     @OA\JsonContent(
 *          @OA\Property(property="error", type="integer", example="400"),
 *          @OA\Property(property="message", type="string")
 *     )
 *  )
 * @OA\Response(
 *     response="Error401",
 *     description="Аутентификация не пройдена",
 *     @OA\JsonContent(
 *          @OA\Property(property="error", type="integer", example="401"),
 *          @OA\Property(property="message", type="string")
 *     )
 *  )
 * @OA\Response(
 *     response="Error403",
 *     description="Доступ запрещен политикой безопасности",
 *     @OA\JsonContent(
 *          @OA\Property(property="error", type="integer", example="403"),
 *          @OA\Property(property="message", type="string")
 *     )
 *  )
 * @OA\Response(
 *     response="Error404",
 *     description="Не найден какой либо запрашиваемый объект",
 *     @OA\JsonContent(
 *          @OA\Property(property="error", type="integer", example="404"),
 *          @OA\Property(property="message", type="string")
 *     )
 *  )
 */
class KernelExceptionSubscriber implements EventSubscriberInterface
{
    public function onExceptionHandle(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof HttpException) {
            $event->setResponse(
                new JsonResponse(
                    [
                        "code" => $exception->getStatusCode(),
                        "message" => $exception->getMessage()
                    ],
                    $exception->getStatusCode(),
                    $exception->getHeaders()
                )
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onExceptionHandle']
        ];
    }
}