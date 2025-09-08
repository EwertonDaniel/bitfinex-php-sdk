<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs;

use EwertonDaniel\Bitfinex\Entities\ConfigEntry;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers\DefaultTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers\ListTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers\MapTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers\PairInfoTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers\TxStatusTransformer;

class ConfigsTransformer
{
    public function __construct(
        private readonly array $strategies = [
            new MapTransformer(),
            new ListTransformer(),
            new PairInfoTransformer(),
            new TxStatusTransformer(),
            new DefaultTransformer(),
        ]
    ) {}

    /**
     * @param array<int,string> $keys
     * @param mixed $content
     * @return array{configs: array<int, ConfigEntry>}
     */
    public function transform(array $keys, mixed $content): array
    {
        $entries = [];
        foreach ($keys as $i => $key) {
            if (!is_array($content) || !array_key_exists($i, $content) || $content[$i] === null) {
                continue;
            }
            $value = $content[$i];
            foreach ($this->strategies as $t) {
                if ($t->supports($key, $value)) {
                    $value = $t->transform($key, $value);
                    break;
                }
            }
            $entries[] = new ConfigEntry($key, $value);
        }
        return ['configs' => $entries];
    }
}
