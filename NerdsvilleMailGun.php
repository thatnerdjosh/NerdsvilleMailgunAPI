<?php

class NerdsvilleMailGun{

    function __construct($from, $trackClicks, $trackOpens, $fileNames=null, $filetypes=null, $attachments=null, $isImage=false){
       $this->from = $from;
       $this->trackClicks = $trackClicks;
       $this->trackOpens = $trackOpens;
       $this->attachments = $attachments;
       $this->fileNames = $fileNames;
       $this->filetypes = $filetypes;
       $this->isImage = $isImage;
    }

    function sendPlainText($to, $subject, $message){
       $postfields = $this->setPostFields($to, $subject, $message);
       $this->initializeCurl($postfields);  
    }

    function sendHTMLMessage($to, $subject, $htmlMessage){
       $postfields = $this->setPostFields($to, $subject, "", $htmlMessage);
       $this->initializeCurl($postfields);
    }

    function sendPlainTextOrHTML($to, $subject, $message, $htmlMessage){
       $postfields = $this->setPostFields($to, $subject, $message, $htmlMessage);
       $this->initializeCurl($postfields);
    }

    function setPostFields($to, $subject, $message="", $htmlMessage=""){
        $attachment = $this->attachments[0];
        $postfields = array(
		    "to"=>$to,
		    "from"=>"quotes@cubic-zirconia-jewelry.com",
		    "subject"=>"Custom Quote Requested",
		    "text"=>$message,
		    "html"=>$htmlMessage=="" ? $message : $htmlMessage,
		    "o:tracking-clicks"=>$this->trackClicks ? "yes" : "no",
		    "o:tracking-opens"=>$this->trackOpens ? "yes" : "no");
        if($this->isImage){
            foreach($this->attachments as $key=>$attachment){ 
                $postfields["attachment[".$key."]"] = "@".$attachment.
                                           ';filename=' . $this->fileNames[$key].
                                           ';type='. $this->filetypes[$key];
            }
        }
        return $postfields;
   }
	   
   function initializeCurl($postfields){
	//$curlURL = "https://api.mailgun.net/v3/cubic-zirconia-jewelry.com";
	$curlURL = "https://api.mailgun.net/v{VERSION HERE}/{DOMAIN NAME HERE}";
        $curlURL .= "/messages"; //Using messages API endpoint for POST
	$headers = $isImage ? array("Content-Type:multipart/form-data") : array();
        $ch = curl_init();

	/*CURL OPTIONS*/
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, 'api:SECRET-KEY-HERE');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_URL, $curlURL);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_exec($ch);

	$info = curl_getinfo($ch);
	if(!curl_errno($ch) || $info['http_code'] != 200) {
	    print 'Your quote is now pending review from the administrator, feel free to hit the back arrow and continue browsing :)';
	} else {
	    print 'Your quote was unable to be sent, please contact the system administrator.<br/>'. curl_error($ch);
	}
	curl_close($ch);
    }
}
