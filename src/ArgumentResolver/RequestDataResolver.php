<?php
declare(strict_types=1);

namespace App\ArgumentResolver;


use App\DTO\Request\RequestDataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ReflectionClass;

class RequestDataResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $reflection = new ReflectionClass($argument->getType());
        return $reflection->implementsInterface(RequestDataInterface::class);

    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if ($request->getContentType() != 'json') throw new BadRequestHttpException('Bad content!');
        try {
            $dto = $this->serializer->deserialize($request->getContent(), $argument->getType(), 'json');
        } catch (UnexpectedValueException $exception) {
            throw new BadRequestHttpException((string)$exception->getMessage());
        }
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) throw new BadRequestHttpException((string)$errors);
        yield $dto;
    }
}