<?php

namespace api\resources;

use common\models\Page;

class PageResource extends \common\models\Page
{
    public function fields()
    {
        return [
            'title' ,
            'image' => function ($model) {
                return $model->pageImage ;
            },
            'body',
        ];
    }

}
