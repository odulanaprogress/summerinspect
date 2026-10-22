<?php
/**
 * Summer Inspection & Logistique W/A LTD
 * Direct One-Click Mail Tester (Zero Configuration / No Passwords Needed)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$targetEmail = isset($_POST['test_email']) ? trim($_POST['test_email']) : 'biz@summerinspectlog.com';
$resultMessage = '';
$isSuccess = null;
$methodUsed = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($targetEmail)) {
    if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
        $resultMessage = "Invalid email format: " . htmlspecialchars($targetEmail);
        $isSuccess = false;
    } else {
        $subject = "New Inquiry Test: Summer Inspection & Logistique (" . date('d M Y, H:i') . ")";
        
        // Professional HTML body that passes spam filters cleanly
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='utf-8'>
          <style>
            body { font-family: Arial, sans-serif; background: #f8fafc; color: #1e293b; padding: 20px; }
            .box { max-width: 580px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
            .head { background: #0c3ba1; color: white; padding: 18px 24px; }
            .content { padding: 24px; font-size: 14px; line-height: 1.6; }
            .highlight { background: #eff6ff; padding: 12px 16px; border-left: 4px solid #0c3ba1; margin: 15px 0; border-radius: 4px; }
            .foot { font-size: 12px; color: #64748b; padding: 12px 24px; border-top: 1px solid #f1f5f9; text-align: center; }
          </style>
        </head>
        <body>
          <div class='box'>
            <div class='head'>
              <h2 style='margin:0; font-size:18px;'>Summer Inspection &amp; Logistique W/A LTD</h2>
              <p style='margin:4px 0 0 0; font-size:12px; opacity:0.9;'>Website Communication Test</p>
            </div>
            <div class='content'>
              <p>Hello Team,</p>
              <div class='highlight'>
                <strong>Status: SUCCESSFUL DELIVERY</strong><br>
                This confirms that your website's form delivery is working properly from your Hostinger server to your Zoho inbox.
              </div>
              <p><strong>Test Details:</strong></p>
              <ul>
                <li><strong>Recipient:</strong> " . htmlspecialchars($targetEmail) . "</li>
                <li><strong>Sender Identity:</strong> SILWA Web System (Hostinger SPF Verified)</li>
                <li><strong>Timestamp:</strong> " . date('r') . "</li>
              </ul>
              <p>Your website contact and quote requests are ready to receive client inquiries.</p>
            </div>
            <div class='foot'>
              &copy; " . date('Y') . " Summer Inspection &amp; Logistique W/A LTD &bull; https://summerinspectlog.com/
            </div>
          </div>
        </body>
        </html>";

        // Check if WordPress wp_mail() is available in this folder
        if (file_exists(__DIR__ . '/wp-load.php')) {
            require_once __DIR__ . '/wp-load.php';
            $wpHeaders = [
                'Content-Type: text/html; charset=UTF-8',
                'From: SILWA Web Portal <wordpress@summerinspectlog.com>',
                'Reply-To: biz@summerinspectlog.com'
            ];
            $sent = wp_mail($targetEmail, $subject, $body, $wpHeaders);
            $methodUsed = 'WordPress Native wp_mail() Engine';
        } else {
            // Direct native PHP mail() using WordPress-compatible SPF sender
            $fromEmail = 'wordpress@summerinspectlog.com';
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            $headers[] = 'From: SILWA Web Portal <' . $fromEmail . '>';
            $headers[] = 'Reply-To: biz@summerinspectlog.com';
            $headers[] = 'Date: ' . date('r');
            $headers[] = 'Message-ID: <' . md5(uniqid(time())) . '@summerinspectlog.com>';
            $headers[] = 'X-Mailer: PHP/' . phpversion();

            $sent = @mail($targetEmail, $subject, $body, implode("\r\n", $headers), "-f " . $fromEmail);
            $methodUsed = 'Hostinger PHP Mail Engine (with SPF sender)';
        }

        if ($sent) {
            $isSuccess = true;
            $resultMessage = "✅ <strong>Message Dispatched Successfully!</strong> (Via {$methodUsed})<br><br>" .
                             "Sent to: <strong>" . htmlspecialchars($targetEmail) . "</strong>.<br>" .
                             "Please check your Zoho inbox (and Spam folder) now.";
        } else {
            $isSuccess = false;
            $lastErr = error_get_last();
            $resultMessage = "❌ Server failed to dispatch message. " . ($lastErr ? htmlspecialchars($lastErr['message']) : '');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SILWA - One-Click Direct Mail Tester</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0b1528; color: #e2e8f0; padding: 40px 20px; margin: 0; }
    .card { max-width: 580px; margin: 0 auto; background: #132238; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow: hidden; border: 1px solid #1e3352; }
    .header { background: #0c3ba1; color: white; padding: 24px 30px; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
    .header p { margin: 6px 0 0 0; font-size: 13px; opacity: 0.9; }
    .content { padding: 30px; }
    .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; line-height: 1.5; }
    .alert-success { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; }
    .alert-error { background: #4c1d24; border: 1px solid #e11d48; color: #fecdd3; }
    .form-group { margin-bottom: 18px; }
    label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px; color: #94a3b8; }
    input[type="email"] { width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0a1424; border: 1px solid #243b5e; border-radius: 6px; font-size: 14px; color: white; }
    input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
    button { background: #2563eb; color: white; border: none; padding: 13px 24px; font-size: 15px; font-weight: 600; border-radius: 6px; cursor: pointer; width: 100%; transition: background 0.2s; margin-top: 10px; }
    button:hover { background: #1d4ed8; }
    .note { margin-top: 20px; font-size: 12px; color: #94a3b8; line-height: 1.6; background: #0a1424; padding: 12px 16px; border-radius: 6px; border-left: 3px solid #3b82f6; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h1>Summer Inspection &amp; Logistique W/A LTD</h1>
      <p>Direct One-Click Mail Tester (No Logins / No Passwords)</p>
    </div>
    <div class="content">
      <?php if (!empty($resultMessage)): ?>
        <div class="alert <?php echo $isSuccess ? 'alert-success' : 'alert-error'; ?>">
          <?php echo $resultMessage; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Recipient Email Address:</label>
          <input type="email" name="test_email" value="<?php echo htmlspecialchars($targetEmail); ?>" required placeholder="biz@summerinspectlog.com">
        </div>

        <button type="submit">⚡ Send Test Message Directly</button>
      </form>

      <div class="note">
        ✨ <strong>Zero-Setup Mode:</strong> Uses the exact same sender identity and Hostinger SPF verification that WordPress uses. No Zoho login or passwords needed!
      </div>
    </div>
  </div>
</body>
</html>
