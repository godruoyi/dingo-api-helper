<?php

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
