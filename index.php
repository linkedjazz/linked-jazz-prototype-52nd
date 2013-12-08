<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');


	require 'app/route.php';





	//below is the homepage

?>
<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Linked Jazz 52nd Street</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
		<link rel="icon" type="image/png" href="/favicon.png">
      
        <link rel="stylesheet" href="/52new/css/bootstrap.min.css">
        <link rel="stylesheet" href="/52new/css/zocial.css">
        <link rel="stylesheet" href="/52new/css/main.css">        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script src="/52new/js/jquery.backstretch.min.js"></script>                
        <script src="/52new/js/bootstrap.js"></script>
        
        <script type="text/javascript">
		
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-34282776-1']);
		  _gaq.push(['_trackPageview']);
		
		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		
		</script>
        
        
        <script type="text/javascript">
		
		var fs = {};
		
		
		$(document).ready(function($) {
			
			
			
			
			
			fs.urlForward = '';
			
			fs.bind = function(){
				
				//$("a").unbind();
				
				
				
				//LOGIN FORM
				
				//register button
				$("#inputRegisterSubmit").bind("click",function(event){		
					$(this).button('loading');								
					fs.registerUser();
					event.preventDefault();
					return false;													
				});
				$("#inputLoginSubmit").bind("click",function(event){		
					$(this).button('loading');								
					fs.loginUser();
					event.preventDefault();
					return false;													
				});		
				$("#inputForgotSubmit").bind("click",function(event){		
					$(this).button('loading');								
					fs.resetUser();
					event.preventDefault();
					return false;													
				});								
				
				$("#linkAbout").bind("click",function(event){		
				
					if ($("#about").css("display")=='none'){
						$("#about").fadeIn('slow');	
					}else{
						$("#about").fadeOut('slow');
					}
					

					event.preventDefault();
					return false;													
				});						
				
				
				
				
				$(".modalLoginShowRegister").bind("click",function(event){	
					$("#modalLoginRegisterForm").css("display","block");
					$("#modalLoginLoginForm").css("display","none");
					$("#modalLoginRegisterFormAlert").css("display","none");	
					$("#modalLoginForgotPassword").css("display","none");			
					event.preventDefault();
					return false;					
				});
				$(".modalLoginShowLogin").bind("click",function(event){	
					$("#modalLoginLoginForm").css("display","block");
					$("#modalLoginRegisterForm").css("display","none");	
					$("#modalLoginRegisterFormAlert").css("display","none");	
					$("#modalLoginForgotPassword").css("display","none");				
					event.preventDefault();
					return false;					
				});				
				$(".modalLoginShowPassword").bind("click",function(event){	
					$("#modalLoginLoginForm").css("display","none");
					$("#modalLoginRegisterForm").css("display","none");	
					$("#modalLoginRegisterFormAlert").css("display","none");	
					$("#modalLoginForgotPassword").css("display","block");				
					event.preventDefault();
					return false;					
				});					 
				
				
				
				//when they click on an interview item make sure they are logged in before sending them
				$(".linkInerviewItem").bind("click",function(event){
					
					if(!signedIn){
						
						fs.urlForward = $(this).attr("href");  
						$("#modalLogin").modal('show');
						
						
						//modify the URLs of the 3rd party login to pass the url we want to go to
						
						$("#linkGoogleLogin").attr("href","/52new/login/?loginGoogle=true&urlForward=" + fs.urlForward);
						$("#linkTwitterLogin").attr("href","/52new/login/?loginTwitter=true&urlForward=" + fs.urlForward);
						$("#linkFacebookLogin").attr("href","/52new/login/?loginFacebook=true&urlForward=" + fs.urlForward);												
						
						event.preventDefault();
						return false;	
												
					}

				});
				
				
				
				//install the tool tips
				$('[rel="tooltip"]').tooltip();
				
				//transcript item popup
				$('[rel="popover"]').popover();
				

				 			
				
			}
			
			
			fs.loginUser = function(){
			
				var loginObj = {};
				loginObj.user = $("#inputLoginUsername").val();				
				loginObj.pass = $("#inputLoginPassword").val();	
				$.post("/52new/login/?loginOrganic=true", loginObj, function(data){					
					if (data.error){		
						$("#modalLoginRegisterFormAlert").text("Uh-oh: " + data.reason);
						$("#modalLoginRegisterFormAlert").css("display","block");
						$("#inputLoginSubmit").button('reset');	
						Recaptcha.reload();
						return false;
					}else{
						$("#modalLoginRegisterFormAlert").css("display","none");						
						
					}
					$("#inputLoginSubmit").button('reset');			
					
					if(fs.urlForward!=''){
						self.location=fs.urlForward;
						return true;	
					}
					$("#modalLogin").modal('hide');
					window.location.replace(window.location.pathname);
							
				}).error(function(data) { 
					alert("There was an error connecting to the server, is your internet connection down?");
				});
				
			}
			
			fs.resetUser = function(){
			
				var loginObj = {};
				loginObj.email = $("#inputForgot").val();		 
				
				$.post("/52new/login/?loginForgot=true", loginObj, function(data){					
					if (data.error){		
						$("#modalLoginRegisterFormAlert").text("Uh-oh: " + data.reason);
						$("#modalLoginRegisterFormAlert").css("display","block");
						$("#inputForgotSubmit").button('reset');
					}else{
						$("#modalLoginRegisterFormAlert").text("OKAY! An email has been sent to you");
						$("#modalLoginRegisterFormAlert").css("display","block");						
						
					}
					$("#inputForgotSubmit").button('reset');					
				}).error(function(data) { 
					alert("There was an error connecting to the server, is your internet connection down?");
				});
				
			}			
			
			fs.registerUser = function(){
			
				
			
				var registerObj = {};

				registerObj.email = $("#inputRegisterEmail").val();
				registerObj.user = $("#inputRegisterUsername").val();				
				registerObj.pass = $("#inputRegisterPassword").val();				
				registerObj.recaptcha_challenge_field = $("#recaptcha_challenge_field").val();
				registerObj.recaptcha_response_field = $("#recaptcha_response_field").val();								
				 
				$.post("/52new/login/?loginRegister=true", registerObj, function(data){
						
					if (data.error){		
						$("#modalLoginRegisterFormAlert").text("Uh-oh: " + data.reason);
						$("#modalLoginRegisterFormAlert").css("display","block");
						Recaptcha.reload();
					}else{
						$("#modalLoginRegisterFormAlert").css("display","none");						
						
					}
					
					$("#inputRegisterSubmit").button('reset');
					if(fs.urlForward!=''){
						self.location=fs.urlForward;
						return true;	
					}else{
						$("#modalLogin").modal('hide');
						window.location.replace(window.location.pathname);								
					}
			
					
				}).error(function(data) { 
					alert("There was an error connecting to the server, is your internet connection down?");
				});
						
				
					
				
				
			}
			
			
			
			fs.bind();
			$.backstretch("/52new/img/bgBlack4.jpg");
			
		});
		
		</script>
        
        
        
        
        <script type="text/javascript">
			<?
				if (isset($_SESSION['userId'])){			
					echo "var signedIn = true;";
				}else{
					echo "var signedIn = false;";					
				}
			?>
		</script>
        
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
        <![endif]-->

        <div id="mainPageFeedback"><a href="https://docs.google.com/forms/d/1pW3dQv0eYcA1ZRVhEHLaYOpZZTePmQ-_re1abjW4Q-Q/viewform">We need your feedback, tell us what you think&nbsp;<img src="/52ndStreet/img/feeback.png"></a></div>
        
        <div class="container" id="containerHomePage"> 
        
			<? include('app/html/accountTab.php'); ?>
        
        	<div class="hero-unit" id="homePageHero">
            	
                
                
            	<div class="row">
                	<div class="span5">
		            	<img src="/52new/img/lj_52_logo.png"/>
                    </div>
                	<div id="logoPhrase"> 
                        Revealing the relationships<br>of the jazz community
                    </div>                    
                </div>

              
            </div>
            
         
            
             
            <div class="hero-unit" id="getStartedHero">
            
            	<h3 style="margin-bottom:10px;">Let's get started!</h3>
            	<div style="text-align:center"><img src="img/1_2_3_4_VS4.png" style="width:700px; height:auto;"></div>
            
            <br>
                
                <div id="statsHero">
                               
                               
                    <? 
						$projectComplete = $db->returnProjectProgress();
						if ($projectComplete < 2){ $projectCompleteBar = 2; }else{$projectCompleteBar = $projectComplete;}
					?>           
                    
                    <div id="projectStatusHolder">
                        <div class="progress progress-info progress-striped">
                          <div class="bar" style="width: <?=$projectCompleteBar?>%">
                            <div id="statsHeroComplete"><? echo "$projectComplete% of Relationships Defined" ?></div>
                          </div>
                        </div>                    
                	</div>
                </div>
                
                
                
                <div id="homePageTopMusicians">
                 
                    <ul class="thumbnails"> 
                    <?
					
						$results = $db->returnTranscriptList();
											
						foreach ($results as &$ar) {
							
							$uriShort = $ar['intervieweeURI'];
							$uriShort = explode('/resource/',$uriShort);
							$uriShort = str_replace('>','',$uriShort[1]);
							
							$percentComplete = round($ar['percentComplete']);
							$percentCompleteReal = round($ar['percentComplete']);
							if ($percentComplete < 2){$percentComplete=2;}
							
							if ($ar['interviewees'] != ''){
								$otherInterviewees = '. ' .$ar['interviewees'] . " also participated in the interview.";
							}else{
								$otherInterviewees = '.';
							}
							
							$popOverContent = "Interview transcript from " . str_replace('_',' ',$ar['sourceName']) . ' interviewed by ' . $ar['interviewers'] . $otherInterviewees;
							 
							?>
							
                              <li class="span2">
                                <a href="/52new/work/<?=$uriShort?>" class="linkInerviewItem">
                                    <div class="thumbnail" data-trigger="hover" data-placement="top" rel="popover" data-title="<? echo $ar['interviewee'] . " ($percentCompleteReal% done)" ?>" data-content="<?=$popOverContent?>">
                                      <div class="thumbnailTitle"><?=$ar['interviewee']?></div>
                                      <img src="/image/square/<?=$uriShort?>.png" alt="">
                                        <div class="thumbnailProgress">
                                            <div class="progress progress-info progress-striped">
                                              <div class="bar" style="width: <?=$percentComplete?>%"></div>
                                            </div>                          
                                        </div>
                                    </div>
                                </a>
                              </li> 								
							
								
							<?
							
						}


					
					?> 
                    </ul>
                     
                    

                    
                                                                                 
                    
                
                </div>
            
            	<div style="text-align:center">This is a prototype tool under active development, if you have any comments or ideas we would <a href="mailto:linkedjazz@linkedjazz.org">love to hear them!</a></div>
            	<br style="clear:both;">

            </div>
              
        </div>
        


        
    

    
 
		<script type="text/javascript">
			
			<?
			
				//this takes care of some of the messy parts of dealing with trying to force the login modal pop when redirected from a log in required part of the site
				if(isset($_GET['forceLogin'])){
					?>
					
					$(document).ready(function($) {
						$("#modalLogin").modal('show');
						
						<?
						
							if (isset($_SESSION['urlForward'])){
							?>
								fs.urlForward = '<?=$_SESSION['urlForward']?>';
								$("#linkGoogleLogin").attr("href","/52new/login/?loginGoogle=true&urlForward=" + fs.urlForward);
								$("#linkTwitterLogin").attr("href","/52new/login/?loginTwitter=true&urlForward=" + fs.urlForward);
								$("#linkFacebookLogin").attr("href","/52new/login/?loginFacebook=true&urlForward=" + fs.urlForward);
 								
							<?
							}
						
						
						?>
						
					});
					
					<?	
				}
			?>
		
		</script>
    
    <? 
		if (!isset($_SESSION['userId'])){
			include('app/html/loginModal.php'); 
		}else{
			include('app/html/userModal.php'); 
		}
	
	?>
        <div class="container">
        
        	
            <div class="homePageHero" id="footer">
            
            
            	<div class="footerMenu">
                
                    <ul class="breadcrumb" style="background-color:#fff">
                    
                      
                      <li><a href="#modalAbout52" role="button"  data-toggle="modal">About 52nd Street</a> <span class="divider">/</span></li>
                      <li><a href="mailto:linkedjazz@linkedjazz.org">Contact Us</a> <span class="divider">/</span></li>
                      <li><a href="/">Linked Jazz Homepage</a></li>
                    </ul>                
                		
                       <span style="font-size:12px;">
                       <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">Linked Jazz</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.  
					 </span>
                     <br>
                     <span style="font-size:12px;">Background Image: <a href="http://memory.loc.gov/cgi-bin/query/r?ammem/gottlieb:@field(NUMBER+@band(gottlieb+02761))">William P. Gottlieb (photographer). (1948). [52nd Street, New York, N.Y., ca. July 1948]</a>.</span>
                </div>
            	
                
            
            </div>
        </div>
        
    </body>

    
</html>