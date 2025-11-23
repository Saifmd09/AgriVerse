<?php
session_start();
require "db.php";

// -----------------------------------
// 1. Ensure email OTP verified
// -----------------------------------
if (!isset($_SESSION['investor_verified_email'])) {
    exit("Email not verified.");
}

$email = $_SESSION['investor_verified_email'];

// -----------------------------------
// 2. Collect form values
// -----------------------------------
$name = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirm_password"] ?? "";

$org_name = trim($_POST["org_name"] ?? "");
$designation = trim($_POST["designation"] ?? "");

$country = trim($_POST["country"] ?? "");
$state = trim($_POST["state"] ?? "");
$city = trim($_POST["city"] ?? "");
$address = trim($_POST["address"] ?? "");

$account_holder = trim($_POST["account_holder"] ?? "");
$bank_account = trim($_POST["bank_account"] ?? "");
$bank_ifsc = trim($_POST["bank_ifsc"] ?? "");
$pan = trim($_POST["pan"] ?? "");

// agri focus JSON
$focus = json_decode($_POST["focus"] ?? "[]", true);
$focus_json = json_encode($focus);

// -----------------------------------
// 3. Basic validation
// -----------------------------------
if (strlen($name) < 3) exit("Name too short.");
if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) exit("Invalid phone number.");
if ($password !== $confirm) exit("Passwords do not match.");
if (strlen($password) < 8) exit("Password too short.");

if (!$account_holder || !$bank_account || !$bank_ifsc || !$pan) {
    exit("Bank details missing.");
}

// -----------------------------------
// 4. Check duplicates
// -----------------------------------
$chk = $conn->prepare("SELECT id FROM investors WHERE email=? OR phone=?");
$chk->bind_param("ss", $email, $phone);
$chk->execute();
$chk->store_result();

if ($chk->num_rows > 0) exit("Email or phone already exists.");
$chk->close();

// -----------------------------------
// 5. Handle file uploads
// -----------------------------------
$uploadDir = "../uploads/investors/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

function saveFile($fileKey, $uploadDir) {
    if (!isset($_FILES[$fileKey])) return "";

    $file = $_FILES[$fileKey];
    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $newName = uniqid() . "." . $ext;

    $path = $uploadDir . $newName;

    if (move_uploaded_file($file["tmp_name"], $path)) {
        return $newName;
    }
    return "";
}

$citizen_proof = saveFile("citizen_proof", $uploadDir);
$bank_proof = saveFile("bank_proof", $uploadDir);

if (!$citizen_proof || !$bank_proof) {
    exit("File upload failed.");
}

// -----------------------------------
// 6. Insert into investors table
// -----------------------------------
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
INSERT INTO investors
(name,email,phone,password_hash,org_name,designation,focus,country,state,city,address,
 account_holder,bank_account,bank_ifsc,pan,citizen_proof,bank_proof)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
    "sssssssssssssssss",
    $name,$email,$phone,$hash,$org_name,$designation,$focus_json,
    $country,$state,$city,$address,
    $account_holder,$bank_account,$bank_ifsc,$pan,$citizen_proof,$bank_proof
);

if (!$stmt->execute()) {
    exit("DB Error: " . $stmt->error);
}

$investor_id = $stmt->insert_id;
$stmt->close();

// -----------------------------------
// 7. Insert verification record
// -----------------------------------
$ver = $conn->prepare("INSERT INTO investor_verification (investor_id) VALUES (?)");
$ver->bind_param("i", $investor_id);
$ver->execute();
$ver->close();

// -----------------------------------
// 8. Set session
// -----------------------------------
$_SESSION["investor_id"] = $investor_id;
$_SESSION["investor_name"] = $name;

// -----------------------------------
// 9. Response to JS
// -----------------------------------
echo "SUCCESS";
exit;

?>
