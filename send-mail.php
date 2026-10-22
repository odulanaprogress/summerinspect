<?php
/**
 * Summer Inspection & Logistique W/A LTD
 * Backend Contact & Enquiry Form Handler
 * Optimized for Hostinger PHP Hosting
 */

// Set response header to JSON
header('Content-Type: application/json; charset=UTF-8');

// Allow CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Only POST is accepted.'
    ]);
    exit();
}

// RECIPIENT EMAIL CONFIGURATION
// You can change or add multiple emails separated by comma (e.g., 'biz@summerinspectlog.com, yourname@gmail.com')
$toEmail = 'biz@summerinspectlog.com';
$companyName = 'Summer Inspection & Logistique W/A LTD';

// Read POST data (handles both FormData and raw JSON)
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

if (!$data) {
    $data = $_POST;
}

// Honeypot spam check (bots will fill this hidden field)
if (!empty($data['website_honeypot'])) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! Your message has been received.'
    ]);
    exit();
}

// Sanitize inputs
$fullName = isset($data['full_name']) ? trim(strip_tags($data['full_name'])) : '';
$email    = isset($data['email']) ? trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL)) : '';
$phone    = isset($data['phone']) ? trim(strip_tags($data['phone'])) : 'Not provided';
$service  = isset($data['service']) ? trim(strip_tags($data['service'])) : 'General Enquiry';
$message  = isset($data['message']) ? trim(htmlspecialchars($data['message'])) : '';

// Validation
if (empty($fullName) || empty($email) || empty($message)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all required fields (Name, Email, and Message).'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please provide a valid email address.'
    ]);
    exit();
}

// Email Subject
$subject = "New Inquiry from Website: " . $service . " - " . $fullName;

// HTML Email Body for SILWA Team
$emailBody = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
    .card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .header { background: #0c3ba1; color: #ffffff; padding: 20px; text-align: center; }
    .content { padding: 25px; }
    .field { margin-bottom: 15px; }
    .label { font-weight: bold; color: #0c3ba1; font-size: 13px; text-transform: uppercase; }
    .value { font-size: 15px; margin-top: 4px; color: #1e293b; }
    .message-box { background: #f8fafc; border-left: 4px solid #0c3ba1; padding: 15px; margin-top: 15px; }
    .footer { font-size: 12px; color: #64748b; text-align: center; padding: 15px; border-top: 1px solid #e2e8f0; }
  </style>
</head>
<body>
  <div class='card'>
    <div class='header'>
      <h2 style='margin:0;'>Summer Inspection &amp; Logistique W/A LTD</h2>
      <p style='margin:5px 0 0 0; font-size:14px;'>New Website Inquiry / Contact Message</p>
    </div>
    <div class='content'>
      <div class='field'>
        <div class='label'>Client Name:</div>
        <div class='value'>{$fullName}</div>
      </div>
      <div class='field'>
        <div class='label'>Email Address:</div>
        <div class='value'><a href='mailto:{$email}'>{$email}</a></div>
      </div>
      <div class='field'>
        <div class='label'>Phone Number:</div>
        <div class='value'><a href='tel:{$phone}'>{$phone}</a></div>
      </div>
      <div class='field'>
        <div class='label'>Service of Interest:</div>
        <div class='value'><strong>{$service}</strong></div>
      </div>
      <div class='field'>
        <div class='label'>Message:</div>
        <div class='message-box'>" . nl2br($message) . "</div>
      </div>
    </div>
    <div class='footer'>
      Received via https://summerinspectlog.com/ on " . date('Y-m-d H:i:s') . " (IP: " . $_SERVER['REMOTE_ADDR'] . ")
    </div>
  </div>
</body>
</html>
";

// Send to company using WordPress engine if available, or direct PHP mail with Hostinger SPF
$sent = false;
$fromSender = 'wordpress@summerinspectlog.com';

$wpPath = file_exists(__DIR__ . '/wp-load.php') ? __DIR__ . '/wp-load.php' : (file_exists(__DIR__ . '/wp_backup/wp-load.php') ? __DIR__ . '/wp_backup/wp-load.php' : null);

if ($wpPath) {
    // If WordPress is present (either in root or wp_backup), use proven wp_mail()
    require_once $wpPath;
    $wpHeaders = [
        'Content-Type: text/html; charset=UTF-8',
        'From: SILWA Web Enquiries <' . $fromSender . '>',
        'Reply-To: ' . $fullName . ' <' . $email . '>'
    ];
    $sent = wp_mail($toEmail, $subject, $emailBody, $wpHeaders);
} else {
    // Standalone Hostinger PHP mail matching Hostinger's SPF record
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: SILWA Web Enquiries <' . $fromSender . '>';
    $headers[] = 'Reply-To: ' . $fullName . ' <' . $email . '>';
    $headers[] = 'Date: ' . date('r');
    $headers[] = 'Message-ID: <' . md5(uniqid(time())) . '@summerinspectlog.com>';
    $headers[] = 'X-Mailer: WordPress/Hostinger';

    $sent = @mail($toEmail, $subject, $emailBody, implode("\r\n", $headers), "-f " . $fromSender);
}

if ($sent) {
    // Send auto-acknowledgement email to the client
    $autoSubject = "We Received Your Inquiry - Summer Inspection & Logistique W/A LTD";
    $autoBody = "
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset='utf-8'>
      <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .header { background: #0c3ba1; color: #ffffff; padding: 20px; text-align: center; }
        .content { padding: 25px; line-height: 1.7; }
        .footer { font-size: 12px; color: #64748b; text-align: center; padding: 15px; border-top: 1px solid #e2e8f0; }
      </style>
    </head>
    <body>
      <div class='card'>
        <div class='header'>
          <h2 style='margin:0;'>Summer Inspection &amp; Logistique W/A LTD</h2>
        </div>
        <div class='content'>
          <p>Dear <strong>{$fullName}</strong>,</p>
          <p>Thank you for contacting <strong>Summer Inspection &amp; Logistique W/A LTD</strong> regarding <em>{$service}</em>.</p>
          <p>We have safely received your inquiry. Our operations superintendence team is reviewing your message and will reach back to you shortly via this email or your phone number (<strong>{$phone}</strong>).</p>
          <p>For urgent vessel attendance or immediate inquiries, you can also reach our dispatch directly at <strong>+234 7045515750</strong> or <strong>biz@summerinspectlog.com</strong>.</p>
          <p style='margin-top:25px;'>Warm regards,<br><strong>Operations &amp; Inspection Team</strong><br>Summer Inspection &amp; Logistique W/A LTD<br><a href='https://summerinspectlog.com/'>www.summerinspectlog.com</a></p>
        </div>
        <div class='footer'>
          Copyright &copy; " . date('Y') . " Summer Inspection &amp; Logistique W/A LTD. All rights reserved.
        </div>
      </div>
    </body>
    </html>
    ";
    
    if (function_exists('wp_mail')) {
        $autoWpHeaders = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Summer Inspection & Logistique <' . $fromSender . '>',
            'Reply-To: biz@summerinspectlog.com'
        ];
        @wp_mail($email, $autoSubject, $autoBody, $autoWpHeaders);
    } else {
        $autoHeaders = [];
        $autoHeaders[] = 'MIME-Version: 1.0';
        $autoHeaders[] = 'Content-type: text/html; charset=UTF-8';
        $autoHeaders[] = 'From: Summer Inspection & Logistique <' . $fromSender . '>';
        $autoHeaders[] = 'Reply-To: biz@summerinspectlog.com';
        $autoHeaders[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . md5(uniqid('auto' . time())) . '@summerinspectlog.com>';
        $autoHeaders[] = 'X-Mailer: WordPress/Hostinger';

        @mail($email, $autoSubject, $autoBody, implode("\r\n", $autoHeaders), "-f " . $fromSender);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! Your message has been sent successfully to the Summer Inspection team. We will contact you shortly.'
    ]);
} else {
    // If Hostinger local mail() is not configured or fails, we return error with mailto fallback
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to send message automatically. Please contact us directly at biz@summerinspectlog.com or call +234 7045515750.'
    ]);
}
