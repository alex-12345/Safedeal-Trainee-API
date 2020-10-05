<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\CreateOrderData;
use App\Entity\Order;
use App\Service\OrderService;
use App\Utils\PaginateHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class OrdersController extends AbstractController
{
    /**
     * @Route("/orders/delivery/price", methods={"POST"})
     *
     * @OA\Post(
     *     path="/orders/delivery/price",
     *     tags={"orders"},
     *     security={{"basicAuth":{}}},
     *     description="Расчет цены доставки",
     *     @OA\RequestBody(ref="#/components/requestBodies/Order"),
     *     @OA\Response(
     *          response=200,
     *          description="Цена доставки успешно рассчитана",
     *          @OA\JsonContent(@OA\Property(property="delivery_price", type="integer", example=7500))
     *     ),
     *     @OA\Response(response=400, ref="#/components/responses/Error400"),
     *     @OA\Response(response=401, ref="#/components/responses/Error401"),
     *     @OA\Response(response=403, ref="#/components/responses/Error403"),
     *     @OA\Response(response=404, ref="#/components/responses/Error404")
     * )
     *
     * @param CreateOrderData $dto
     * @param OrderService $orderService
     * @return JsonResponse
     */
    public function createDeliveryPrice(CreateOrderData $dto, OrderService $orderService)
    {
        $this->denyAccessUnlessGranted('ROLE_CONSUMER');
        $orderSummary = $orderService->createOrderSummary($dto);
        return new JsonResponse(['delivery_price' => $orderSummary->getDeliveryPrice()]);
    }

    /**
     * @Route("/orders", methods={"POST"})
     *
     * @OA\Post(
     *     path="/orders",
     *     tags={"orders"},
     *     security={{"basicAuth":{}}},
     *     description="Создание нового заказа",
     *     @OA\RequestBody(ref="#/components/requestBodies/Order"),
     *     @OA\Response(
     *          response=200,
     *          description="Заказ успешно создан",
     *          @OA\JsonContent(ref="#/components/schemas/OrderFullItem")
     *     ),
     *     @OA\Response(response=400, ref="#/components/responses/Error400"),
     *     @OA\Response(response=401, ref="#/components/responses/Error401"),
     *     @OA\Response(response=403, ref="#/components/responses/Error403"),
     *     @OA\Response(response=404, ref="#/components/responses/Error404")
     * )
     *
     * @param CreateOrderData $dto
     * @param OrderService $orderService
     * @param EntityManagerInterface $entityManager
     * @param UserInterface $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function create(
        CreateOrderData $dto,
        OrderService $orderService,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONSUMER');
        $orderSummary = $orderService->createOrderSummary($dto);
        $order = $orderService->createOrder($dto, $orderSummary, $entityManager, $user);
        return new JsonResponse($serializer->normalize($order, null, ['groups' => 'full']));
    }

    /**
     * @Route("/orders/{id<\d+>}", methods={"GET"})
     *
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"orders"},
     *     security={{"basicAuth":{}}},
     *     description="Получение полной информации о заказе",
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Response(
     *          response=200,
     *          description="Данные по заказу успешно получены",
     *          @OA\JsonContent(ref="#/components/schemas/OrderFullItem")
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Error401"),
     *     @OA\Response(response=403, ref="#/components/responses/Error403"),
     *     @OA\Response(response=404, ref="#/components/responses/Error404")
     * )
     *
     * @param int $id
     * @param SerializerInterface $serializer
     * @param UserInterface $user
     * @return JsonResponse
     */
    public function read(int $id, SerializerInterface $serializer, UserInterface $user): JsonResponse
    {
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        if (is_null($order))
            throw new NotFoundHttpException("Order not found!");

        if ($user->getUsername() !== $order->getConsumer()) {
            $this->denyAccessUnlessGranted('ROLE_COURIER');
        }
        return new JsonResponse($serializer->normalize($order, null, ['groups' => 'full']));
    }

    /**
     * @Route("/orders", methods={"GET"})
     *
     * @OA\Get(
     *     path="/orders",
     *     tags={"orders"},
     *     description="Получение списка заказов",
     *     security={{"basicAuth":{}}},
     *     @OA\Parameter(name="page[size]", in="query", @OA\Schema(type="integer"), description="Размер страницы"),
     *     @OA\Parameter(name="page[number]", in="query", @OA\Schema(type="integer"), description="Номер страницы"),
     *     @OA\Response(
     *          response=200,
     *          description="Список заказов успешно получены",
     *          @OA\JsonContent(@OA\Items(ref="#/components/schemas/OrderListItem"))
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Error401"),
     *     @OA\Response(response=403, ref="#/components/responses/Error403"),
     *     @OA\Response(response=404, ref="#/components/responses/Error404"),
     * )
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SELLER');

        $paginateHelper = PaginateHelper::createFormRequest($request);

        $orderRepository = $this->getDoctrine()->getManager()->getRepository(Order::class);
        $paginator = $orderRepository->getListPaginator($paginateHelper);

        $results = [];
        foreach ($paginator as $item) {
            $results[] = $serializer->normalize($item, null, ['groups' => 'list']);
        }

        if (empty($results))
            throw new NotFoundHttpException("Orders not found!");

        return new JsonResponse($results);
    }

}