<?php
$pathToPHPMailer = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'PHPMailer.php';
require $pathToPHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
function getConfiguredMail () {
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;

    $mail->Username = "prostoblog.local@gmail.com";
    $mail->Password = "1Artaxerx2##";
    $mail->setFrom('prostoblog.local@gmail.com', 'Prosto Blog');
    $mail->addReplyTo('prostoblog.local@gmail.com', 'Prosto Blog');
    
    return $mail;
}



// below you can see the full instructions to use PHPMailer
/* *
 * This example shows settings to use when sending via Google's Gmail servers.
 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
 * example to see how to use XOAUTH2.
 * The IMAP section shows how to save this message to the 'Sent Mail' folder using IMAP commands.
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
// $mail->Username = "prostoblog.local@gmail.com";

//Password to use for SMTP authentication
// $mail->Password = "1Artaxerx2##";

//Set who the message is to be sent from
// $mail->setFrom('prostoblog.local@gmail.com', 'Prosto Blog');

// //Set an alternative reply-to address
// $mail->addReplyTo('prostoblog.local@gmail.com', 'Prosto Blog');

//Set who the message is to be sent to
// $mail->addAddress('drotov.mihailo@gmail.com', 'Prosto Blog');

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