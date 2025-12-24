<?php
// referral-generator.php
// Simple referral URL generator + optional tinyurl shorten
// Usage via CLI or web: ?id=4289483&source=telegram&medium=post&campaign=ref2025&shorten=1

function build_referral($id, $utm = []) {
    $base = 'https://faucetpay.io/';
    $params = ['r' => $id] + $utm;
    return $base . '?' . http_build_query($params);
}

function tinyurl_shorten($url) {
    // TinyURL basic API (no key): https://tinyurl.com/api-create.php?url=...
    $api = 'https://tinyurl.com/api-create.php?url=' . urlencode($url);
    $ch = curl_init($api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $short = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($short === false || !$short) return null;
    return $short;
}

// Input
$id = isset($_GET['id']) ? $_GET['id'] : getenv('REF_ID') ?: '4289483';
$utm = [
    'utm_source'   => $_GET['source'] ?? 'website',
    'utm_medium'   => $_GET['medium'] ?? 'banner',
    'utm_campaign' => $_GET['campaign'] ?? 'referral'
];

$ref = build_referral($id, $utm);
$shorten = isset($_GET['shorten']) && $_GET['shorten'] === '1';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Referral Generator</title></head>
<body>
<h2>Referral URL</h2>
<p><strong>Full:</strong> <a href="<?php echo htmlspecialchars($ref); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($ref); ?></a></p>

<?php if ($shorten): 
    $short = tinyurl_shorten($ref);
    ?>
    <p><strong>Short:</strong>
    <?php if ($short): ?>
        <a href="<?php echo htmlspecialchars($short); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($short); ?></a>
    <?php else: ?>
        (Gagal memperpendek URL)
    <?php endif; ?>
    </p>
<?php endif; ?>

<h3>Embed snippet (iframe)</h3>
<pre>&lt;iframe src="https://tap-coin.de/banners/tc_728.html" width="728" height="90" frameborder="0"&gt;&lt;/iframe&gt;</pre>

<h3>Anchor + referral</h3>
<pre>&lt;a href="<?php echo htmlspecialchars($ref); ?>" target="_blank" rel="noopener noreferrer"&gt;Daftar FaucetPay lewat saya&lt;/a&gt;</pre>

</body>
</html>
<?php
// End of file
?>
