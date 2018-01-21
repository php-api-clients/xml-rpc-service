<?php declare(strict_types=1);

namespace ApiClients\Tools\Services\XmlRpc;

use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Xml\XmlStream;
use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;
use RingCentral\Psr7\Request;
use function React\Promise\reject;
use function React\Promise\resolve;

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

    public function callRaw(string $method, array $arguments = []): PromiseInterface
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
        ));
    }

    public function call(string $method, array $arguments = []): PromiseInterface
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

        return $this->callRaw($method, $params)->then(function (ResponseInterface $response) {
            $xml = $response->getBody()->getParsedContents();
            $xml = $xml['methodResponse'];

            if (isset($xml['fault'])) {
                return $this->handleFault($xml);
            }

            return $this->handleSuccess($xml);
        });
    }

    private function handleFault(array $xml): PromiseInterface
    {
        $fault = XmlRpcPayloadParser::parse($xml['fault']['value']);

        return reject(
            new XmlRpcError(
                $fault['faultString'],
                $fault['faultCode']
            )
        );
    }

    private function handleSuccess(array $xml): PromiseInterface
    {
        return resolve(
            XmlRpcPayloadParser::parse(
                $xml['params']['param']['value']
            )
        );
    }
}
