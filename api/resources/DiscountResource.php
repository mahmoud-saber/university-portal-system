<?php
namespace api\resources;

use Yii;

class DiscountResource extends \common\models\SubscriptionDiscounts
{
    public function fields()
    {
        return [
            'promo_id',
            'subscription_id',
            'promo_name' => function () {
                $promo = $this->promos;
                return $promo ? $promo->name : 'No Promo Found';
            },
            'discount_amount' => 'amount',
            'discount_type',
            'percentage',
            'allow_stack',
        ];
    }
}
