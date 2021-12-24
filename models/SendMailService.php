<?php
use PHPMailer\PHPMailer\PHPMailer;
$pathToPHPMailer = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'PHPMailer.php';
require $pathToPHPMailer;

class SendMailService {
    private $mail;
    private static $_instance;
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self(new PHPMailer());
        }
        return self::$_instance;
    }
    private function __construct(PHPMailer $phpMailer) {
        $this->mail = $phpMailer;
    }
    public function getConfiguredMail() {
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 0;
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->Port = 587;
        $this->mail->SMTPSecure = 'tls';
        $this->mail->SMTPAuth = true;

        $data = json_decode(file_get_contents('data.json'), true);
        $this->mail->Username = $data['email'];
        $this->mail->Password = $data['password'];
        $this->mail->setFrom($data['email'], 'Prosto Blog');
        $this->mail->addReplyTo($data['email'], 'Prosto Blog');
        return $this->mail;
    }
    public function sendMail($toEmail, $title, $message) {
        $mail = $this->getConfiguredMail();
        $mail->addAddress($toEmail, 'Prosto Blog');
        $mail->Subject = $title;
        $mail->msgHTML($message);
        //$mail->AltBody = 'This is a plain-text message body';
        //$mail->addAttachment('images/phpmailer_mini.png');
        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }
}


// below you can see the full instructions to use PHPMailer
/* *
 * This example shows settings to use when sending via Google's Gmail servers.
 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
 * example to see how to use XOAUTH2.
 * The IMAP section shows how to save this message to the 'Sent Mail' folder USING IMAP commands.
 */

//Import PHPMailer classes into the global namespace
// use PHPMailer\PHPMailer\PHPMailer;

//Create a new PHPMailer instance
// $mail = new PHPMailer;

//Tell PHPMailer to use SMTP
// $mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
//$mail->SMTPDebug = 0;

//Set the hostname of the mail server
//$mail->Host = 'smtp.gmail.com';
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
// $mail->Port = 587;

//Set the encryption system to use - ssl (deprecated) or tls
// $mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
// $mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
// $mail->Username = "**************";

//Password to use for SMTP authentication
// $mail->Password = "*********";

//Set who the message is to be sent from
// $mail->setFrom('*******************', 'Prosto Blog');

// //Set an alternative reply-to address
// $mail->addReplyTo('*****************', 'Prosto Blog');

//Set who the message is to be sent to
// $mail->addAddress('*****************', 'Prosto Blog');

//Set the subject line
// $mail->Subject = 'Darova';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
// $mail->msgHTML('<a href="http://prostoblog.local">Ssylka na sajt</a>');

//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';

//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
// if (!$mail->send()) {
//     echo "Mailer Error: " . $mail->ErrorInfo;
// } else {
//     echo "Message sent!";
//Section 2: IMAP
//Uncomment these to save your message in the 'Sent Mail' folder.  I delete this
//     #if (save_mail($mail)) {
//     #    echo "Message saved!";
//     #}