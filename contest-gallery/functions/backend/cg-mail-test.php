<?php
if(!defined('ABSPATH')){exit;}

// Request-local diagnostics. Never enable SMTP debug output or persist credentials.
if(!class_exists('CG_Mail_Test')){
	class CG_Mail_Test {
		private $id;
		private $subject;
		private $mailer;
		private $secrets = array();
		private $details = array();
		private $errors = array();
		private $shortCircuit = false;
		private $succeeded = false;
		private $failed = false;

		public function clean($value){
			if(!is_scalar($value)){return '';}
			$value = (string)$value;
			foreach($this->secrets as $secret){
				if($secret!==''){$value = str_replace(array($secret,base64_encode($secret),rawurlencode($secret)),'[redacted]',$value);}
			}
			$value = preg_replace('~(://)[^\s/@]+:[^\s/@]+@~','$1[redacted]@',$value);
			$value = preg_replace('/\b(password|passwd|username|access[_ -]?token|refresh[_ -]?token|api[_ -]?key|secret|authorization)\b\s*[=:]\s*("[^"]*"|\'[^\']*\'|[^\s,;]+)/i','$1=[redacted]',$value);
			$value = preg_replace('/\b(Bearer|AUTH)\s+[^\r\n]+|\b334[ -][A-Za-z0-9+\/=]+/i','[authentication data redacted]',$value);
			$value = preg_replace('/\b[A-Za-z0-9+\/_=-]{48,}\b/','[long token redacted]',$value);
			return substr(preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/','',wp_strip_all_tags($value)),0,2000);
		}

		private function matches($mail){
			return is_array($mail) && isset($mail['subject']) && strpos($mail['subject'],$this->id)!==false;
		}

		public function preMail($result,$mail){
			if($this->matches($mail)){$this->shortCircuit = $result!==null;}
			return $result;
		}

		public function observeMailer($mailer){
			if(strpos((string)$mailer->Subject,$this->id)===false){return;}
			$this->mailer = $mailer;
			foreach(array('Username','Password') as $key){
				if(isset($mailer->$key) && is_string($mailer->$key) && $mailer->$key!==''){$this->secrets[] = $mailer->$key;}
			}
			$this->details['Transport'] = $this->clean($mailer->Mailer);
			$this->details['Observed From'] = $this->clean($mailer->From);
			$this->details['Observed From name'] = $this->clean($mailer->FromName);
			foreach(array('getReplyToAddresses'=>'Observed Reply-To','getToAddresses'=>'Observed recipient') as $method=>$label){
				$addresses = array();
				if(method_exists($mailer,$method)){
					foreach($mailer->$method() as $address){if(isset($address[0])){$addresses[] = $this->clean($address[0]);}}
				}
				$this->details[$label] = $addresses ? implode(', ',$addresses) : 'Not available / not set';
			}
			if($mailer->Mailer==='smtp'){
				$this->details['SMTP host'] = $this->clean($mailer->Host);
				$this->details['SMTP port'] = (int)$mailer->Port;
				$this->details['SMTP encryption setting'] = $mailer->SMTPSecure ? $this->clean($mailer->SMTPSecure) : 'Not explicitly configured';
				$this->details['Automatic STARTTLS setting'] = !empty($mailer->SMTPAutoTLS) ? 'Enabled' : 'Disabled / unavailable';
				$this->details['SMTP authentication'] = $mailer->SMTPAuth ? 'Enabled' : 'Disabled';
			}
		}

		private function transportResult(){
			$mailer = $this->mailer;
			// The global mailer can be reused by nested mail calls. Never report their state.
			if(!$mailer || strpos((string)$mailer->Subject,$this->id)===false){return;}
			$this->observeMailer($mailer);
			if($this->succeeded && method_exists($mailer,'getLastMessageID')){
				$this->details['Message-ID'] = $this->clean(trim($mailer->getLastMessageID(),'<>'));
			}
			if($mailer->ErrorInfo){$this->errors[] = $this->clean($mailer->ErrorInfo);}
			if($mailer->Mailer==='smtp' && method_exists($mailer,'getSMTPInstance')){
				$smtp = $mailer->getSMTPInstance();
				if(method_exists($smtp,'getLastReply')){$this->details['Last SMTP reply (not proof of delivery)'] = $this->clean($smtp->getLastReply());}
				if($this->succeeded && method_exists($smtp,'getLastTransactionID') && $smtp->getLastTransactionID()){
					$this->details['SMTP transaction ID'] = $this->clean($smtp->getLastTransactionID());
				}
				if(method_exists($smtp,'getError')){
					$error = $smtp->getError();
					foreach(array('error','detail','smtp_code','smtp_code_ex') as $key){
						if(!empty($error[$key])){$this->details['SMTP '.$key] = $this->clean($error[$key]);}
					}
				}
			}
		}

		public function mailSucceeded($mail){
			if(!$this->matches($mail)){return;}
			$this->succeeded = true;
			$this->transportResult();
		}

		public function mailFailed($error){
			if(!is_wp_error($error) || !$this->matches($error->get_error_data())){return;}
			$this->failed = true;
			foreach($error->get_error_codes() as $code){
				foreach($error->get_error_messages($code) as $message){$this->errors[] = $this->clean($code.': '.$message);}
			}
			$data = $error->get_error_data();
			if(isset($data['phpmailer_exception_code'])){$this->details['PHPMailer exception code'] = $this->clean($data['phpmailer_exception_code']);}
			$this->transportResult();
		}

		public function send($addresses){
			global $wp_version;
			$this->id = wp_generate_uuid4();
			$this->subject = 'Contest Gallery test email ['.$this->id.']';
			$time = gmdate('Y-m-d H:i:s').' UTC';
			$this->details = array('Test ID'=>$this->id,'Time'=>$time,'Website'=>home_url('/'),'Subject'=>$this->subject,'WordPress'=>$wp_version,'PHP'=>PHP_VERSION,
				'Requested From'=>$addresses['from']!=='' ? $addresses['from'] : 'Empty. Uses Reply-To or WordPress default.',
				'Requested Reply-To'=>$addresses['reply']!=='' ? $addresses['reply'] : 'Empty. Uses From when provided.',
				'Requested recipient'=>$addresses['to']);
			$headers = cg_add_mail_from_reply_headers(array('Content-Type: text/plain; charset=UTF-8','X-CG-Mail-Test: '.$this->id),get_bloginfo('name'),$addresses['from'],$addresses['reply']);
			$body = "This is a neutral Contest Gallery test email.\n\nWebsite: ".home_url('/')."\nTest ID: ".$this->id."\nTime: ".$time."\n\nIf you can read this message, it reached this mailbox. No registration, PIN or other action was created.\n";
			add_filter('pre_wp_mail',array($this,'preMail'),PHP_INT_MAX,2);
			add_action('phpmailer_init',array($this,'observeMailer'),PHP_INT_MAX);
			add_action('wp_mail_succeeded',array($this,'mailSucceeded'),-PHP_INT_MAX);
			add_action('wp_mail_failed',array($this,'mailFailed'),-PHP_INT_MAX);
			$start = microtime(true);
			$result = null;
			$bufferLevel = ob_get_level();
			ob_start(); // A third-party SMTP debug echo must not corrupt the JSON or expose AUTH data.
			try{
				$result = wp_mail($addresses['to'],$this->subject,$body,$headers['headers']);
			}catch(Exception $error){
				$this->failed = true;
				$this->errors[] = $this->clean($error->getMessage());
			}catch(Throwable $error){
				$this->failed = true;
				$this->errors[] = $this->clean($error->getMessage());
			}finally{
				remove_filter('pre_wp_mail',array($this,'preMail'),PHP_INT_MAX);
				remove_action('phpmailer_init',array($this,'observeMailer'),PHP_INT_MAX);
				remove_action('wp_mail_succeeded',array($this,'mailSucceeded'),-PHP_INT_MAX);
				remove_action('wp_mail_failed',array($this,'mailFailed'),-PHP_INT_MAX);
				while(ob_get_level()>$bufferLevel){ob_end_clean();}
			}
			// Older WordPress versions have no success hook. Only inspect an observed test mailer.
			if(!$this->succeeded && !$this->failed && !$this->shortCircuit){$this->transportResult();}
			$this->details['Duration'] = round(microtime(true)-$start,2).' seconds';
			$this->details['wp_mail() return'] = $result===true ? 'true' : ($result===false ? 'false' : 'No boolean result');
			$this->details['wp_mail_succeeded observed'] = $this->succeeded ? 'Yes' : 'No';
			$this->details['wp_mail_failed observed'] = $this->failed ? 'Yes / exception' : 'No';
			$this->details['Mail plugin short-circuit observed'] = $this->shortCircuit ? 'Yes' : 'No';
			$status = $this->failed || $result===false ? 'failed' : ($result===true ? 'accepted' : 'unknown');
			$titles = array('accepted'=>'Processed without an immediate sending error','failed'=>'Test email could not be sent','unknown'=>'Sending result is unknown');
			$summary = $this->shortCircuit || !$this->mailer ? 'A mail plugin or custom mail function handled this request. Transport details may be unavailable. Check its delivery logs.' : 'The details below describe the mailer observed during this test. A mail plugin may override the requested values.';
			if(!isset($this->details['Transport'])){$this->details['Transport'] = 'Unknown. No PHPMailer instance observed for this test.';}
			$errors = array_unique($this->errors);
			if($errors){$this->details['Errors'] = implode("\n",$errors);}
			$tips = array();
			$errorText = strtolower(implode(' ',$errors).' '.(isset($this->details['SMTP detail']) ? $this->details['SMTP detail'] : ''));
			if(strpos($errorText,'auth')!==false){$tips[] = 'Check the SMTP account, credentials and sender permissions in your mail plugin. Some providers require an app password or OAuth.';}
			if(preg_match('/tls|ssl|certificate/',$errorText)){$tips[] = 'Check the SMTP host, port, encryption and server certificate. Do not disable certificate verification.';}
			if(preg_match('/connect|timeout|timed out|refused/',$errorText)){$tips[] = 'Ask your host to check the SMTP hostname, port, firewall and outbound connection limits.';}
			if($status==='failed' && !$tips){$tips[] = 'Check the error details with your mail plugin or hosting provider. Verify the sender address and outgoing mail configuration.';}
			if($this->details['Transport']==='mail' || $this->details['Transport']==='sendmail'){$tips[] = 'This test uses the server mail service. Your host can check its queue and logs. An authenticated SMTP setup can provide clearer delivery diagnostics.';}
			$tips[] = 'If the message is missing, check spam and the mail provider or plugin logs using the test ID. Ask the provider to verify sender authorization and SPF, DKIM and DMARC. This test does not check DNS.';
			if($status==='unknown'){$tips[] = 'Check the mailbox before sending again. An unknown result does not mean that no message was sent.';}
			foreach($this->details as $key=>$value){$this->details[$key] = $this->clean($value);}
			return array('status'=>$status,'title'=>$titles[$status],'summary'=>$summary,'details'=>$this->details,'tips'=>$tips);
		}
	}
}

add_action('wp_ajax_post_cg_send_test_mail','post_cg_send_test_mail');
if(!function_exists('post_cg_send_test_mail')){
	function post_cg_send_test_mail(){
		if(!current_user_can('manage_options')){wp_send_json_error(array('message'=>'Only administrators can send test emails.'),403);}
		$nonce = isset($_POST['nonce']) && is_string($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
		if(!wp_verify_nonce($nonce,'cg_send_test_mail')){wp_send_json_error(array('message'=>'The security token has expired. Reload this page before testing again.'),403);}
		$addresses = array();
		$errors = array();
		foreach(array('from'=>'From','reply'=>'Reply-To','to'=>'Recipient') as $field=>$label){
			$value = isset($_POST[$field]) && is_string($_POST[$field]) ? wp_unslash($_POST[$field]) : null;
			if($value===null || trim($value)===''){
				$errors[$field] = $label.' is required.';
				continue;
			}
			$address = trim($value);
			$valid = !preg_match('/[\r\n\x00]/',$value) && strlen($value)<=254 && is_email($address);
			if($valid){
				$parts = explode('@',$address,2);
				$valid = strlen($parts[0])<=64 && !preg_match('/^\.|\.$|\.\./',$parts[0]);
				foreach(explode('.',$parts[1]) as $part){if(strlen($part)>63){$valid = false;}}
			}
			if(!$valid){$errors[$field] = $label.': enter one valid email address (name@example.com).';}
			$addresses[$field] = $address;
		}
		if($errors){
			wp_send_json_error(array('message'=>'Test email not sent. Please correct the marked fields.','fields'=>$errors),400);
		}
		$test = new CG_Mail_Test();
		wp_send_json_success($test->send($addresses));
	}
}
