<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

/* ---------------------------------------------------
   .env loader
---------------------------------------------------- */
function env($key, $default = null) {
    static $loaded = false;
    static $data = [];
    if (!$loaded) {
        $path = __DIR__ . '/.env';
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (!strpos($line, '=')) continue;
                list($k, $v) = explode('=', $line, 2);
                $data[trim($k)] = trim($v);
            }
        }
        $loaded = true;
    }
    return $data[$key] ?? $default;
}

/* ---------------------------------------------------
   Shared SMTP Mailer Setup
---------------------------------------------------- */
function MailerBase() {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = env('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('SMTP_USER');
        $mail->Password = env('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = env('SMTP_PORT', 587);

        // Debug optional
        if (env('SMTP_DEBUG', '0') == '1') {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';
        }

        $mail->setFrom(env('MAIL_FROM', env('SMTP_USER')), env('MAIL_FROM_NAME', 'AgriVerse'));

    } catch (Exception $e) {
        return false;
    }

    return $mail;
}

/* ---------------------------------------------------
   SEND OTP Email (Farmer / Investor)
---------------------------------------------------- */
function sendOTPEmail($toEmail, $toName, $otp) {
    $mail = MailerBase();
    if (!$mail) return false;

    try {
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = "Your AgriVerse OTP";
        $mail->Body = "
            <h2>Your OTP: <b style='color:green;'>$otp</b></h2>
            <p>This OTP is valid for 10 minutes.</p>
        ";
        $mail->AltBody = "Your OTP: $otp. Valid for 10 minutes.";

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}

/* ---------------------------------------------------
   SEND PLAIN HTML EMAIL (used for Investor OTP)
---------------------------------------------------- */
function sendPlainEmail($toEmail, $toName, $subject, $body) {
    $mail = MailerBase();
    if (!$mail) return false;

    try {
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}
