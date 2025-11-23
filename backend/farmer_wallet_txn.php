<?php
require 'db.php';
require 'auth_guard.php';
require 'json.php';

/* Inputs: type = credit|debit, amount (int), description (string) */
$type = $_POST['type'] ?? '';
$amount = intval($_POST['amount'] ?? 0);
$desc = trim($_POST['description'] ?? '');

if (!in_array($type, ['credit','debit'])) json_err('Invalid type');
if ($amount <= 0) json_err('Invalid amount');
if ($desc === '') json_err('Description required');

$conn->begin_transaction();

try {
    // Current balance (ensure row exists)
    $conn->query("INSERT IGNORE INTO farmer_wallet (farmer_id,balance_tokens) VALUES ($FARMER_ID,0)");
    $balQ = $conn->prepare("SELECT balance_tokens FROM farmer_wallet WHERE farmer_id=? FOR UPDATE");
    $balQ->bind_param("i", $FARMER_ID);
    $balQ->execute();
    $balQ->bind_result($balance);
    $balQ->fetch();
    $balQ->close();

    $newBalance = $balance + ($type === 'credit' ? $amount : -$amount);
    if ($newBalance < 0) throw new Exception('INSUFFICIENT_FUNDS');

    // insert txn
    $ins = $conn->prepare("INSERT INTO farmer_transactions (farmer_id, txn_type, amount, description) VALUES (?,?,?,?)");
    $ins->bind_param("isis", $FARMER_ID, $type, $amount, $desc);
    $ins->execute();
    $ins->close();

    // update wallet
    $up = $conn->prepare("UPDATE farmer_wallet SET balance_tokens=? WHERE farmer_id=?");
    $up->bind_param("ii", $newBalance, $FARMER_ID);
    $up->execute();
    $up->close();

    $conn->commit();
    json_ok(['balance' => $newBalance]);

} catch (Exception $e) {
    $conn->rollback();
    if ($e->getMessage() === 'INSUFFICIENT_FUNDS') json_err('INSUFFICIENT_FUNDS', 400);
    json_err('TXN_FAILED', 500);
}
