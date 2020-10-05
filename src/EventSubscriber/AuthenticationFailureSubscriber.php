<?php
declare(strict_types=1);

namespace App\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\AuthenticationEvents;

class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
    public function onAuthenticationFailure()
    {
        throw new UnauthorizedHttpException(
            'Basic realm="Secured Area"',
            'The username or password provided is incorrect!'
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure'
        ];

    }
}