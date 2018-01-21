<?php declare(strict_types=1);

namespace ApiClients\Tests\Tools\Services\XmlRpc;

use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Xml\XmlStream;
use ApiClients\Tools\Services\XmlRpc\XmlRpcError;
use ApiClients\Tools\Services\XmlRpc\XmlRpcService;
use ApiClients\Tools\TestUtilities\TestCase;
use RingCentral\Psr7\Request;
use RingCentral\Psr7\Response;
use function React\Promise\resolve;

final class XmlRpcServiceTest extends TestCase
{
    public function testCall()
    {
        $xml = [
            'methodCall' => [
                'methodName' => 'method',
            ],
        ];
        $request = new Request(
            'POST',
            '',
            [],
            new XmlStream($xml)
        );
        $response = new Response(
            200,
            [],
            new XmlStream([
                'methodResponse' => [
                    'params' => [
                        'param' => [
                            'value' => [
                                'string' => 'result',
                            ],
                        ],
                    ],
                ],
            ])
        );

        $requestService = $this->prophesize(RequestService::class);
        $requestService->request($request)->shouldBeCalled()->willReturn(resolve($response));

        $service = new XmlRpcService($requestService->reveal());
        $result = $this->await($service->call('method'));

        self::assertSame('result', $result);
    }

    public function testCallError()
    {
        self::expectException(XmlRpcError::class);
        self::expectExceptionCode(123);
        self::expectExceptionMessage('ERROR_MESSAGE');

        $xml = [
            'methodCall' => [
                'methodName' => 'method',
            ],
        ];
        $request = new Request(
            'POST',
            '',
            [],
            new XmlStream($xml)
        );
        $response = new Response(
            200,
            [],
            new XmlStream([
                'methodResponse' => [
                    'fault' => [
                        'value' => [
                            'struct' => [
                                'member' => [
                                    [
                                        'value' => [
                                            'int' => '123',
                                        ],
                                    ],
                                    [
                                        'value' => [
                                            'string' => 'ERROR_MESSAGE',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $requestService = $this->prophesize(RequestService::class);
        $requestService->request($request)->shouldBeCalled()->willReturn(resolve($response));

        $service = new XmlRpcService($requestService->reveal());
        $this->await($service->call('method'));
    }
}
