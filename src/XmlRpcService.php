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

        return $this->requestService->request(new Request(
            'POST',
            '',
            [],
            new XmlStream([
                'methodCall' => [
                    'methodName' => $method,
                ],
            ])
        ))->then(function (ResponseInterface $response) {
            $status = $response->getBody()->getParsedContents();
            $status = $status['methodResponse']['params']['param']['value']['boolean'];

            return resolve($status);
        });
    }
}
