        	<div id="loginHolder">
            
            
            	<style>
		
				</style>
				
           
                <div class="dropdown  pull-left">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                       <i style="color:#08C" class="icon-info-sign"></i>&nbsp;About</a><b class="caret"></b>&nbsp;&nbsp;
                    
                    <ul class="dropdown-menu" style="right:0; left:auto; min-width:250px">
                        
                        
                        
                        <li><a href="#modalAbout52" role="button" class="link" data-toggle="modal" id="linkAbout52"><img src="/52new/img/52_street_sign.png" style="height:45px; width:auto" /> About 52nd Street Tool</a></li>
                        <li class="divider"></li>
                        <li><a href="#modalAboutLJ" role="button" class="link" data-toggle="modal" id="linkUser"><img src="/52new/img/lj_logo_alone.png" style="height:45px; width:auto" /> About Linked Jazz Project</a></li>
                        
                    </ul>
                </div>                
            
            	<? 
				if (isset($_SESSION['userId'])){
				
					$user = $db->returnUserSingle($_SESSION['userId']);
					
					if ($user['screenName'] ==''){
						$userName = $user['name'];
					}else{
						$userName = $user['screenName'];
					}
					
					if (strlen($userName)>20){
						
						$userName = substr($userName,0,20) . '...';	
					}
					
					
					?>
                    
                        <div class="dropdown pull-left">
                        	&nbsp;|&nbsp;
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                <i style="color:#08C" class="icon-user"></i>&nbsp;Account</a><b class="caret"></b>
                            
                            <ul class="dropdown-menu" style="right:0; left:auto;">
                            	<li style="text-align:center; color:#999"><?=$userName?></li>
                                <li class="divider"></li>
                                <li><a href="#modalUser" role="button" class="link" data-toggle="modal" id="linkUser">Options</a></li>
                                <li class="divider"></li>
                                <li><a href="/52new/login/?loginLogout=true">Logout</a></li>
                            </ul>
                        </div>                       
                    
<!--                    	<a href="#modalUser" role="button" class="link" data-toggle="modal" id="linkUser"><i style="color:#08C" class="icon-user">&nbsp;</i>&nbsp;<?=$userName?></a><span>&nbsp;|&nbsp;</span><a href="/52new/login/?loginLogout=true">Logout</a>
-->					<?
				}else{
					?>
                    	&nbsp;|&nbsp;&nbsp;<i style="color:#08C" class="icon-user"></i>&nbsp;<a href="#modalLogin" role="button" class="link" data-toggle="modal" id="linkLogin">Login</a>
                    <?
				}
				?>
            	
            	
            </div>
            
            
    
    <div class="modal hide" id="modalAbout52" tabindex="-1" role="dialog" aria-labelledby="myModalLabelAbout52" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabelAbout52">About 52nd Street Tool</h3>
      </div>
      <div class="modal-body">
    		<div class="thumbnail" style="text-align:center;"><img src="/52new/img/Great_Day_in_Harlem.jpg"><a href="http://www.artkane.com/">Art Kane</a> — A Great Day In Harlem, 1958</div>

Linked Jazz 52nd Street is a crowdsourcing tool that functions as an essential part of the overall Linked Jazz project. Linked Jazz 52nd Street invites users to select the type of relationship shared by jazz musicians based on short excerpts of interview transcripts pulled from jazz history archives. The relationships that users choose contribute to the Linked Open Data semantics on these jazz musicians.
<br /><br />
How exactly does that work? User contributions to Linked Jazz 52nd Street fill in the blanks for computers. Before a user chooses a relationship, the computer only knows that Musician A  and Musician B have some sort of relationship. Using the Linked Jazz 52nd Street interface, users can tell the computer that Musician A was the mentor of Musician B, that Musician C has met Musician D, and that Musician X toured with Musician Y. These relationships are then translated into Linked Open Data readable by computers, which can then be manipulated to visualize the jazz community network. Each choice that a user makes thus contributes to a richer and more sophisticated understanding of the complex relationships within the jazz community.            
            
            <br /><br />
            This is a prototype tool under active development, if you have any comments or ideas we would <a href="mailto:linkedjazz@linkedjazz.org">love to hear them!</a>
    
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button> 
      </div>
    </div>
    
    <div class="modal hide" id="modalAboutLJ" tabindex="-1" role="dialog" aria-labelledby="myModalLabelAboutLJ" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabelAboutLJ">About Linked Jazz Project</h3>
      </div>
      <div class="modal-body">
    	
        <div class="pull-left" style="width:27%">
        	<img src="/52new/img/lj_logo_alone.png" style="height:140px; width:auto" />
    	</div>
        <div class="pull-left"  style="width:70%">
        <p>
Linked Jazz is an ongoing project that applies Linked Open Data (LOD) technology to enhance the discovery and visibility of digital cultural heritage materials. Utilizing documents from digital archives of jazz history, Linked Jazz works to expose the social relationships between jazz musicians and reveal their community’s network. This project aims to help uncover meaningful connections between documents and data related to the lives of musicians who often practice in rich and diverse social networks. Read more about Linked Jazz on the <a href="http://linkedjazz.org">Linked Jazz project website</a>. 
<br /><br />
Among the many tools and methods team members have developed, Linked Jazz 52nd Street plays an key role in the overall Linked Jazz project. This crowdsourcing tool provides an interface where users decide what type of relationship two jazz musicians share based on excerpts from interview transcripts. Read more about Linked Jazz 52nd Street.
        
        
         </p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button> 
      </div>
    </div>    
    
            
            
                
    <script type="text/javascript">
	
	/*
	 * Project: Twitter Bootstrap Hover Dropdown
	 * Author: Cameron Spear
	 * Contributors: Mattia Larentis
	 *
	 * Dependencies?: Twitter Bootstrap's Dropdown plugin
	 *
	 * A simple plugin to enable twitter bootstrap dropdowns to active on hover and provide a nice user experience.
	 *
	 * No license, do what you want. I'd love credit or a shoutout, though.
	 *
	 * http://cameronspear.com/blog/twitter-bootstrap-dropdown-on-hover-plugin/
	 */(function(e,t,n){var r=e();e.fn.dropdownHover=function(n){r=r.add(this.parent());return this.each(function(){var n=e(this).parent(),i={delay:500,instantlyCloseOthers:!0},s={delay:e(this).data("delay"),instantlyCloseOthers:e(this).data("close-others")},o=e.extend(!0,{},i,o,s),u;n.hover(function(){o.instantlyCloseOthers===!0&&r.removeClass("open");t.clearTimeout(u);e(this).addClass("open")},function(){u=t.setTimeout(function(){n.removeClass("open")},o.delay)})})};e(document).ready(function(){e('[data-hover="dropdown"]').dropdownHover()})})(jQuery,this);
		
	
	</script>