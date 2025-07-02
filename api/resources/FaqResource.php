<?php

namespace api\resources;

use common\models\Faq;


class FaqResource extends Faq
{
    public function fields()
    {
        return [
            'id',
            'question',
            'answer',
        ];
    }
}
