<?php declare(strict_types=1);

namespace ApiClients\Tools\Services\XmlRpc;

use DateTimeImmutable;

final class XmlRpcPayloadParser
{
    public static function parse(array $xml)
    {
        if (isset($xml['string'])) {
            return $xml['string'];
        }

        if (isset($xml['base64'])) {
            return base64_decode($xml['base64'], true);
        }

        if (isset($xml['i4'])) {
            return (int)$xml['i4'];
        }

        if (isset($xml['int'])) {
            return (int)$xml['int'];
        }

        if (isset($xml['double'])) {
            return (float)$xml['double'];
        }

        if (isset($xml['boolean'])) {
            return (bool)$xml['boolean'];
        }

        if (isset($xml['dateTime.iso8601'])) {
            return new DateTimeImmutable($xml['dateTime.iso8601']);
        }

        return $xml;
    }
}
