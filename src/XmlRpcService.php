<?php

namespace ApiClients\Tools\Services\XmlRpc;

use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Xml\XmlStream;
use Psr\Http\Message\ResponseInterface;
use function React\Promise\resolve;
use RingCentral\Psr7\Request;

class XmlRpcService
{
    /**
     * @var RequestService
     */
    private $requestService;

    /**
     * @param RequestService $requestService
     */
    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function call(string $method, array $arguments = [])
    {
        $xml = [
            'methodCall' => [
                'methodName' => $method,
            ],
        ];

        if (count($arguments) > 0) {
            $xml['params'] = $arguments;
        }

        return $this->requestService->request(new Request(
            'POST',
            '',
            [],
            new XmlStream($xml)
        ))->then(function (ResponseInterface $response) {
            $xml = $response->getBody()->getParsedContents();

            return resolve($xml['methodResponse']['params']['param']);
        });
    }
}
