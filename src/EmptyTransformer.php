<?php

/*
 * This file is part of the godruoyi/dingo-api-helper.
 *
 * (c) Godruoyi <godruoyi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\DingoApiHelper;

use League\Fractal\TransformerAbstract;

class EmptyTransformer extends TransformerAbstract
{
    /**
     * Transform a empty model.
     *
     * @param  $model
     *
     * @return array
     */
    public function transform($model)
    {
        return [
        ];
    }
}
