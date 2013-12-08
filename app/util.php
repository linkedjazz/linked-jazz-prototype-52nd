<?




	class utils {
		
		private $recaptchaPublicKey = '';
		private $recaptchaPrivateKey = '';		
		
		
		
		
		public function recaptchaDisplay(){			
			require 'vendor/recaptcha/recaptchalib.php';	
			return recaptcha_get_html($this->recaptchaPublicKey);
		}
		public function recaptchaValidate($challenge,$response){			
			require 'vendor/recaptcha/recaptchalib.php';	
			$resp = recaptcha_check_answer ($this->recaptchaPrivateKey, $_SERVER["REMOTE_ADDR"], $challenge, $response);	
			if ($resp->is_valid == 1) {				
				return true;
			}else{
				return false;
			}
		}		
		
		public function errorLog(){	 
		
		
		
		}
		
		
		
		//Send the user to the error page when something breaks
		public function routeError(){		
			header('Location: http://linkedjazz.org/52new/error/'); 			
			
		}
		
		
		
		
		
		
	}






?>