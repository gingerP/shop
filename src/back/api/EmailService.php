<?php
include_once('db');
include_once('import');
class EmailService {

    public static function sendFeedbackEmail($messageBody, $senderEmail, $senderName) {
        $dbPrefrencesType = new DBPreferencesType();
        $sendToEmails = $dbPrefrencesType->getPreference(Constants::FEEDBACK_MAIL);
        $sendToEmails = explode(";", $sendToEmails[DB::TABLE_PREFERENCES__VALUE]);
        $systemMail = $dbPrefrencesType->getPreference(Constants::SYSTEM_MAIL);
        $systemMail = $systemMail[DB::TABLE_PREFERENCES__VALUE];
        if ($sendToEmails == "") {
            Log::error("empty system email: $senderEmail, $senderName");
            return false;
        }
        $headers = "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\nFrom: new.vinni@gmail.com\r\nBcc: $systemMail\r\nX-Mailer: PHP/".phpversion();

        //$headers = 'From: new.vinni@gmail.com\r\n'.'X-Mailer: PHP/' . phpversion();;

        for($emailIndex = 0; $emailIndex < count($senderEmail); $emailIndex++) {
            $res = mail(implode(', ', $sendToEmails), "FEEDBACK from $senderName ($senderEmail)", $messageBody, $headers);
            /*$res = mail($sendToEmails[0], "FEEDBACK from $senderName ($senderEmail)", $messageBody, $headers);*/
            Log::info($res." send email: $senderEmail, $senderName - to ".$sendToEmails[0]);
        }
        /*return $res;*/



        Log::info("$res send email: $senderEmail, $senderName - to .$sendToEmails");
        return $res;
    }
}