<?php
session_start();
require "mail_helper.php";

$email = trim($_POST['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("INVALID_EMAIL");
}

$otp = rand(100000, 999999);

// store OTP
$_SESSION["supplier_email_pending"] = $email;
$_SESSION["supplier_email_otp"] = $otp;
$_SESSION["supplier_email_expires"] = time() + 600; // 10 min

$subject = "Your AgriVerse Supplier OTP";
$body = "
    <h2>Your OTP: <b style='color:green;'>$otp</b></h2>
    <p>This OTP is valid for 10 minutes.</p>
";

$sent = sendPlainEmail($email, "Supplier", $subject, $body);

echo $sent ? "OTP_SENT" : "OTP_FAILED";
?>
