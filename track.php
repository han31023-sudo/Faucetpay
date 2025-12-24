<?php
// track.php
// Simple click logger + redirector for FaucetPay referral
// Writes to clicks.csv in same directory (make sure webserver can write).
// Example usage:
//  track.php?utm_source=telegram&utm_medium=post&utm_campaign=ref2025

date_default_timezone_set('UTC');

// Config
$default_ref = '4289483';
$click_log_file = __DIR__ . '/clicks.csv';

// Get params (sanitize lightly)
$r = preg_replace('/[^0-9]/', '', ($_GET['r'] ?? $default_ref));
$utm_source = substr((string)($_GET['utm_source'] ?? ''), 0, 100);
$utm_medium = substr((string)($_GET['utm_medium'] ?? ''), 0, 100);
$utm_campaign = substr((string)($_GET['utm_campaign'] ?? ''), 0, 100);

// Build referral URL
$base = 'https://faucetpay.io/';
$query = http_build_query([
    'r' => $r,
    'utm_source' => $utm_source ?: 'unknown',
    'utm_medium' => $utm_medium ?: 'unknown',
    'utm_campaign' => $utm_campaign ?: 'referral'
]);
$redirect_url = $base . '?' . $query;

// Prepare log entry
$ts = gmdate('Y-m-d\TH:i:s\Z');
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$page = $_SERVER['REQUEST_URI'] ?? ''; // includes query

$log_row = [
    $ts,
    $ip,
    $ua,
    $referer,
    $utm_source,
    $utm_medium,
    $utm_campaign,
    $page,
    $r,
    $redirect_url
];

// Append to CSV with locking
$header = ['timestamp','ip','user_agent','referer','utm_source','utm_medium','utm_campaign','page','ref_id','redirect_url'];
$need_header = !file_exists($click_log_file) || filesize($click_log_file) === 0;

if ($fp = @fopen($click_log_file, 'a')) {
    if (flock($fp, LOCK_EX)) {
        if ($need_header) {
            fputcsv($fp, $header);
        }
        fputcsv($fp, $log_row);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
} else {
    // logging failed; don't block redirect
    error_log("track.php: gagal membuka file log ({$click_log_file})");
}

// Perform redirect (302)
header('Location: ' . $redirect_url, true, 302);
exit;
?>
