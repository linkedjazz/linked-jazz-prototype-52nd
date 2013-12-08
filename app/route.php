<?php

	/*
	/	Controls the routing of the app
	/
	/
	/
	*/
	
	//aways start the session
	session_start();

	require 'app/db.php';
	require 'app/util.php';
	
	$db = new dataBase;
	$utils = new utils;
	
	
	//see if they are returning to the site, had  signed in before, but not signed in yet
	if(isset($_COOKIE['lj_hash']) == true && isset($_SESSION['userId']) == false){	
		require 'app/login.php';	
		$login = new login;
		$login->sessionLoginFromCookie();
	}
	
	 
	// if a request is made to 52ndst/login
	if (isset($_REQUEST['login'])){		
	
		
		require 'app/login.php';		
		
 		$login = new login;
 	
		//LOGOUT
		if (isset($_REQUEST['loginLogout'])){
			$login->logout();			
		}
	
	
	
	 	//TWITTER
		//They are logging in with twitter
		if (isset($_REQUEST['loginTwitter'])){
			if (isset($_REQUEST['authResponse'])){
				 $login->twitterAuthResponse();
			}else{
				$login->twitterForwardToLogin();
			}
		}		
		
	 	//GOOGLE
		//They are logging in with google 
		if (isset($_REQUEST['loginGoogle'])){						
			if (isset($_REQUEST['openid_ns'])){				
				$login->googleAuthResponse();
			}else{
				$login->googleForwardToLogin();
			}		
		}
		
	 	//FACEBOOK
		//They are logging in with facebook 
		if (isset($_REQUEST['loginFacebook'])){					
			if (isset($_REQUEST['state'])){						
				$login->facebookAuthResponse();
			}else{
				$login->facebookForwardToLogin();	
			}
		}
		
		//register a new account json exchange
		if (isset($_REQUEST['loginRegister'])){	
			$login->registerOrganic();
		}
		//login account json exchange
		if (isset($_REQUEST['loginOrganic'])){	
			$login->loginOrganic();
		}		
		//forget / reset stuff
		if (isset($_REQUEST['loginForgot'])){	
			$login->loginForgotSend();
		}		
 		if (isset($_REQUEST['loginReset'])){	
			require 'app/html/passwordReset.php';
			die();
		}			
		if (isset($_REQUEST['loginResetAction'])){	
			$login->loginResetPass(); 
		}			
		if (isset($_REQUEST['loginChangeScreenName'])){	
			$login->loginChangeScreenName(); 
		}		
		if (isset($_REQUEST['verify'])){	
			$login->loginVerify(); 
		}					
		
	}


	if (isset($_REQUEST['work'])){	

		//if they are not logged in then boot them out
		if(!isset($_SESSION['userId'])){		
			$_SESSION['urlForward']=$_SERVER["REQUEST_URI"];		
			header('Location: /52new/?forceLogin=true'); 
			die();
		}
		//if their session expired log them out
		if($_SESSION['userId'] == '' || $_SESSION['userId'] == '0' || $_SESSION['userId'] == 0){		
			$_SESSION['urlForward']=$_SERVER["REQUEST_URI"];		
			header('Location: /52new/login/?loginLogout=true'); 
			die();
		}	
	
	
		//json requests
		if (isset($_REQUEST['action'])){	
		
			header("content-type: application/json");
		
			if ($_REQUEST['action']=='returnMatchesForURI'){			
				echo $db->returnMatchesForURI($_GET['transcript'],$_GET['uri']);				
			}
			if ($_REQUEST['action']=='returnTextById'){			
				echo $db->returnTextById($_GET['transcript'],$_GET['id']);				
			}			
			if ($_REQUEST['action']=='storeRelationship'){			
				echo $db->storeRelationship($_POST['source'],$_POST['target'],$_POST['transcript'],$_POST['value'],$_POST['idLocals'],$_POST['points']);				
			}	
			
			
			if ($_REQUEST['action']=='storeComment'){			
				echo $db->storeComment($_POST['transcript'],$_POST['interviewee'],$_POST['mention'],$_POST['comment'],$_POST['pairs']);				
			}							
		 
			die();
		}
	
	
	
		require 'html/work.php';	
		die();	
	}



	



?>