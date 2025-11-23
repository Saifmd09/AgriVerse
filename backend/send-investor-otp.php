<?php
session_start();
require "mail_helper.php";

$email = trim($_POST['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("INVALID_EMAIL");
}

$otp = rand(100000, 999999);

// ✅ Save OTP for investor
$_SESSION['investor_pending_email'] = $email;
$_SESSION['investor_email_otp'] = $otp;
$_SESSION['investor_email_otp_expires'] = time() + 600;

$subject = "Your AgriVerse Investor OTP";
$body = "<h2>Your OTP: <b style='color:green;'>$otp</b></h2><p>Valid for 10 minutes.</p>";

$sent = sendPlainEmail($email, "Investor", $subject, $body);

echo $sent ? "OTP_SENT" : "OTP_FAILED";
?>
