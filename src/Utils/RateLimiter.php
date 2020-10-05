<?php
declare(strict_types=1);

namespace App\Utils;

use SplQueue;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Contracts\Cache\CacheInterface;

class RateLimiter
{
    const CACHE_USER_QUEUE_PREFIX = 'user_queue';
    const CACHE_USER_QUEUE_TTL = 60;

    private ?CacheInterface $cache;
    private ?int $rpm;
    private string $appEnv;

    public function __construct(CacheInterface $cache, ?int $rpm = 0, string $appEnv = 'dev')
    {
        $this->cache = $cache;
        $this->rpm = $rpm;
        $this->appEnv = $appEnv;
    }

    /**
     * @param string $username
     * @param int|null $currentTime
     * @return bool
     * @throws TooManyRequestsHttpException
     */
    public function doCycle(string $username, ?int $currentTime = null): bool
    {
        if ($this->rpm < 1 || $this->appEnv === 'test') return false;
        $userRLQu = $this->cache->getItem(self::CACHE_USER_QUEUE_PREFIX . '_' . $username);
        $uq = ($userRLQu->isHit()) ? unserialize($userRLQu->get()) : new SplQueue();

        if (is_null($currentTime)) $currentTime = time();

        if ($uq->count() >= $this->rpm) {
            $retryAfter = 60 - ($currentTime - $uq->bottom());
            if ($retryAfter > 0)
                throw new TooManyRequestsHttpException(
                    $retryAfter,
                    "The limit of requests for this user has been exceeded!"
                );
            $uq->dequeue();
        }
        $uq->enqueue($currentTime);
        $this->cache->save($userRLQu->set(serialize($uq))->expiresAfter(self::CACHE_USER_QUEUE_TTL));

        return true;
    }


}