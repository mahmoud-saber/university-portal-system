<?php
namespace api\helpers;

 use backend\models\TranslationsWithText;
 use yii\db\Expression;

 class TranslationHelper {

    public static function getTranslation($table,$attr,$id)
    {        
        return TranslationsWithText::find()->where([
					'table_name' => $table,
					'model_id'   => $id,
                    'attribute' => $attr,
				])->one()->value;    
    }

}
?>