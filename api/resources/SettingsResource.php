<?php

namespace api\resources;

use common\models\Settings;

class SettingsResource extends Settings
{
    public function fields()
    {
        return [
            "logo_path",
            "who_are_we",
            "privacy_policy",
            "conditions",
            "linkedin",
            "instagram",
            "twitter",
            "facebook",
            "whatsapp",
            "youtube",
            "telegram",
        ];
    }
}
