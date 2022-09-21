<?php

Class sendmail{

	public $smtp_host;
	public $smtp_port = 25;
	public $smtp_user;
	public $smtp_password;
	public $from_name;
	public $SendFromMail;
	public $mail_to;
	public $subject;
	public $message;
	public $headers = '';
	public $ContentType = 'html';
	public $charset = 'windows-1251';
	public $smtp_debug = true;
	public $socket;
	public $error;
	public $SendMailVia  = 'smtp';
	
  public function __construct()
	{
			if($this->SendFromMail == ''){
			   $this->SendFromMail = $this->smtp_user;
			}
	}
	
	public function Send($mail_to = '', $subject = '', $message = '')
	{
		if($mail_to!=''){$this->mail_to = stripslashes($mail_to);}
			if($subject!=''){$this->subject = stripslashes($subject);}
			if($message!=''){$this->message = $message;}
			$meilsArr = array_filter($this->GetMailAndNameArr());
			if(trim($this->mail_to)==''){$this->error = 'Enter the recipient address'; }
			if($meilsArr == array()){$this->error = 'Please enter a valid recipient address'; }
			foreach ($meilsArr as $val)
			{
				$validEmail = $this->validEmail($val[2]);
				if($validEmail)
				{
				  if($this->SendMailVia=='smtp'){
					  return $this->SMTPsend($mail_to = $val[2], $name_to = $val[1]);
					}
					else{
					  return $this->MAILsend($mail_to = $val[2], $name_to = $val[1]);
					}	
				}
			}
	}
	
	public function MAILsend($mail_to, $name_to)
	{
	if($this->ContentType=="text"){
		$header="Content-Type: text/plain; charset=".$this->charset."";
	  }
		else{
		$header="Return-Path: ".$this->smtp_user."\n".
	  "Reply-To: ".$this->SendFromMail."\n".
	  "From: ".$this->from_name." <".$this->SendFromMail.">\n".
	  "Subject: ".$this->subject."\n".
		"Content-Type: text/html; charset=".$this->charset."\n";
	  }
	  if(mail("$name_to <$mail_to>",$this->subject,$this->message,$header)){
		 return true;
		}else{
		 return false;
		}
  }
	
	public function SMTPsend($mail_to, $name_to)
	{
			$SEND =   "Date: ".date("D, d M Y H:i:s") . "\r\n";
			$SEND .=   'Subject: =?'.$this->charset.'?B?'.base64_encode($this->subject)."=?=\r\n";
			if ($this->headers!=''){ $SEND .= $this->headers."\r\n\r\n"; }
	  else
	  {
		 $SEND .= "Reply-To: ".$this->SendFromMail."\r\n";
		 $SEND .= "MIME-Version: 1.0\r\n";
		 $SEND .= "Content-Type: text/".$this->ContentType."; charset=\"".$this->charset."\"\r\n";
		 $SEND .= "Content-Transfer-Encoding: 8bit\r\n";
		 $SEND .= "From: \"".$this->from_name."\" <".$this->SendFromMail.">\r\n";
		 $SEND .= "To: $name_to <$mail_to>\r\n";
		 $SEND .= "X-Priority: 3\r\n\r\n";
	  }
	  $SEND .=  $this->message."\r\n";
		
				socket = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
		  if(!$socket) {
		      if($this->smtp_debug) $this->error = $errno." - ".$errstr;
		      return false;
		  }
				
				if (!$this->server_parse($socket, "220", __LINE__)){ return false; }
				
			fputs($socket, "HELO ".$this->smtp_host. "\r\n");
			if (!$this->server_parse($socket, "250", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Can not send HELO!</p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, "AUTH LOGIN\r\n");
			if (!$this->server_parse($socket, "334", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Can not find an answer to a request authorization.</p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, base64_encode($this->smtp_user) . "\r\n");
			if (!$this->server_parse($socket, "334", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Login authorization was not accepted by server!</p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, base64_encode($this->smtp_password) . "\r\n");
			if (!$this->server_parse($socket, "235", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>No password was not accepted as a true server! Authorization Error!</p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, "MAIL FROM: <".$this->smtp_user.">\r\n");
			if (!$this->server_parse($socket, "250", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Unable to send command MAIL FROM: </p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, "RCPT TO: <" . $mail_to . ">\r\n");
			if (!$this->server_parse($socket, "250", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Unable to send command RCPT TO: </p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, "DATA\r\n");
			if (!$this->server_parse($socket, "354", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Unable to send command DATA</p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, $SEND."\r\n.\r\n");
			if (!$this->server_parse($socket, "250", __LINE__)) {
			   if ($this->smtp_debug) $this->error = '<p>Unable to send the message body. The letter was sent!</p>';
			   fclose($socket);
			   return false;
			}
			fputs($socket, "QUIT\r\n");
			fclose($socket);
			return TRUE;
	}
	
	
	
	private function GetMailAndNameArr(){
		$mailingArr = array();
			$tos = preg_split("/;|,/",$this->mail_to);
			$pregcode = '/(.*?)<(.*?)>/i';
			foreach($tos as $to)
			{
			  if(preg_match('/(.*?)<(.*?)>/i',$to,$matches))
				{
				  unset($matches[0]);	
				  $matches[1] = trim(str_replace('"','',$matches[1]));
				  $matches[2] = trim($matches[2]);
				  $mailingArr[] =$matches; 
				}
				elseif(preg_match('/\b([A-Z0-9._%-]+)@([A-Z0-9.-]+\.[A-Z]{2,4})\b/i',$to,$matches2))
				{
					 unset($matches[0]);	
					 $matches[1] = trim(str_replace('"','',$matches2[1]));
					 $matches[2] = trim($matches2[0]);
					 $mailingArr[] =$matches;
				}
			}
			return $mailingArr;
	}
	
	private function server_parse($socket, $response, $line = __LINE__) {
	while (substr($server_response, 3, 1) != ' ') {
		  if (!($server_response = fgets($socket, 256))) {
			   if ($this->smtp_debug) $this->error = "<p>$line Problems sending mail! $response</p>";
			   return false;
		  }
	}
	if (!(substr($server_response, 0, 3) == $response)) {
		   if ($this->smtp_debug) $this->error = "<p>$line Problems sending mail! $response</p>";
		   return false;
		}
	return true;
  }

  function validEmail($email)
  {
	$isValid = true;
	$atIndex = strrpos($email, "@");
	  $msg = '';
	if (is_bool($atIndex) && !$atIndex)
	{
	  $isValid = false;
	}
	else
	{
	  $domain = substr($email, $atIndex+1);
	  $local = substr($email, 0, $atIndex);
	  $localLen = strlen($local);
	  $domainLen = strlen($domain);
	  if ($localLen < 1 || $localLen > 64){
				 $msg = 'local part length exceeded';
		 $isValid = false;
	  }
	  else if ($domainLen < 1 || $domainLen > 255){
				 $msg = ' domain part length exceeded ';
		 $isValid = false;
	  }
	  else if ($local[0] == '.' || $local[$localLen-1] == '.'){
				 $msg = ' local part starts or ends with .';
		 $isValid = false;
	  }
	  else if (preg_match('/\\.\\./', $local)){
				 $msg = 'local part has two consecutive dots';
		 $isValid = false;
	  }
	  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
				 $msg = 'character not valid in domain part';
		 $isValid = false;
	  }
	  else if (preg_match('/\\.\\./', $domain)){
				 $msg = '  domain part has two consecutive dots';
		 $isValid = false;
	  }
	  else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))){
				 $msg = '  character not valid in local part unless local part is quoted';
		 if (!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))){
			$isValid = false;
		 }
	  }
	  if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))){
				 $msg = '  domain <b>'.$domain.'</b> not found in DNS';
		 $isValid = false;
	  }
	}
	  $this->error = $msg;
	return $isValid;
  }

}
?>