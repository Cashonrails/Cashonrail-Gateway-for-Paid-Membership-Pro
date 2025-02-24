<?php

if (!defined('ABSPATH')) {
    exit;
}

// Log request headers
$headers = getallheaders();
file_put_contents(__DIR__ . '/webhook.log', date("Y-m-d H:i:s") . " - Headers: " . json_encode($headers) . PHP_EOL, FILE_APPEND);

// Read Raw POST Data
$body = file_get_contents('php://input');

// Log raw request body
file_put_contents(__DIR__ . '/raw_input.log', date("Y-m-d H:i:s") . " - Raw Input: " . $body . PHP_EOL, FILE_APPEND);

if (empty($body)) {
    http_response_code(400);
    echo json_encode(["message" => "Empty request body."]);
    exit;
}

// Decode JSON
$data = json_decode($body, true);

if ($data === null) {
    file_put_contents(__DIR__ . '/webhook.log', date("Y-m-d H:i:s") . " - JSON Decode Error: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data."]);
    exit;
}

// Validate necessary fields
if (
    isset($data['event'], $data['data']['metadata']['user_id'], $data['data']['metadata']['membership_level']) &&
    $data['event'] === 'charge.success'
) {
    $user_id = intval($data['data']['metadata']['user_id']);
    $membership_level_id = intval($data['data']['metadata']['membership_level']);

    // Activate Membership
    pmpro_changeMembershipLevel($membership_level_id, $user_id);

    http_response_code(200);
    echo json_encode(["message" => "Payment confirmed. Membership activated."]);
    exit;
}

// Log invalid webhook data
file_put_contents(__DIR__ . '/webhook.log', date("Y-m-d H:i:s") . " - Invalid webhook data: " . json_encode($data) . PHP_EOL, FILE_APPEND);

http_response_code(400);
echo json_encode(["message" => "Invalid webhook data."]);
exit;
