<?php
/*
|--------------------------------------------------------------------------
| Email Preference Settings
|--------------------------------------------------------------------------
|
| There are 17 different preferences available to tailor how your email messages are sent.
|
*/

//Codeignitor Default Settings
//options: None
$config['useragent'] = 'FreshBooks';
//options: mail, sendmail or smtp
$config['protocol'] = 'mail';
//options: the serverpath to sendmail
$config['mailpath'] = '/usr/sbin/sendmail';
//options: SMTP Server Address
$config['smtp_host'] = '';
//options: SMTP Username
$config['smtp_user'] = '';
//options: SMTP Password
$config['smtp_pass'] = '';
//options: SMTP Port
$config['smtp_port'] = 25;
//options: SMTP Timeout (in seconds)
$config['smtp_timeout'] = 5;
//options: TRUE or FALSE
$config['wordwrap'] = TRUE;
//options: Character count to wrap at
$config['wrapchars'] = 76;
//options: text or html
$config['mailtype'] = 'text';
//options: Character set (utf-8, iso-8859-1, etc.)
$config['charset'] = 'utf-8';
//options: TRUE or FALSE Whether to validate the email address
$config['validate'] = FALSE;
//options: 1, 2, 3, 4, 5	Email Priority. 1 = highest. 5 = lowest. 3 = normal.
$config['priority'] = 3;
//options: "\r\n" or "\n" or "\r"  Newline character. (Use "\r\n" to comply with RFC 822).
$config['crlf'] = '\n';
//options: TRUE or FALSE Enable BCC Batch Mode.
$config['bcc_batch_mode'] = FALSE;
//options: Number of emails in each BCC batch.
$config['bcc_batch_size'] = 200;

/* End of file email.php */
/* Location: ./application/config/email.php */