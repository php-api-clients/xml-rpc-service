<?php declare(strict_types=1);

namespace ApiClients\Tests\Tools\Services\XmlRpc;

use ApiClients\Tools\Services\XmlRpc\XmlRpcPayloadParser;
use ApiClients\Tools\TestUtilities\TestCase;
use DateTimeImmutable;

final class XmlRpcPayloadParserTest extends TestCase
{
    public function provideInputAndOutput()
    {
        yield [
            [
                'string' => 'string',
            ],
            'string',
        ];

        yield [
            [
                'i4' => '123',
            ],
            123,
        ];

        yield [
            [
                'int' => '123',
            ],
            123,
        ];

        yield [
            [
                'double' => '1.23',
            ],
            1.23,
        ];

        yield [
            [
                'boolean' => '0',
            ],
            false,
        ];

        yield [
            [
                'boolean' => '1',
            ],
            true,
        ];

        yield [
            [
                'dateTime.iso8601' => '19980717T14:08:55',
            ],
            new DateTimeImmutable('19980717T14:08:55'),
        ];

        yield [
            [
                'base64' => 'eW91IGNhbid0IHJlYWQgdGhpcyE=',
            ],
            'you can\'t read this!',
        ];
    }

    /**
     * @dataProvider provideInputAndOutput
     * @param mixed $expectedOutput
     */
    public function testParse(array $input, $expectedOutput)
    {
        $output = XmlRpcPayloadParser::parse($input);
        self::assertEquals($expectedOutput, $output);
    }
}
