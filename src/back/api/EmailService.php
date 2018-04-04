<?php
include_once('src/back/import/db');
include_once('src/back/import/import');

class EmailService
{

    public static function sendFeedbackEmail($messageBody, $senderEmail, $senderName, $fromProductCode = '')
    {
        $sendToEmails = DBPreferencesType::getPreferenceValue(Constants::FEEDBACK_MAIL);
        $sendToEmails = explode(";", $sendToEmails);
        $systemMail = DBPreferencesType::getPreferenceValue(Constants::SYSTEM_MAIL);

        if (count($sendToEmails) == 0 || $sendToEmails[0] == '') {
            Log::info("Empty system email: $senderEmail, $senderName");
            throw new Exception('Empty system email.');
        }

        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: new.vinni@gmail.com\r\nBcc: $systemMail\r\nX-Mailer: PHP/" . phpversion();

        $res = mail(
            implode(', ', $sendToEmails),
            self::getEmailSubject($senderEmail, $senderName),
            self::getHtmlMessage($messageBody, $senderEmail, $senderName, $fromProductCode),
            $headers
        );
        if (!$res) {
            $error = error_get_last();
            throw new Exception('Email sending failed: '.$error['message']);
        }
        Log::info("Email sended: $senderEmail, $senderName - to " . implode(', ', $sendToEmails));
        return $res;
    }

    private static function getHtmlMessage($messageBody, $senderEmail, $senderName, $fromProductCode)
    {
        $publicUrl = DBPreferencesType::getPreferenceValue(Constants::PUBLIC_URL);
        $escapedMessageBody = htmlentities($messageBody);
        $html = "<!doctype html>
                    <html>
                      <head>
                        <meta name=\"viewport\" content=\"width=device-width\" />
                        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
                        <title>Email from </title>
                        </head>
                        <body>
                            $escapedMessageBody";
        if ($fromProductCode !== '') {
            $Products = new DBGoodsType();
            $product = $Products->getByCode($fromProductCode);
            if (!is_null($product)) {
                $productUrl = FileUtils::buildPath(
                    $publicUrl,
                    URLBuilder::getCatalogLinkForSingleItem($product["key_item"])
                );
                $productName = $product[DB::TABLE_GOODS__NAME];
                $html .= "<hr><br>
                        <span style=\"font-weight: bold;\">
                            <span style=\"color: grey;\">Сообщение отправлено со страницы товара&nbsp</span>
                            <a style=\"color: #414141;\" href=\"$productUrl\">$productName</a>
                        </span>";
            }
        }

        $html .= "</body></html>";
        return $html;
    }

    private static function getEmailSubject($senderName, $senderEmail) {
        return "Сообщение с сайта от $senderName ($senderEmail)";
    }
}