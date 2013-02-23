<?php
/**
 * author: cty@20121014
 *   func: email send class
 *   
 *   call: (new CMail())->subject('test')->body('body')->to('ctymtce@126.com')->send();
 *
 *
*/

class CMail {

    private $sender_mail = 'fortuan800@126.com';
    private $sender_user = 'fortuan800@126.com';
    private $sender_name = 'sender';
    private $sender_pswd = '12345678';

    private $server_smtp = 'smtp.126.com';

    private $subject     = null;
    private $toArr       = array();
    private $body        = '';
    private $ishtml      = true;

    /*
    * desc: set subject of mail
    *
    *@subject --- string 
    *return: this object
    */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    /*
    * desc: add receiver mail
    *@tos --- string mail1,mail2
    */
    public function addtos($tos)
    {
        $this->toArr = array_merge($this->toArr, explode(',', trim($tos,',')));
        return $this;
    }
    public function to($to)
    {
        return $this->addtos($to);
    }
    public function ishtml($is=true)
    {
        $this->ishtml = $is;
        return $this;
    }
    public function body($body='')
    {
        $this->body = $body;
        return $this;
    }
    public function send()
    {
        require_once dirname(__FILE__).'/mail/class.phpmailer.php';
        //发送方
        $user    = $this->sender_user; // isset($user)?$user:'fortuan800@126.com';
        $pswd    = $this->sender_pswd; // isset($pswd)?$pswd:'12345678';
        $name    = $this->sender_name; // isset($name)?$name:$user;
        //接收方
        $subject = $this->subject; // isset($subject)?$subject:'subject';
        $body    = $this->body; // isset($body)?$body:'';
        // $tos     = $this->tos; // isset($to)?$to:$user;
        $body    = preg_replace('/\\\\/','', $body); //Strip backslashes
        $mail    = new PHPMailer(true); //New instance, with exceptions enabled
        
        $mail->CharSet    ="UTF-8";
        $mail->IsSMTP();                           // tell the class to use SMTP
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->Port       = 25;                    // set the SMTP server port
        $mail->Host       = $this->server_smtp;        // SMTP server
        $mail->Username   = $user;                 // SMTP server username
        $mail->Password   = $pswd;                 // SMTP server password
        
        $mail->From       = $user;
        $mail->FromName   = $name;
        // $to = "ctymtce@gmail.com";
        foreach ($this->toArr as $key => $to) {
            $mail->AddAddress($to);
        }
        // $mail->AddAddress($tos);
        $mail->Subject  = $subject;
        // $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->WordWrap   = 80;       // set word wrap
        $mail->MsgHTML($body);
        $mail->IsHTML($this->ishtml); // send as HTML
        
        if(!$mail->Send()) {
          echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
          echo "Message sent!";
        }
        // return $mail->Send();
    }

};
?>
