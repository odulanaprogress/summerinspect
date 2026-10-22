<?php
/**
 * Summer Inspection & Logistique W/A LTD
 * Diagnostic Mail Delivery Tester
 * 
 * Usage:
 * 1. Upload this file to your public_html folder.
 * 2. Visit https://summerinspectlog.com/mail-test.php in your browser.
 * 3. Enter your test email address and click "Send Test Email".
 * 4. Check both your Inbox and Spam/Junk folder.
 * 5. (Important) Delete this file after testing for security.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$targetEmail = isset($_POST['test_email']) ? trim($_POST['test_email']) : 'biz@summerinspectlog.com';
$resultMessage = '';
$isSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($targetEmail)) {
    if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
        $resultMessage = "Invalid email format: " . htmlspecialchars($targetEmail);
        $isSuccess = false;
    } else {
        $subject = "SILWA Mail Delivery Test - " . date('Y-m-d H:i:s');
        $message = "Hello,\r\n\r\nThis is a verification test from Summer Inspection & Logistique W/A LTD.\r\n" .
                   "If you received this email, PHP mail delivery is WORKING properly on your server.\r\n\r\n" .
                   "Server: " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n" .
                   "Timestamp: " . date('r') . "\r\n" .
                   "Sender: no-reply@summerinspectlog.com\r\n";

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=UTF-8';
        $headers[] = 'From: SILWA Verification <biz@summerinspectlog.com>';
        $headers[] = 'Reply-To: biz@summerinspectlog.com';
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . md5(uniqid(time())) . '@summerinspectlog.com>';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        // Attempt delivery with -f parameter (required by Hostinger to prevent mail drop)
        $sent = @mail($targetEmail, $subject, $message, implode("\r\n", $headers), "-f biz@summerinspectlog.com");

        if ($sent) {
            $isSuccess = true;
            $resultMessage = "Email successfully dispatched to: <strong>" . htmlspecialchars($targetEmail) . "</strong>!<br><br>" .
                             "Please check both your <strong>Inbox</strong> and <strong>Spam / Junk folder</strong> in the next 1-3 minutes.";
        } else {
            $isSuccess = false;
            $lastError = error_get_last();
            $resultMessage = "PHP mail() returned FALSE. Server could not send the message.<br>" .
                             "Error details: " . ($lastError ? htmlspecialchars($lastError['message']) : 'No specific error returned by PHP.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SILWA - Email Delivery Test Tool</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f1f5f9; color: #1e293b; padding: 40px 20px; margin: 0; }
    .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0; }
    .header { background: #0c3ba1; color: white; padding: 24px 30px; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
    .header p { margin: 6px 0 0 0; font-size: 13px; opacity: 0.9; }
    .content { padding: 30px; }
    .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; line-height: 1.5; }
    .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
    .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
    .form-group { margin-bottom: 18px; }
    label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px; color: #334155; }
    input[type="email"] { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; }
    input[type="email"]:focus { outline: none; border-color: #0c3ba1; box-shadow: 0 0 0 3px rgba(12, 59, 161, 0.15); }
    button { background: #0c3ba1; color: white; border: none; padding: 12px 24px; font-size: 15px; font-weight: 600; border-radius: 6px; cursor: pointer; width: 100%; transition: background 0.2s; }
    button:hover { background: #092c7d; }
    .server-info { margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b; line-height: 1.6; }
    .warning { margin-top: 20px; font-size: 12px; color: #d97706; background: #fffbeb; padding: 10px 14px; border-radius: 6px; border: 1px solid #fde68a; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h1>Summer Inspection &amp; Logistique</h1>
      <p>Server Email Delivery Diagnostic Tool</p>
    </div>
    <div class="content">
      <?php if (!empty($resultMessage)): ?>
        <div class="alert <?php echo $isSuccess ? 'alert-success' : 'alert-error'; ?>">
          <?php echo $resultMessage; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="test_email">Recipient Email Address to Test:</label>
          <input type="email" id="test_email" name="test_email" value="<?php echo htmlspecialchars($targetEmail); ?>" required placeholder="e.g. biz@summerinspectlog.com or your personal email">
        </div>
        <button type="submit">Send Test Email Now</button>
      </form>

      <div class="server-info">
        <strong>Server Telemetry:</strong><br>
        PHP Version: <?php echo phpversion(); ?><br>
        mail() function enabled: <?php echo function_exists('mail') ? 'YES' : 'NO'; ?><br>
        Server Software: <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?><br>
      </div>

      <div class="warning">
        <strong>Security Notice:</strong> Delete or rename <code>mail-test.php</code> after completing your test so unauthorized visitors cannot trigger test emails.
      </div>
    </div>
  </div>
</body>
</html>
