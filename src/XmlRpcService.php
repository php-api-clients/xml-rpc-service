<?php

namespace ApiClients\Tools\Services\XmlRpc;

use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Xml\XmlStream;
use Psr\Http\Message\ResponseInterface;
use function React\Promise\reject;
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
        $params = [];
        foreach ($arguments as $argument) {
            $params[] = [
                'param' => [
                    'value' => [
                        gettype($argument) => $argument,
                    ],
                ],
            ];
        }

        return $this->callRaw($method, $params);
    }

    public function callRaw(string $method, array $arguments = [])
    {
        $xml = [
            'methodCall' => [
                'methodName' => $method,
            ],
        ];

        if (count($arguments) > 0) {
            $xml['methodCall']['params'] = $arguments;
        }

        return $this->requestService->request(new Request(
            'POST',
            '',
            [],
            new XmlStream($xml)
        ))->then(function (ResponseInterface $response) {
            $xml = $response->getBody()->getParsedContents();

            if (isset($xml['methodResponse']['fault'])) {
                return reject(
                    new XmlRpcError(
                        $xml['methodResponse']['fault']['value']['struct']['member'][1]['value']['string'],
                        (int)$xml['methodResponse']['fault']['value']['struct']['member'][0]['value']['int']
                    )
                );
            }

            return resolve($xml['methodResponse']['params']['param']);
        });
    }
}
