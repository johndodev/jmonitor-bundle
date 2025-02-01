<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Jmonitor;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Johndodev\JmonitorBundle\Exceptions\JmonitorException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
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

    public function post(string $path, $body = null, array $query = [], ?string $contentType = null)
    {
        $this->headers['Content-type'] = $contentType ?? 'application/json';

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

        try {
            return $this->parseResponse($this->httpClient->sendRequest($request));
        } catch (NetworkExceptionInterface $e) {
            throw $e;
            // todo custom exception ?
            // throw new CommunicationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function parseResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() === 204 || $response->getStatusCode() === 202) {
            return null;
        }

        if (!$this->isJSONResponse($response->getHeader('content-type'))) {
            throw new JmonitorException('InvalidResponseBodyException');
            // throw new InvalidResponseBodyException($response, (string) $response->getBody());
        }

        if ($response->getStatusCode() >= 300) {
            $body = $this->json->unserialize((string) $response->getBody()) ?? $response->getReasonPhrase();
            throw new JmonitorException('ApiException');
            // throw new ApiException($response, $body);
        }

        return $this->json->unserialize((string) $response->getBody());
    }

    private function buildQueryString(array $queryParams = []): string
    {
        return \count($queryParams) > 0 ? '?'.http_build_query($queryParams) : '';
    }

    private function isJSONResponse(array $headerValues): bool
    {
        $filteredHeaders = array_filter($headerValues, static function (string $headerValue) {
            return false !== strpos($headerValue, 'application/json');
        });

        return \count($filteredHeaders) > 0;
    }
}
