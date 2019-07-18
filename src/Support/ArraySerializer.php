<?php

namespace Godruoyi\DingoApiHelper\Support;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

class ArraySerializer extends BaseArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return $resourceKey ? [$resourceKey => $data] : $data;
    }
}
