<?php
declare(strict_types=1);

namespace App\APIHelper;


use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

abstract class AbstractAPIHelper
{
    protected string $apiUrl;
    protected HttpClientInterface $client;

    public function __construct(string $apiUrl, HttpClientInterface $client)
    {
        $this->apiUrl = $apiUrl;
        $this->client = $client;
    }

    abstract protected function getAPIServiceName(): string;

    protected function sendRequest(string $method, string $path, array $option = []): array
    {
        try {
            $response = $this->client->request(
                $method,
                $this->apiUrl . $path,
                $option
            );
            $code = $response->getStatusCode();
            $content = $response->toArray(false);
        } catch (Throwable $exception) {
            throw new HttpException(500, static::getAPIServiceName() . ' unavailable!');
        }
        switch ($code) {
            case 200:
                return $content;
            case 404:
                $message = (isset($content['message'])) ? static::getAPIServiceName() . ': ' . strval($content['message']) : null;
                if (isset($message))
                    throw new NotFoundHttpException($message);
        }
        throw new HttpException(500, static::getAPIServiceName() . ' unavailable!');

    }

    /**
     * @param array $parameters
     * @param array $serviceParameters
     */
    protected function checkParameters(array $parameters, array $serviceParameters): void
    {
        $parameters = array_filter($parameters, function ($parameter) use ($serviceParameters) {
            return in_array($parameter, $serviceParameters);
        });
        if (empty($parameters))
            throw new InvalidArgumentException('At least one valid parameter must be passed!');
    }

}