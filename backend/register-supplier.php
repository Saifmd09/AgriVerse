<?php
session_start();
require "db.php";

// --------------------
// 1. Check Email Verification
// --------------------
if (!isset($_SESSION["supplier_email_verified"])) {
    exit("EMAIL_NOT_VERIFIED");
}

$email = $_SESSION["supplier_email_verified"];

// --------------------
// 2. Collect Form Data
// --------------------
$business_name = trim($_POST["business_name"] ?? "");

$categories = $_POST["category"] ?? [];
$other_category = trim($_POST["other_text"] ?? "");

$phone = trim($_POST["phone"] ?? "");
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirm_password"] ?? "";

$address = trim($_POST["address"] ?? "");
$state = trim($_POST["state"] ?? "");
$district = trim($_POST["district"] ?? "");
$pincode = trim($_POST["pincode"] ?? "");

$gst_number = trim($_POST["gst_number"] ?? "");
$pan_number = trim($_POST["pan_number"] ?? "");
$business_license = trim($_POST["business_license"] ?? "");

$bank_account = trim($_POST["bank_account"] ?? "");
$bank_name = trim($_POST["bank_name"] ?? "");
$bank_ifsc = trim($_POST["bank_ifsc"] ?? "");
$account_holder = trim($_POST["account_holder"] ?? "");

// --------------------
// 3. Validations
// --------------------
if (strlen($business_name) < 3) exit("Invalid business name");
if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) exit("Invalid phone number");
if ($password !== $confirm) exit("Passwords do not match");
if (strlen($password) < 8) exit("Password too short");
if (!$address || !$state || !$district || !$pincode) exit("Address incomplete");
if (!$gst_number || !$pan_number || !$business_license) exit("Registration details missing");
if (!$bank_account || !$bank_name || !$bank_ifsc || !$account_holder) exit("Bank details missing");

// --------------------
// 4. Validate Categories
// --------------------
if (empty($categories)) {
    exit("Please select at least one supply category.");
}

if (in_array("other", $categories)) {
    if (!$other_category) exit("Enter custom category");
    $categories[] = $other_category;
    $categories = array_diff($categories, ["other"]);
}

$categories_json = json_encode(array_values($categories));

// --------------------
// 5. Check duplicate Email/Phone
// --------------------
$chk = $conn->prepare("SELECT id FROM suppliers WHERE email=? OR phone=?");
$chk->bind_param("ss", $email, $phone);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) exit("Email or Phone already exists");
$chk->close();

// --------------------
// 6. File Upload
// --------------------
$uploadDir = "../uploads/suppliers/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

function saveFile($key, $uploadDir) {
    if (!isset($_FILES[$key])) return "";

    $ext = pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION);
    $name = uniqid() . "." . $ext;
    $path = $uploadDir . $name;

    return move_uploaded_file($_FILES[$key]["tmp_name"], $path) ? $name : "";
}

$gst_certificate = saveFile("gst_certificate", $uploadDir);
$id_proof = saveFile("id_proof", $uploadDir);
$warehouse_photo = saveFile("warehouse_photo", $uploadDir);
$business_license_doc = saveFile("business_license_doc", $uploadDir);

if (!$gst_certificate || !$id_proof || !$warehouse_photo || !$business_license_doc) {
    exit("Failed to upload documents");
}

// --------------------
// 7. Insert Supplier
// --------------------
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
INSERT INTO suppliers
(business_name, categories, email, phone, password_hash,
 address, state, district, pincode,
 gst_number, pan_number, business_license,
 bank_account, bank_name, bank_ifsc, account_holder,
 gst_certificate, id_proof, warehouse_photo, business_license_doc)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "ssssssssssssssssssss",
    $business_name, $categories_json, $email, $phone, $hash,
    $address, $state, $district, $pincode,
    $gst_number, $pan_number, $business_license,
    $bank_account, $bank_name, $bank_ifsc, $account_holder,
    $gst_certificate, $id_proof, $warehouse_photo, $business_license_doc
);

if (!$stmt->execute()) {
    exit("DB_ERROR: " . $stmt->error);
}

$supplier_id = $stmt->insert_id;
$stmt->close();

// --------------------
// 8. Create Verification Record
// --------------------
$v = $conn->prepare("INSERT INTO supplier_verification (supplier_id) VALUES (?)");
$v->bind_param("i", $supplier_id);
$v->execute();
$v->close();

// --------------------
// 9. Login Session
// --------------------
$_SESSION["supplier_id"] = $supplier_id;
$_SESSION["supplier_name"] = $business_name;

// --------------------
// ✅ 10. SUCCESS
// --------------------
echo "SUCCESS";
exit;
?>
