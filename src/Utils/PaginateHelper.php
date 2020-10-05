<?php
declare(strict_types=1);

namespace App\Utils;


use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class PaginateHelper
{
    private int $number;
    private int $size;

    public function __construct(int $number, int $size)
    {
        $this->number = $number;
        $this->size = $size;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getFirstResult(): int
    {
        return ($this->number - 1) * $this->size;
    }

    public static function createFormRequest(Request $request): self
    {
        try {
            $param = $request->query->all('page');
            $number = (isset($param['number']) && intval($param['number']) > 0) ? intval($param['number']) : 1;
            $size = (isset($param['size']) && intval($param['size']) > 0) ? intval($param['size']) : 10;
        } catch (BadRequestException $exception) {
            $number = 1;
            $size = 10;
        }

        return new self($number, $size);
    }


}