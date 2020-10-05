<?php
declare(strict_types=1);

namespace App\Tests\Utils;


use App\Utils\PaginateHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PaginateHelperTest extends TestCase
{

    /**
     * @return array[]
     */
    public function createFromRequestProvider(): array
    {
        return [
            [null, 1, 10, 0],
            [['number' => 1, 'size' => 10], 1, 10, 0],
            [['number' => -3, 'size' => 6], 1, 6, 0],
            [['number' => 5, 'size' => -4], 5, 10, 40],
            [['number' => 5], 5, 10, 40],
            [['size' => 6], 1, 6, 0],
            [['number' => 3, 'size' => 6], 3, 6, 12],
        ];
    }

    /**
     * @dataProvider createFromRequestProvider
     * @param array|null $pageParam
     * @param int $expectedNumber
     * @param int $expectedSize
     * @param int $expectedFirstResult
     */
    public function testCreateFormRequest(
        ?array $pageParam,
        int $expectedNumber,
        int $expectedSize,
        int $expectedFirstResult
    ): void
    {
        $request = new Request();
        $request->query->set('page', $pageParam);
        $paginateHelper = PaginateHelper::createFormRequest($request);

        $this->assertEquals($expectedNumber, $paginateHelper->getNumber());
        $this->assertEquals($expectedSize, $paginateHelper->getSize());
        $this->assertEquals($expectedFirstResult, $paginateHelper->getFirstResult());

    }
}