<div class="modal hide" id="modalLogin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 id="myModalLabel">To keep everything organized, please log in before we start</h5>
  </div>
  <div class="modal-body">
  <span>Log in quickly by using an existing account. [<span style="font-size:12px; font-weight:bold;" rel="tooltip" data-placement="bottom" title="If you have an account with Google, Twiter or Facebook you can use it to log in to Linked Jazz. We only use it for authentication, we do not store or display your name, email address and do NOT have access to post on your wall, publish tweets, etc.">?</span>]</span><br /><br />
  
	<div class="pull-left">
    	<a id="linkGoogleLogin" href="/52new/login/?loginGoogle=true"><button class='zocial google'>Log in with Google</button></a>    
    </div>
	<div class="pull-left">
    	<a id="linkTwitterLogin" href="/52new/login/?loginTwitter=true"><button class='zocial twitter'>Log in with Twitter</button></a>
    </div>
	<div class="pull-left">
    	<a id="linkFacebookLogin" href="/52new/login/?loginFacebook=true"><button class='zocial facebook'>Log in with Facebook</button></a>
    </div>        
	<br class="clearfix" />
	<hr />
 <span>Otherwise you can log in or create a Linked Jazz account.</span><br /><br />
	<div class="alert alert-error" id="modalLoginRegisterFormAlert"></div>
	<div id="modalLoginLoginForm">
        <form class="form-inline">
          <input type="text" id="inputLoginUsername" class="span3" placeholder="Email Address">
          <input type="password" id="inputLoginPassword" class="span2" placeholder="Password">
          <button type="submit"  rel="loadingButton" data-loading-text="Working..." autocomplete="off" id="inputLoginSubmit"  class="btn">Log in</button>
        </form>    
        <a href="#" class="modalLoginShowRegister">Register an account.</a>&nbsp;&nbsp;&nbsp;<a href="#" class="modalLoginShowPassword">Forgot Password.</a>
    </div>

	<div id="modalLoginRegisterForm">  
        <form class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputEmail">Email</label>
            <div class="controls">
              <input type="text" id="inputRegisterEmail" placeholder="Email">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputUsername">User Name</label>
            <div class="controls">
              <input type="text" id="inputRegisterUsername" placeholder="User Name">
            </div>
          </div>          
          <div class="control-group">
            <label class="control-label" for="inputPassword">Password</label>
            <div class="controls">
              <input type="password" id="inputRegisterPassword" placeholder="Password">
            </div>
          </div>
          <div class="control-group">
          
          	<?=$utils->recaptchaDisplay()?>
           
          </div>
          
          <div class="control-group">
            <div class="controls">
              <button type="submit" id="inputRegisterSubmit" rel="loadingButton" data-loading-text="Registering Account..." autocomplete="off" class="btn">Register</button>
              <button  autocomplete="off" class="btn btn-link modalLoginShowLogin">I already have an account</button>
            </div>
          </div>
        </form>    
    
    </div>
    <div id="modalLoginForgotPassword">
        <form class="form-inline">
          <input type="text" id="inputForgot" class="span4" placeholder="Email Address">
           <button type="submit"  rel="loadingButton" data-loading-text="Working..." autocomplete="off" id="inputForgotSubmit"  class="btn">Send Reset</button>
        </form>    
        <a href="#" class="modalLoginShowRegister">Register an account.</a>&nbsp;&nbsp;&nbsp;<a href="#" class="modalLoginShowLogin">Log in.</a>    
    </div>


  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button> 
  </div>
</div>