<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Utils\RateLimiter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private RateLimiter $rateLimiter;

    public function __construct(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     * @return void
     * @throws TooManyRequestsHttpException
     */
    public function onRateLimit(AuthenticationSuccessEvent $event): void
    {
        $this->rateLimiter->doCycle($event->getAuthenticationToken()->getUsername());
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onRateLimit'
        ];

    }
}