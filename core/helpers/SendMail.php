<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

final class SendMail
{
    public static function Send(array $addresses, string $subject, string $body, string $altBody)
    {
        try {
            global $cfg;

            $mail = new PHPMailer(true);
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = $cfg['mailHost'];
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['mailPassword'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $cfg['mailPort'];

            $mail->setFrom($cfg['mainAddress'], $cfg['name']);
            $mail->addReplyTo($cfg['mainAddress']);
            $mail->addCC($cfg['mainAddress']);
            $mail->addBCC($cfg['mainAddress']);

            foreach ($addresses as $ad) {
                $mail->addAddress($ad);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody;

            $mail->send();
            $addressesString = implode(", ", $addresses);
            Logger::Log("($subject) mail successfully sent to: [$addressesString]. ", logLvl::Info);
        } catch (\Throwable $th) {
            Logger::Log("Email elküldése sikertelen ($subject). ".$th->getMessage(), logLvl::Error);
            throw new MailException("Email elküldése sikertelen ($subject). ".$th->getMessage());
        }
    }
}
