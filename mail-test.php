<?php
/**
 * Summer Inspection & Logistique W/A LTD
 * Mail Delivery & SMTP Diagnostic Tool
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mode = isset($_POST['mode']) ? $_POST['mode'] : 'smtp';
$targetEmail = isset($_POST['test_email']) ? trim($_POST['test_email']) : 'biz@summerinspectlog.com';
$smtpHost = isset($_POST['smtp_host']) ? trim($_POST['smtp_host']) : 'smtppro.zoho.com';
$smtpPort = isset($_POST['smtp_port']) ? intval($_POST['smtp_port']) : 465;
$smtpUser = isset($_POST['smtp_user']) ? trim($_POST['smtp_user']) : 'biz@summerinspectlog.com';
$smtpPass = isset($_POST['smtp_pass']) ? $_POST['smtp_pass'] : '';

$resultMessage = '';
$isSuccess = null;
$debugLog = [];

/**
 * Pure PHP SMTP Client (No Composer / No External Dependencies Required)
 */
function sendViaSMTP($host, $port, $user, $pass, $from, $to, $subject, $bodyText, &$debugLog) {
    $debugLog[] = "Connecting to {$host}:{$port} ...";
    
    $timeout = 15;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $protocol = ($port == 465) ? 'ssl://' : '';
    $socket = @stream_socket_client($protocol . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    
    if (!$socket) {
        $debugLog[] = "ERROR: Failed to connect: {$errstr} ({$errno})";
        return false;
    }
    
    $readResponse = function() use ($socket, &$debugLog) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        $debugLog[] = "S: " . trim($response);
        return $response;
    };
    
    $sendCommand = function($cmd, $mask = false) use ($socket, &$debugLog) {
        $debugLog[] = "C: " . ($mask ? "******" : $cmd);
        fputs($socket, $cmd . "\r\n");
    };
    
    // 1. Initial greeting
    $greeting = $readResponse();
    if (substr($greeting, 0, 3) != '220') return false;
    
    // 2. EHLO
    $hostname = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'summerinspectlog.com';
    $sendCommand("EHLO " . $hostname);
    $readResponse();
    
    // 3. STARTTLS if port 587
    if ($port == 587) {
        $sendCommand("STARTTLS");
        $res = $readResponse();
        if (substr($res, 0, 3) != '220') return false;
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $sendCommand("EHLO " . $hostname);
        $readResponse();
    }
    
    // 4. AUTH LOGIN
    $sendCommand("AUTH LOGIN");
    $res = $readResponse();
    if (substr($res, 0, 3) != '334') return false;
    
    $sendCommand(base64_encode($user));
    $res = $readResponse();
    if (substr($res, 0, 3) != '334') return false;
    
    $sendCommand(base64_encode($pass), true);
    $res = $readResponse();
    if (substr($res, 0, 3) != '235') {
        $debugLog[] = "ERROR: Authentication failed! Please double-check the mailbox password.";
        return false;
    }
    
    // 5. MAIL FROM
    $sendCommand("MAIL FROM: <{$from}>");
    $res = $readResponse();
    if (substr($res, 0, 3) != '250') return false;
    
    // 6. RCPT TO
    $sendCommand("RCPT TO: <{$to}>");
    $res = $readResponse();
    if (substr($res, 0, 3) != '250') return false;
    
    // 7. DATA
    $sendCommand("DATA");
    $res = $readResponse();
    if (substr($res, 0, 3) != '354') return false;
    
    // 8. Headers & Body
    $headers = [];
    $headers[] = "From: Summer Inspection & Logistique <{$from}>";
    $headers[] = "To: <{$to}>";
    $headers[] = "Subject: {$subject}";
    $headers[] = "Date: " . date('r');
    $headers[] = "Message-ID: <" . md5(uniqid(time())) . "@summerinspectlog.com>";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    
    $payload = implode("\r\n", $headers) . "\r\n\r\n" . $bodyText . "\r\n.\r\n";
    fputs($socket, $payload);
    $debugLog[] = "C: [Sent Headers and Message Body]";
    
    $res = $readResponse();
    $sendCommand("QUIT");
    fclose($socket);
    
    return (substr($res, 0, 3) == '250');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
        $resultMessage = "Invalid email format: " . htmlspecialchars($targetEmail);
        $isSuccess = false;
    } elseif ($mode === 'smtp') {
        if (empty($smtpPass)) {
            $resultMessage = "Please enter your Hostinger email password for <strong>" . htmlspecialchars($smtpUser) . "</strong>.";
            $isSuccess = false;
        } else {
            $subject = "Hostinger SMTP Test Delivery - " . date('Y-m-d H:i:s');
            $body = "Hello,\r\n\r\nThis is a SUCCESSFUL authenticated SMTP test from Summer Inspection & Logistique W/A LTD.\r\n\r\n" .
                    "Hostinger SMTP server accepted and verified this transmission.\r\n" .
                    "Timestamp: " . date('r') . "\r\n" .
                    "Sent via: smtp.hostinger.com (Port {$smtpPort})\r\n";
            
            $sent = sendViaSMTP($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpUser, $targetEmail, $subject, $body, $debugLog);
            if ($sent) {
                $isSuccess = true;
                $resultMessage = "🎉 <strong>SUCCESS! Authenticated via Hostinger SMTP.</strong><br><br>" .
                                 "Hostinger's official mail server verified your credentials and accepted the email for immediate delivery to <strong>" . htmlspecialchars($targetEmail) . "</strong>.<br>" .
                                 "Check your inbox right now!";
            } else {
                $isSuccess = false;
                $resultMessage = "❌ <strong>SMTP Authentication Failed.</strong><br>Check the server conversation log below to see what Hostinger returned (most often wrong password).";
            }
        }
    } else {
        // Fallback PHP mail()
        $subject = "PHP mail() Diagnostic Test - " . date('Y-m-d H:i:s');
        $message = "Test message from PHP mail().";
        $headers = "From: Summer Inspection <biz@summerinspectlog.com>\r\n" .
                   "Reply-To: biz@summerinspectlog.com\r\n" .
                   "Date: " . date('r') . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        $sent = @mail($targetEmail, $subject, $message, $headers, "-f biz@summerinspectlog.com");
        if ($sent) {
            $isSuccess = true;
            $resultMessage = "Local PHP mail() sent message to local queue. However, if this email never appears in your inbox, Hostinger is blocking unauthenticated PHP mail() at server level.";
        } else {
            $isSuccess = false;
            $resultMessage = "PHP mail() returned FALSE. Server completely disabled mail().";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SILWA - Email & SMTP Tester</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0b1528; color: #e2e8f0; padding: 40px 20px; margin: 0; }
    .card { max-width: 650px; margin: 0 auto; background: #132238; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow: hidden; border: 1px solid #1e3352; }
    .header { background: #0c3ba1; color: white; padding: 24px 30px; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
    .header p { margin: 6px 0 0 0; font-size: 13px; opacity: 0.9; }
    .content { padding: 30px; }
    .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; line-height: 1.5; }
    .alert-success { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; }
    .alert-error { background: #4c1d24; border: 1px solid #e11d48; color: #fecdd3; }
    .form-group { margin-bottom: 18px; }
    label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px; color: #94a3b8; }
    input[type="text"], input[type="email"], input[type="password"] { width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0a1424; border: 1px solid #243b5e; border-radius: 6px; font-size: 14px; color: white; }
    input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
    .grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }
    button { background: #2563eb; color: white; border: none; padding: 13px 24px; font-size: 15px; font-weight: 600; border-radius: 6px; cursor: pointer; width: 100%; transition: background 0.2s; margin-top: 10px; }
    button:hover { background: #1d4ed8; }
    .log-box { margin-top: 25px; background: #050b14; border: 1px solid #1e293b; border-radius: 8px; padding: 15px; font-family: 'Courier New', monospace; font-size: 12px; color: #38bdf8; max-height: 250px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; }
    .note { margin-top: 20px; font-size: 12px; color: #94a3b8; line-height: 1.6; background: #0a1424; padding: 12px 16px; border-radius: 6px; border-left: 3px solid #3b82f6; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h1>Summer Inspection &amp; Logistique W/A LTD</h1>
      <p>Zoho Mail Authenticated SMTP Live Diagnostic Tool</p>
    </div>
    <div class="content">
      <?php if (!empty($resultMessage)): ?>
        <div class="alert <?php echo $isSuccess ? 'alert-success' : 'alert-error'; ?>">
          <?php echo $resultMessage; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="mode" value="smtp">

        <div class="form-group">
          <label>Zoho Mailbox (Sender &amp; Username):</label>
          <input type="email" name="smtp_user" value="<?php echo htmlspecialchars($smtpUser); ?>" required>
        </div>

        <div class="form-group">
          <label>Zoho Mailbox Password (or Zoho App Password):</label>
          <input type="password" name="smtp_pass" placeholder="Enter Zoho account password or App Password" required autofocus>
        </div>

        <div class="grid-2">
          <div class="form-group">
            <label>SMTP Host:</label>
            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($smtpHost); ?>" required>
          </div>
          <div class="form-group">
            <label>Port:</label>
            <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($smtpPort); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>Send Test Email To (Recipient):</label>
          <input type="email" name="test_email" value="<?php echo htmlspecialchars($targetEmail); ?>" required placeholder="biz@summerinspectlog.com or personal Gmail">
        </div>

        <button type="submit">🚀 Connect &amp; Send via Zoho SMTP</button>
      </form>

      <?php if (!empty($debugLog)): ?>
        <div style="margin-top: 25px;">
          <label style="font-weight: 600; font-size: 13px; color: #cbd5e1;">Live Zoho Server Handshake Log:</label>
          <div class="log-box"><?php echo htmlspecialchars(implode("\n", $debugLog)); ?></div>
        </div>
      <?php endif; ?>

      <div class="note">
        💡 <strong>Why Zoho SMTP?</strong> Your domain's MX records point directly to <code>mx.zoho.com</code>. Zoho Mail requires outgoing emails claiming to be from <code>@summerinspectlog.com</code> to authenticate via Zoho's secure SMTP server (<code>smtppro.zoho.com</code>).
      </div>
    </div>
  </div>
</body>
</html>
