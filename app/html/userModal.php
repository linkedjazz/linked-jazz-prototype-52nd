
<script type="text/javascript">

$(document).ready(function($) {

			$("#inputChangeScreenNameSubmit").bind("click",function(event){				
				fs.changeScreenName();		
				event.preventDefault();
				return false;					
				
			});
			
			fs.changeScreenName = function(){
			
				var changeObj = {};
				changeObj.newName = $("#inputNewScreenName").val();		 
				
				$.post("/52new/login/?loginChangeScreenName=true", changeObj, function(data){					
				
					if (data.error){		
						$("#modalLoginRegisterFormAlert").text("Sorry that name is already taken");
						$("#modalLoginRegisterFormAlert").css("display","block");
					}else{
						$("#modalLoginRegisterFormAlert").text("OKAY! You name has been changed, please refresh to see the change.");
						$("#modalLoginRegisterFormAlert").css("display","block");						
						
					}
				
				}).error(function(data) { 
					alert("There was an error connecting to the server, is your internet connection down?");
				});
				
			}	
	
});

</script>


<div class="modal hide" id="modalUser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 id="myModalLabel">Your Settings</h5>
  </div>
  <div class="modal-body">

	<div id="modalLoginLoginForm">    
    	<div class="alert alert-error" id="modalLoginRegisterFormAlert"></div>

        <form class="form-inline">        
          <span>Change My Screen Name</span>
          <input type="text" id="inputNewScreenName" class="span2" placeholder="New Screen Name">
          <button type="submit"  rel="loadingButton" data-loading-text="Working..." autocomplete="off" id="inputChangeScreenNameSubmit"  class="btn">Change Name</button>
        </form>    
        
        
        
    </div>
<br /><br />
	TODO: add other options~


  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button> 
  </div>
</div>