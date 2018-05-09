<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class EmailFormComponent extends AbstractComponent
{

    function __construct()
    {
        parent::__construct();
    }

    public function build()
    {
        $feedbackEmail = DBPreferencesType::getPreferenceValue(Constants::FEEDBACK_MAIL);
        $tpl = parent::getEngine()->loadTemplate('components/emailForm/email-form.mustache');
        return $tpl->render([
            'feedbackEmail' => $feedbackEmail,
            'i18n' => Localization
        ]);
    }
}
