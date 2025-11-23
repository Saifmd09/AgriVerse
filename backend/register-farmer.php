<?php
/*************************************************
 *  register-farmer.php – OTP-Based Farmer Registration
 *************************************************/

session_start();
require "db.php";

/* ------------------------
   1) Ensure Email Verified via OTP
   ------------------------ */
if (!isset($_SESSION["pending_email"]) || !isset($_SESSION["email_otp"])) {
    exit("⚠️ Please verify your email first before registration.");
}

$email = $_SESSION["pending_email"];

/* ------------------------
   2) COLLECT FORM DATA
   ------------------------ */
$name = trim($_POST["name"] ?? '');
$phone = trim($_POST["phone"] ?? '');
$password = $_POST["password"] ?? '';
$confirm = $_POST["confirm_password"] ?? '';
$gender = $_POST["gender"] ?? '';
$dob = $_POST["dob"] ?? '';
$state = trim($_POST["state"] ?? '');
$district = trim($_POST["district"] ?? '');
$village = trim($_POST["village"] ?? '');
$address = trim($_POST["address"] ?? '');
$land = $_POST["land_area_acres"] ?? '';
$farming = $_POST["farming_type"] ?? '';
$aadhaar = $_POST["aadhaar_last4"] ?? '';
$bank = trim($_POST["bank_account"] ?? '');
$ifsc = trim($_POST["bank_ifsc"] ?? '');

/* ------------------------
   3) BASIC VALIDATIONS
   ------------------------ */
if (strlen($name) < 3) exit("❌ Name too short");
if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) exit("❌ Invalid Phone Number");
if ($password !== $confirm) exit("❌ Passwords don't match");
if (strlen($password) < 8) exit("❌ Password must be at least 8 characters");
if (!preg_match("/^[0-9]{4}$/", $aadhaar)) exit("❌ Aadhaar last 4 digits required");
if ($land <= 0) exit("❌ Invalid land size");

/* ------------------------
   4) CHECK DUPLICATE PHONE
   ------------------------ */
$check = $conn->prepare("SELECT id FROM farmers WHERE phone = ?");
$check->bind_param("s", $phone);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    exit("❌ Phone already exists");
}
$check->close();

/* ------------------------
   5) HASH PASSWORD
   ------------------------ */
$hash = password_hash($password, PASSWORD_BCRYPT);

/* ------------------------
   6) INSERT INTO FARMERS TABLE
   ------------------------ */
$stmt = $conn->prepare("
INSERT INTO farmers 
(name, email, phone, password_hash, gender, dob, state, district, village, address, 
 land_area_acres, farming_type, aadhaar_last4, bank_account, bank_ifsc, created_at)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())
");

$stmt->bind_param(
    "ssssssssssissss",
    $name, $email, $phone, $hash, $gender, $dob,
    $state, $district, $village, $address,
    $land, $farming, $aadhaar, $bank, $ifsc
);

if (!$stmt->execute()) {
    exit("❌ Registration failed: " . $stmt->error);
}

$farmer_id = $stmt->insert_id;
$stmt->close();

/* ------------------------
   7) CREATE FARMER WALLET (token balance starts at 0)
   ------------------------ */
$wallet = $conn->prepare("INSERT INTO farmer_wallet (farmer_id, token_balance, total_received, total_spent)
                          VALUES (?, 0, 0, 0)");
$wallet->bind_param("i", $farmer_id);
$wallet->execute();
$wallet->close();

/* ------------------------
   8) CLEAR OTP SESSION
   ------------------------ */
unset($_SESSION["pending_email"], $_SESSION["email_otp"], $_SESSION["email_otp_expires"]);

/* ------------------------
   9) LOGIN SESSION
   ------------------------ */
$_SESSION["farmer_id"] = $farmer_id;
$_SESSION["farmer_name"] = $name;

/* ------------------------
   10) SUCCESS RESPONSE
   ------------------------ */
echo "SUCCESS";
exit;

?>
