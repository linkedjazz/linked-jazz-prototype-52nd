<?php

 

	
 	/*
	/	Controls login and 3rd party login
	/
	/
	/
	*/

	class login {
 
 		private $here = null;
 
 
	 
		//
		//	Twitter classes
		// 		
 
 		private function twitterConstruct(){
				
			require 'vendor/tmhOAuth/tmhOAuth.php';
			require 'vendor/tmhOAuth/tmhUtilities.php';	
			$tmhOAuth = new tmhOAuth(array(
			  'consumer_key'    => '',
			  'consumer_secret' => '',
			));			
			
			$this->here = tmhUtilities::php_self();			
			
			return $tmhOAuth;
			
		}
				
		public function twitterForwardToLogin(){

			$tmhOAuth = $this->twitterConstruct();

 			$callback = $this->here . '?loginTwitter=true&authResponse=true';

			//store the url we are trying to access			
			if (isset($_GET['urlForward'])){	
				$_SESSION['urlForward']=$_GET['urlForward'];
			}
			
			$params = array(
				'oauth_callback'     => $callback
			);
			
			$params['x_auth_access_type'] = 'read';
			
			$code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), $params);
			
			if ($code == 200) {
				$_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
				$method = 'authenticate'; //isset($_REQUEST['authenticate']) ? 'authenticate' : 'authorize';
				$force  = ''; //isset($_REQUEST['force']) ? '&force_login=1' : '';
				$authurl = $tmhOAuth->url("oauth/{$method}", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}{$force}";
 				
				header('Location: ' . $authurl . ' '); 
	
			} else {
				outputError($tmhOAuth);
				$util->routeError();
			}				
			
			
			
		}
		
		public function twitterAuthResponse(){

			$tmhOAuth = $this->twitterConstruct();

			if (isset($_REQUEST['oauth_verifier'])) {
				
				$tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
				$tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];
				
				$code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
					'oauth_verifier' => $_REQUEST['oauth_verifier']
				));
				
				if ($code == 200) {
					$response = $tmhOAuth->extract_params($tmhOAuth->response['response']);
 					
					$userObj = $this->returnUserAry();
					$userObj['name'] = $response['screen_name'];
					$userObj['type'] = 'twitter';
					$userObj['oauth_token'] = $response['oauth_token'];
					$userObj['oauth_verifier'] = $response['oauth_token_secret'];
					
					$db = new dataBase;
					$userId = $db->storeUser($userObj);
					
					$this->sessionLogin($userId);
					  					
				} else {
					outputError($tmhOAuth);
					$util->routeError();					
				}
					 
				
			}else{
				$util->routeError();	
			}
				
		}
 
 
 
 
 		//
		//	Google classes
		// 
 		public function googleForwardToLogin(){
			
			//store the url we are trying to access			
			if (isset($_GET['urlForward'])){	
				$_SESSION['urlForward']=$_GET['urlForward'];
			}
			
			
			require 'vendor/googleOpenID/googleOpenID.php';
			$googleLogin = GoogleOpenID::createRequest("52new/login/?loginGoogle=true");
			$googleLogin->redirect();
			
		}
 
		public function googleAuthResponse(){ 
		
			require 'vendor/googleOpenID/googleOpenID.php';
			$googleLogin = GoogleOpenID::getResponse();
			if($googleLogin->success()){
				
				$user_id = $googleLogin->identity();
			
				$userObj = $this->returnUserAry();
				
				$userObj['name'] = $user_id;
				$userObj['type'] = 'google';
				
				$db = new dataBase;
				$userId = $db->storeUser($userObj);
				
				$this->sessionLogin($userId);				
	 
			}
		
		
		}
		
 
  		//
		//	facebook classes
		// 
		
		private function facebookConstruct(){			
			require 'vendor/facebook/facebook.php';
			$facebook = new Facebook(array(
			  'appId'  => '',
			  'secret' => '',
			  'cookie' => true,
			));
			
			return 	$facebook;		
		}
		
		
 		public function facebookForwardToLogin(){
			
			
			//store the url we are trying to access			
			if (isset($_GET['urlForward'])){	
				$_SESSION['urlForward']=$_GET['urlForward'];
			}

			
			$facebook = $this->facebookConstruct();
			
			$user = $facebook->getUser();
			if ($user) {
			  try {
				// Proceed knowing you have a logged in user who's authenticated.
				$user_profile = $facebook->api('/me');
			  } catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			  }
			}			
			
			$loginUrl = $facebook->getLoginUrl(); 

			header('Location: ' . $loginUrl . ' '); 
		}
 
  		public function facebookAuthResponse(){
			
			$facebook = $this->facebookConstruct();
			$user = $facebook->getUser();
			if ($user) {
			  try {
				// Proceed knowing you have a logged in user who's authenticated.
				$user_profile = $facebook->api('/me');
			  } catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			  }
			}	 
 		
			$userObj = $this->returnUserAry();
			
			$userObj['name'] = $user_profile['username'];
			$userObj['type'] = 'facebook';
			
			$db = new dataBase;
			$userId = $db->storeUser($userObj);		
			
			$this->sessionLogin($userId);	
	 
		}
	 
	 
	 	//
		//	Organic login / register classes
		//
		public function registerOrganic(){
			
			$email = trim($_POST['email']);
			$user = trim($_POST['user']);			
			$pass = trim($_POST['pass']);
			$recaptcha_challenge_field = trim($_POST['recaptcha_challenge_field']);			
			$recaptcha_response_field =trim($_POST['recaptcha_response_field']);
 
			//test for empty values
	 		if ($email == '' || $user == '' || $pass == '' || $recaptcha_challenge_field == '' || $recaptcha_response_field == ''){
				$this->returnErrorJson("One of the required fields is empty");	
			}
			//email test
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->returnErrorJson("That does not look like a valid email address.");	
			}			
			//length test
			if (strlen($pass) < 4 || strlen($user) < 3) {
				$this->returnErrorJson("That username or password is too short.");	
			}		
			
			$db = new dataBase;
			
			//check to make sure tha name is free
			if ($db->isUserName($user)){
				$this->returnErrorJson("That username is already taken.");	
			}
			
			//check the recaptcha
			$utils = new utils;
			if (!$utils->recaptchaValidate($recaptcha_challenge_field,$recaptcha_response_field)){
				$this->returnErrorJson("The reCAPTCHA is incorrect.");	
			}
			
	 		//store the username and register the session
			$userObj = $this->returnUserAry();
			
			$userObj['name'] = $user;
			$userObj['type'] = 'organic';			
			$userObj['email'] = $email;
			$userObj['pass'] = $pass;
			
			
			
			//send them an verify email 
			$hash = sha1(uniqid(mt_rand(), true));		

			$message = "Hi, you created an account at Linked Jazz, please before we can use any of your contributions you need to verify your email. Please follow this link: http://linkedjazz.org/52new/login/?verify=$hash";
			$message = wordwrap($message, 70);
			$headers = 'From: linkedjazz@linkedjazz.org' . "\r\n" .
				'Reply-To: linkedjazz@linkedjazz.org' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();				
			mail($email, 'Linked Jazz Email Verification', $message,$headers);						
			 
			
			$userId = $db->storeUser($userObj);		
			$this->sessionLogin($userId);				

			//store the hash into the table
			$db = new dataBase;
			$db->setUserHash($email,'reset',$hash);					
			
	 
		}
		public function loginOrganic(){
			
 			$user = trim($_POST['user']);			
			$pass = trim($_POST['pass']); 
 
			//test for empty values
	 		if ($user == '' || $pass == ''){
				$this->returnErrorJson("One of the required fields is empty");	
			}


			$db = new dataBase;
			
			//check to make sure tha name exists
			if (!$db->isEmail($user)){
				$this->returnErrorJson("No such email.");	
			} 
			
			
			$valResults = $db->validateUser($user,$pass);
			if ($valResults == false){
				$this->returnErrorJson("Invalid password.");	
			}else{
				$this->sessionLogin($valResults);					
			}
			
 	
			
			
	 
		}
		
		public function logout(){
			
			session_unset();
			setcookie("lj_hash", NULL,-1, '/', 'linkedjazz.org');
			
		}
		
		
		public function loginForgotSend(){			
 			$email = trim($_POST['email']);
			
			$db = new dataBase;
			if($db->isEmail($email)){
				
				//build a reset hash
				$hash = sha1(uniqid(mt_rand(), true));		

				$message = "Hi,you asked for your Linked Jazz password to be reset. Please follow this link: http://linkedjazz.org/52new/login/?loginReset=$hash";
				$message = wordwrap($message, 70);
				$headers = 'From: linkedjazz@linkedjazz.org' . "\r\n" .
					'Reply-To: linkedjazz@linkedjazz.org' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();				
				mail($email, 'Linked Jazz Password Reset', $message,$headers);						
				
				//store the hash into the table
				$db->setUserHash($email,'reset',$hash);
				
				
			}else{
				$this->returnErrorJson("That email address is not in the system, maybe you logged in with Google/Twitter/Facebook?.");
			}
			
		}
	 	
		
		public function loginChangeScreenName(){
			$db = new dataBase;
			$resuts = $db->changeScreenName($_POST['newName'],$_SESSION['userId']);						

			header("content-type: application/json");			
			if ($resuts){				
				echo json_encode(array('error' => false));	
			}else{
				echo json_encode(array('error' => true));				
			}
			die();
			
		}
		
		public function loginVerify(){
			
			$db = new dataBase;
			$resuts = $db->verify($_GET['verify']);	
			if ($resuts){				
				
				echo 'Okay! You are verified. Forwarding you to the <a href="/52new/">Start Page</a>';
				echo '<script>setTimeout(function(){self.location = "/52new/";},4000);</script>';
				
			}else{
				
				echo 'Error. <a href="/52new/">Return Home</a>';
							
			}			
			die();
			
			
		}
		
		 
	 
	 	public function loginResetPass(){	
			
			if (trim($_POST['inputNewPassword'])==''){
				
				header('Location: /52new/login/?loginReset=' . $_REQUEST['loginResetAction'] . ' '); 
				die();
			}
		
		
			//update the hash
			$db = new dataBase;
			$id = $db->resetUserPassword($_REQUEST['loginResetAction'],$_POST['inputNewPassword']);
			$this->sessionLogin($id);
			header('Location: /52new/'); 
		
		}
	 
		public function sessionLoginFromCookie(){
			$db = new dataBase;
			$id = $db->returnUserByHash($_COOKIE['lj_hash']);
			$this->sessionLogin($id); 
			
		}
			 
	 
	 
	 	private function returnErrorJson($error){
			header("content-type: application/json");
			$arr = array('error' => true, 'reason' => $error);
			echo json_encode($arr);			
			die();			
		}
	 
	 
	 
		private function sessionLogin($userId){

			//set the userid
			$_SESSION['userId'] = $userId;
			
			//set a long term cookie
			$hash = sha1(uniqid(mt_rand(), true));		
			if (setcookie("lj_hash", $hash, time() + 86400 * 365,'/','linkedjazz.org')){				
				$db = new dataBase;
				$db->setUserHash($userId,'cookie',$hash);				
			}
					
			//if they logged in with 3rd pary and care comeing back to the site via clicking on the musican item
			if (isset($_SESSION['urlForward'])){		
				header('Location: ' . $_SESSION['urlForward']); 
				$_SESSION['urlForward'] = NULL;
				unset($_SESSION['urlForward']);
			}			

			
			
		}
	
		
		private function returnUserAry(){
		
			$ary = array();
			$ary['name'] = '';
			$ary['type'] = '';
			$ary['email'] = '';
			$ary['pass'] = '';
			$ary['oauth_token'] = '';
			$ary['oauth_verifier'] = 0;
			
			return $ary;
			
		}
		
		
 
	}
  


	
	function outputError($tmhOAuth) {
	  echo 'Error: ' . $tmhOAuth->response['response'] . PHP_EOL;
	  tmhUtilities::pr($tmhOAuth);
	}


?>