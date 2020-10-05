<?php
declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\RateLimiter;
use PHPUnit\Framework\TestCase;
use SplQueue;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Contracts\Cache\ItemInterface;

class RateLimiterTest extends TestCase
{
    /**
     * @dataProvider doCycleProvider
     * @param int $currentTime
     * @param array $queue
     * @param int $rpm
     * @param bool $isHitFlag
     * @param bool $throwExceptionFlag
     * @param bool $resultFlag
     */
    public function testDoCycle(int $currentTime, array $queue, int $rpm, bool $isHitFlag, bool $throwExceptionFlag, bool $resultFlag = true)
    {

        $userRLQu = new SplQueue();

        while (!empty($queue)) $userRLQu->enqueue(array_shift($queue));

        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock->expects($this->any())->method('isHit')->willReturn($isHitFlag);

        if ($isHitFlag) {
            $itemMock->expects($this->any())->method('get')->willReturn(serialize($userRLQu));
        }
        if (!$throwExceptionFlag) {
            $itemMock->expects($this->any())->method('set')->willReturn($itemMock);
            $itemMock->expects($this->any())->method('expiresAfter')->willReturn($itemMock);
        }

        $cache = $this->createMock(AbstractAdapter::class);
        $cache->expects($this->any())->method('getItem')->willReturn($itemMock);
        $cache->expects($this->any())->method('save')->willReturn(true);

        if ($throwExceptionFlag) {
            $this->expectException(TooManyRequestsHttpException::class);
        }
        $rateLimitService = new RateLimiter($cache, $rpm);
        $testResult = $rateLimitService->doCycle('test', $currentTime);

        if (!$throwExceptionFlag) {
            $this->assertEquals($resultFlag, $testResult);
        }
    }


    public function doCycleProvider()
    {

        $rpm = 10;
        $currentTime = time();

        $arr1 = array_fill(0, $rpm, $currentTime);
        $arr2 = array_merge($arr1, []);
        array_splice($arr2, $rpm - 1);
        $arr3 = array_merge($arr1, []);
        $arr3[0] = $currentTime - 61;
        $arr4 = array_merge($arr1, []);
        $arr4[0] = $currentTime - 50;

        $smallArr = array_fill(0, 4, $currentTime);

        return [
            [$currentTime, $arr1, $rpm, true, true],
            [$currentTime, $arr2, $rpm, true, false, true],
            [$currentTime, $arr3, $rpm, true, false, true],
            [$currentTime, $arr4, $rpm, true, true],
            [$currentTime, $smallArr, $rpm, true, false, true],
            [$currentTime, $smallArr, 4, true, true],
            [$currentTime, $smallArr, -4, false, false, false]
        ];
    }
}