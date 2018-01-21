<?php declare(strict_types=1);

namespace ApiClients\Tools\Services\XmlRpc;

use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Xml\XmlStream;
use Psr\Http\Message\ResponseInterface;
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

        return $this->callRaw($method, $params)->then(function (array $xml) {
            return XmlRpcPayloadParser::parse($xml);
        });
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
                $fault = XmlRpcPayloadParser::parse($xml['methodResponse']['fault']['value']);

                return reject(
                    new XmlRpcError(
                        $fault['faultString'],
                        $fault['faultCode']
                    )
                );
            }

            return resolve($xml['methodResponse']['params']['param']['value']);
        });
    }
}
