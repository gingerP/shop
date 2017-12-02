<?php
include_once('src/back/import/db');
include_once('src/back/import/import');
class EmailService {

    public static function sendFeedbackEmail($messageBody, $senderEmail, $senderName) {
        $sendToEmails = DBPreferencesType::getPreferenceValue(Constants::FEEDBACK_MAIL);
        $sendToEmails = explode(";", $sendToEmails);
        $systemMail = DBPreferencesType::getPreferenceValue(Constants::SYSTEM_MAIL);

        if ($sendToEmails == "") {
            echo 'error';
            Log::error("empty system email: $senderEmail, $senderName");
            return false;
        }
        $headers = "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\nFrom: new.vinni@gmail.com\r\nBcc: $systemMail\r\nX-Mailer: PHP/".phpversion();

        //$headers = 'From: new.vinni@gmail.com\r\n'.'X-Mailer: PHP/' . phpversion();;
        //return 'before4 '.implode(', ', $sendToEmails);
        $res = mail(
            implode(', ', $sendToEmails),
            "FEEDBACK from $senderName ($senderEmail)",
            $messageBody,
            $headers
        );
        /*$res = mail($sendToEmails[0], "FEEDBACK from $senderName ($senderEmail)", $messageBody, $headers);*/
        Log::info($res." send email: $senderEmail, $senderName - to ".$sendToEmails[0]);
        /*return $res;*/

        //Log::info("$res send email: $senderEmail, $senderName - to .$sendToEmails");
        return $res;
    }
}