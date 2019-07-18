<?php

/*
 * This file is part of the godruoyi/dingo-api-helper.
 *
 * (c) Godruoyi <godruoyi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\DingoApiHelper\Support;

use Exception;
use Godruoyi\DingoApiHelper\EmptyTransformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;

class Converter
{
    /**
     * Convert data to transformer instance.
     *
     * @param mixed $data
     * @param mixed $transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function convert($data, $transformer = null): TransformerAbstract
    {
        if (!is_null($transformer) && $transformer instanceof TransformerAbstract) {
            return $transformer;
        }

        if (($data instanceof LengthAwarePaginator || $data instanceof Collection) && $data->isEmpty()) {
            return new EmptyTransformer();
        }

        $classname = $this->getClassnameFrom($data);
        $classBasename = class_basename($classname);

        if (!class_exists($transformer = "App\\Transformers\\{$classBasename}Transformer")) {
            throw new Exception("No transformer for {$classname}");
        }

        return new $transformer();
    }

    /**
     * Get the class name from the given object.
     *
     * @param object $object
     *
     * @return string
     */
    protected function getClassnameFrom($object)
    {
        if ($object instanceof LengthAwarePaginator or $object instanceof Collection) {
            return get_class(array_first($object));
        }
        if (!is_string($object) && !is_object($object)) {
            throw new Exception("No transformer of \"{$object}\"found.");
        }

        return get_class($object);
    }
}
