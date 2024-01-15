<?php

declare(strict_types=1);

namespace SimPod\ClickHouseClient\Sql;

use Safe\Exceptions\PcreException;
use SimPod\ClickHouseClient\Exception\UnsupportedValue;

use function assert;
use function is_string;
use function Safe\preg_replace;
use function sprintf;
use function str_replace;

/** @internal */
final class SqlFactory
{
    public function __construct(private ValueFormatter $valueFormatter)
    {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws PcreException
     * @throws UnsupportedValue
     */
    public function createWithParameters(string $query, array $parameters): string
    {
        /** @var mixed $value */
        foreach ($parameters as $name => $value) {
            $query = preg_replace(
                sprintf('~:%s(?!\w)~', $name),
                str_replace('\\', '\\\\', $this->valueFormatter->format($value, $name, $query)),
                $query,
            );
            assert(is_string($query));
        }

        $query = preg_replace('~ ?=([\s]*?)IS NULL~', '$1IS NULL', $query);
        assert(is_string($query));

        return $query;
    }
}
