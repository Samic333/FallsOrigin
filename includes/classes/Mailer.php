<?php
/**
 * Falls Origin Coffee - Production Mailer Class
 * Wrapper for PHPMailer or native mail with SMTP fallback.
 */

class Mailer {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = DB::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Mailer();
        }
        return self::$instance;
    }

    /**
     * Sends a transactional email with HTML branding.
     */
    public function send($to, $subject, $message, $order_id = null) {
        $settings = $this->getSMTPSettings();
        
        // Simple HTML Wrapper
        $htmlBody = $this->wrapInTemplate($subject, $message);
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Falls Origin Coffee <" . ADMIN_EMAIL . ">" . "\r\n";
        $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Enforce Envelope Sender for Namecheap Compliance
        $success = mail($to, $subject, $htmlBody, $headers, "-f" . ADMIN_EMAIL);

        // Log the attempt
        $this->logEmail($order_id, $to, $subject, $success ? 'success' : 'failure');

        return $success;
    }

    private function wrapInTemplate($title, $content) {
        $content = nl2br($content);
        return "
        <html>
        <body style='background-color: #050505; color: #f5f5f4; font-family: serif; padding: 40px;'>
            <div style='max-width: 600px; margin: auto; background: #0a0a0a; border: 1px solid rgba(255,255,255,0.05); border-radius: 40px; padding: 60px; box-shadow: 0 40px 100px rgba(0,0,0,0.5);'>
                <h1 style='color: #d97706; text-transform: uppercase; letter-spacing: 0.3em; font-size: 18px; text-align: center; margin-bottom: 40px;'>Falls Origin Coffee</h1>
                <h2 style='color: #ffffff; font-size: 24px; margin-bottom: 20px;'>$title</h2>
                <div style='color: rgba(255,255,255,0.6); font-size: 14px; line-height: 1.8; margin-bottom: 40px;'>
                    $content
                </div>
                <div style='border-top: 1px solid rgba(255,255,255,0.05); pt: 40px; font-size: 10px; color: rgba(255,255,255,0.2); text-transform: uppercase; letter-spacing: 0.1em; text-align: center;'>
                    This is an automated transmission from the Origin OS.
                </div>
            </div>
        </body>
        </html>";
    }

    private function logEmail($order_id, $recipient, $subject, $status) {
        try {
            $stmt = $this->db->prepare("INSERT INTO email_logs (order_id, recipient_email, subject, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $recipient, $subject, $status]);
        } catch (Exception $e) {}
    }

    private function getSMTPSettings() {
        $settings = $this->db->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
        return $settings;
    }
}
?>
