<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Jmonitor;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Johndodev\JmonitorBundle\Exceptions\ResponseException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private string $baseUrl;
    private Json $json;
    private array $headers;

    public function __construct(string $baseUrl, string $jmonitorVersion, string $projectApiKey, ?ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $reqFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->json = new Json();
        $this->headers = [
            'X-JMONITOR-VERSION' => $jmonitorVersion,
            'X-JMONITOR-API-KEY' => $projectApiKey,
        ];

        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function sendMetrics(array $metrics)
    {
        return $this->post('/metrics', $metrics);
    }

    public function post(string $path, $body = null, array $query = [])
    {
        $this->headers['Content-type'] = 'application/json';

        $body = $this->json->serialize($body);

        $request = $this->requestFactory
            ->createRequest('POST', $this->baseUrl.$path.$this->buildQueryString($query))
            ->withBody($this->streamFactory->createStream($body));

        return $this->execute($request);
    }

    private function execute(RequestInterface $request)
    {
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        $response = $this->httpClient->sendRequest($request);

        return $this->parseResponse($response);
    }

    private function parseResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() === 204 || $response->getStatusCode() === 202) {
            return null;
        }

        if (!$this->isJSONResponse($response->getHeader('content-type'))) {
            throw new ResponseException('Unexpected response content-type : '.implode(', ', $response->getHeader('content-type')), (string) $response->getBody());
        }

        return $this->json->unserialize((string) $response->getBody());
    }

    private function buildQueryString(array $queryParams = []): string
    {
        return \count($queryParams) > 0 ? '?'.http_build_query($queryParams) : '';
    }

    private function isJSONResponse(array $headerValues): bool
    {
        foreach ($headerValues as $headerValue) {
            if (str_contains($headerValue, 'application/json')) {
                return true;
            }
        }

        return false;
    }
}
