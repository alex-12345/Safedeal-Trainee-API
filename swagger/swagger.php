<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Safedeal trainee API",
 *     description="Документация API прототипа сервиса курьерской доставки",
 *     version="1.0"
 * )
 * @OA\Server(
 *     url="http://safedeal.localhost"
 * )
 * @OA\Tag(
 *     name="orders",
 *     description="Методы связанные с работой над заказами"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="basic",
 *     securityScheme="basicAuth",
 *     description="Username-password pairs: seller-seller, courier-courier, consumer-consumer"
 * )
 *
 * @OA\Parameter(
 *     name="id",
 *     in="path",
 *     description="Id ресурса",
 *     required=true,
 *     @OA\Schema(type="integer", example="1")
 * )
 *
 */
