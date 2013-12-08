<?
 
	//get the people list
	$transcriptMeta = $db->returnTranscriptMetaByURI($_GET['work']);
 	$matchPeople = $db->returnMatchPeople($transcriptMeta['md5']);	

	$matchList = array();
	$lastPerson = '';
	$personAry = array();
	$personAry['count'] = 0;
	$allPeopleJS = "";
	
	$peopleAddedToJSObj = array();
	
	
	foreach ($matchPeople as $aPerson){
		
		
		
		
		//if ($lastPerson!=''&&$lastPerson!=$aPerson['personURI']){		
		
		if (array_key_exists('uri',$personAry) && in_array($personAry['uri'],$peopleAddedToJSObj) == false){
			
		
			$personAry['uri'] = str_replace("'","%27",$personAry['uri'] );
			
			$allPeopleJS .= "fs.allPeople.push('" . $personAry['uri'] . "');\n";
			
			
			$peopleAddedToJSObj[]=$personAry['uri'];
			$matchList[]=$personAry;		
			
			//reset 
			$personAry = array();
			$personAry['count'] = 0;				
			
			
		}
		
		$personAry['uri'] = $aPerson['personURI'];
		$personAry['name'] = $aPerson['name'];
		$personAry['image'] = $aPerson['image'];
		$personAry['idLocal'] = $aPerson['idLocal'];
		$personAry['count'] = $personAry['count'] + 1;		
		
		
		$lastPerson = $aPerson['personURI'];
	}
 

	//review their stored progress
	$userProgress = $db->returnProgress($transcriptMeta['md5']);
	 
	$pointerEarned = 0;
	$completedPeopleJS = '';
	$completedPeopleNodes = array();
	
	foreach ($userProgress as $aProgress){
	
	 
		
		
		foreach ($matchList as $aPerson){
			
			if ($aProgress['target'] == $aPerson['uri']){
				
				$pointerEarned = $pointerEarned + $aProgress['points'];
				
				/*
				print "<pre>";
				
					print_r($aProgress);
					print "~~~~~~~~<br>";
					print_r($aPerson);					
				
				print "</pre><hr>";		
				*/
				
				
				//if they have a count larger? MAYBE BUG HERE TODO		
				if ($aPerson['count'] >= $aProgress['count']  || $aProgress['points'] >= $aPerson['count']){
					//they finished this person	
					$aPerson['uri'] = str_replace("'","%27",$aPerson['uri'] );
					
					$completedPeopleJS .= "fs.completedPeople.push('" . $aPerson['uri'] . "');\n";
					
					
					//also build their network node
					//$completedPeopleNodesJS
				//	$completedPeopleNodes[]=array("x" => 0, "y" => 0,

					//fs.viz.nodes.push({x:fs.viz.width/2,y:fs.viz.height/2, type: updateObj.value, name: fs.activeName, points : fs.activePoints, strength: fs.viz.linkStrengthLookup[updateObj.value]});	

				} 
				
			}
			
			
		}
	
	
	} 

	$isNoob = $db->isNoob();
	
	if ($isNoob['count']==0){
		$isNoob = 'fs.showTutorial = true;';
	}else{
		$isNoob = 'fs.showTutorial = false;';		
	}
	
	 

?>
<!DOCTYPE HTML>
<html>
<head>
    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Linked Jazz 52nd Street</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

	<link rel="icon" type="image/png" href="/favicon.png">
    
    <link rel="stylesheet" href="/52new/css/bootstrap.min.css">
	<link rel="stylesheet" href="/52new/css/main.css">     
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
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
	<title>Linked Jazz 52nd St</title>
    
    
    <script>
		
		var interviewee = "<?=$transcriptMeta['interviewee']?>";
	
	</script>
    
    
    <script type="text/javascript">
 
 

		var fs={};
		
		//a global flag to help the flow of aysnc requets
		fs.working=false;
		fs.activePairs = [];
		fs.activeURI = '';
		fs.activePoints = 0;
		fs.activePairIndex = 0;
		fs.idLocalTop = 0;
		fs.idLocalBottom = 0;		
		fs.activeName = '';
		fs.idLocalsSeen = [];
		fs.pointsEarned = 0;
		fs.pointsTotal = 0;
		fs.allPeople = [];
		fs.completedPeople = [];
		fs.showTutorial = false;
		fs.tutorialStep = 0;
		fs.hasSVG = false;
		
		
		
		fs.viz = {};
		fs.viz.linkStrengthLookup = [];
		
		
		
		///////////////////
		
		
		fs.viz.init = function(){
			
			
			
			//we are dyamicly loading d3, so if it is not yet ready, take a break, and try again later
			if (typeof d3 == 'undefined'){			
				setTimeout(fs.viz.init,1000);
				return false;	
			}
			
			
			fs.viz.linkStrengthLookup['knows'] = 0.1;
			fs.viz.linkStrengthLookup['hasMet'] = 0.5;
			fs.viz.linkStrengthLookup['acquaintance'] = 0.5;			
			fs.viz.linkStrengthLookup['friend'] = 2.5;			
			fs.viz.linkStrengthLookup['collaborated'] = 0.5;	
			fs.viz.linkStrengthLookup['collaboratedPlayedTogether'] = 0.5;	
			fs.viz.linkStrengthLookup['collaboratedTouredWith'] = 0.5;	
			fs.viz.linkStrengthLookup['collaboratedInBandTogether'] = 0.5;	
			fs.viz.linkStrengthLookup['collaboratedWasBandleader'] = 0.5;	
			fs.viz.linkStrengthLookup['collaboratedWasBandmember'] = 0.5;	
					
			fs.viz.linkStrengthLookup['influenced'] = 2.2;			
			fs.viz.linkStrengthLookup['mentor'] = 2.5;															
			
						
			fs.viz.width = $("#viz").width() - 3;
			fs.viz.height = $("#viz").height() - 3;
			fs.viz.fill = d3.scale.category20();
			fs.viz.nodes = [];
			fs.viz.links = [];			
			
			fs.viz.nodes.push({x:fs.viz.width/2,y:fs.viz.height/2, type:'root', name: fs.transcriptMeta['interviewee'], points:2});
			
			
			fs.viz.vis = d3.select("#viz").append("svg")
				.attr("width", fs.viz.width)
				.attr("height", fs.viz.height)
				.style("fill", "none");		
				
			fs.viz.vis.append("rect")
				.attr("width", fs.viz.width)
				.attr("height", fs.viz.height);					
			
			fs.viz.force = d3.layout.force()
				.distance(10)
				.charge(-160)
				.nodes(fs.viz.nodes)
				.links(fs.viz.links)
				.linkStrength(function(d){return d.target.strength;})
				.size([fs.viz.width, fs.viz.height]);			
			
			fs.viz.force.on("tick", function() {
			  fs.viz.vis.selectAll("line.link")
				  .attr("x1", function(d) { return d.source.x; })
				  .attr("y1", function(d) { return d.source.y; })
				  .attr("x2", function(d) { return d.target.x; })
				  .attr("y2", function(d) { return d.target.y; });
			
			  fs.viz.vis.selectAll("circle.node")
				  .attr("cx", function(d) { return d.x; })
				  .attr("cy", function(d) { return d.y; });
			 
			  fs.viz.nodes[0].x = fs.viz.width/2;
			  fs.viz.nodes[0].y = fs.viz.height/2;
				  
			});		
			
			fs.viz.restart();
				
		}
		 
		fs.viz.restart = function(){
			
		  
		
		  fs.viz.vis.selectAll("line.link")
			  .data(fs.viz.links)
			.enter().insert("line", "circle.node")
			  .attr("class", "link")
			  .attr("x1", function(d) { return d.source.x; })
			  .attr("y1", function(d) { return d.source.y; })
			  .attr("x2", function(d) { return d.target.x; })
			  .attr("y2", function(d) { return d.target.y; });
		
		  fs.viz.vis.selectAll("circle.node")
			  .data(fs.viz.nodes)
			.enter().insert("circle", "circle.cursor")
			  .attr("class", function(d){ return "viz" + d.type + ' node'}) 
			  .attr("cx", function(d) { return d.x; })
			  .attr("cy", function(d) { return d.y; })
			  .attr("r", function(d) { return Math.sqrt(d.points) * 4; })
			  .call(fs.viz.force.drag)
			  .append("title").text(function(d) { return d.name; });			
			
			fs.viz.force.start();
				
		}
		 
		
		
		///////////////
	
		fs.tutorialRebind = function(){
			$(".tutorialNext").unbind('click');
			$(".tutorialNext").bind('click',function(){				
				fs.tutorialStep = fs.tutorialStep +1;
				fs.tutorial();
			});		
			
			$(".tutorialStop").unbind('click');	
			$(".tutorialStop").bind('click',function(){
  				$("#tutorialStart").popover('hide'); 				
				$("#tutorialStart").css("display","none");				
				fs.tutorialStep = 7;
				fs.tutorial();				
			});			
			
		}
	
		fs.tutorial = function (){
		
		
					
			switch(fs.tutorialStep)
			{
			case 0:
				$("#tutorialStart").css("display","block");
				$("#tutorialStart").popover('show');
				fs.tutorialRebind();
 				break
			case 1:
  				$("#tutorialStart").popover('hide'); 				
				$("#tutorialStart").css("display","none");
				$("#matchList").css("background-color","#AED5FA");
				//$("#tutorialStartContentArea").css("display","block");
				//$("#tutorialStartContentArea").popover('show');
				$("#matchList").popover('show');
				fs.tutorialRebind();				
 				break	
			case 2:
  				$("#matchList").popover('hide'); 				
				$("#tutorialStart").css("display","none");
				$("#matchList").css("background-color","transparent");
				$("#tutorialStartContentArea").css("display","block");
				$("#tutorialStartContentArea").popover('show');
				$("#transcriptHolder").css("background-color","#AED5FA");
				fs.tutorialRebind();								
			
				break			
			case 3:
  				$("#tutorialStartContentArea").popover('hide'); 				
				$("#tutorialStartContentArea").css("display","none");
				$("#transcriptHolder").css("background-color","transparent");
				
				$("#transcriptHolderMoreUp").popover('show');
				$("#transcriptHolderMoreDown").popover('show');				
				fs.tutorialRebind();				
 				break	
			case 4:
				$("#transcriptHolderMoreUp").popover('hide');
				$("#transcriptHolderMoreDown").popover('hide');				
				$("#relButtonHolder").css("background-color","#AED5FA");
				$("#relButtonHolder").popover('show');		

				fs.tutorialRebind();				
 				break	
			case 5:
				$("#relButtonHolder").popover('hide');
 				$("#relButtonHolder").css("background-color","transparent");
				$("#logoHolder").popover('show');		
				fs.tutorialRebind();				
 				break	
			case 6:
				$("#logoHolder").popover('hide');
				$("#tutorialStartDone").css("display","block");
 				$("#tutorialStartDone").popover('show');		
				fs.tutorialRebind();				
 				break					
			case 7:
				$("#tutorialStartDone").popover('hide');
				$("#tutorialStartDone").css("display","none");
				fs.showTutorial = false;
				fs.updatePeopleList();
				fs.updatePercentComplete();
				fs.gotoNextPerson();
				
 				break					
				
				
															
			}			
		
		
			
			
		}
	
	
		fs.gotoNextPerson = function(){
			
			var found = false;
			
			for (x in fs.allPeople){
				
				//if 	(fs.completedPeople.indexOf(fs.allPeople[x]) == -1){					
				if 	($.inArray(fs.allPeople[x],fs.completedPeople)==-1){
					
					$(".matchListItemLink").each(function(){
						if ($(this).data('uri') == fs.allPeople[x]){								 
				 
							$("#matchList").animate({scrollTop:$("#matchList").scrollTop() + $(this).children(".matchListItem").position().top + 2}, 500);							
							$(this).children(".matchListItem").addClass("matchListItemActive");							
							$(this).click();
							
							found = true;
							return true;							
						}
					});
					
					if (found){return true;}
				}
			}
			//if they get here they finished all of the people
			$("#tutorialFinishedAll").css("display","block");
			$("#tutorialFinishedAll").popover('show');
			$("#finalPoints").text(fs.pointsEarned);
			

		}


		fs.updatePeopleList = function(){
			for (x in fs.allPeople){
				//if 	(fs.completedPeople.indexOf(fs.allPeople[x]) != -1){
				if 	($.inArray(fs.allPeople[x],fs.completedPeople)!=-1){						
					$(".matchListItemLink").each(function(){
						if ($(this).data('uri') == fs.allPeople[x]){							
							$(this).children(".matchListItem").addClass("matchListItemDone");
							$(".matchListItem").removeClass("matchListItemActive");
							
							
							$(this).children().first().popover({content: "Relationship analysis complete. CLick to revise your work", trigger: "hover", placement: "top"});
							
						}
					});
				}
			}
		}



		fs.updatePercentComplete = function(){
			var percent = Math.round(fs.pointsEarned / fs.pointsTotal * 100);						
			if (percent>100){percent=100;}
			$("#statsHeroComplete").text(percent + '% Complete!');	
			$(".bar").css("width",	percent + '%');	
			
		}

		fs.storeRelationship = function(button){
			
			
			$('[rel="popover"]').popover('hide');
			
			var updateObj = {};
			
			updateObj.transcript = fs.transcriptMeta['md5'];
			updateObj.target = fs.activeURI;
			updateObj.source = fs.transcriptMeta['intervieweeURI'];
			updateObj.value = button.data("value");
			updateObj.idLocals = fs.activePairs[fs.activePairIndex].question.idLocal + ',' + fs.activePairs[fs.activePairIndex].answer.idLocal;
			updateObj.points = fs.activePairs[fs.activePairIndex].points;
			
			fs.activePoints = fs.activePoints + fs.activePairs[fs.activePairIndex].points;
			
			$.post("/52new/work/?action=storeRelationship", updateObj, function(data){					
				//if we are updating a previous response do not add points, they area alredy added in
				if (data.points){
					fs.pointsEarned = fs.pointsEarned + fs.activePairs[fs.activePairIndex].points; 		
					fs.updatePercentComplete();									
				}
			});
			
			

			 
			
			//move to the next question
 			if (fs.activePairIndex+1 >= fs.activePairs.length){
			
				//they	are done
				//if (fs.completedPeople.indexOf(fs.activeURI)==-1){		
				if 	($.inArray(fs.activeURI,fs.completedPeople)==-1){					
					fs.completedPeople.push(fs.activeURI); 
					
					
					if (fs.hasSVG && updateObj.value != 'skip'){
						fs.viz.nodes.push({x:fs.viz.width/2,y:fs.viz.height/2, type: updateObj.value, name: fs.activeName, points : fs.activePoints, strength: fs.viz.linkStrengthLookup[updateObj.value]});	
						fs.viz.links.push({source: fs.viz.nodes[0], target: fs.viz.nodes[fs.viz.nodes.length-1]});
						fs.viz.restart();
					}
					
				}
				
				
				fs.activePoints = 0;
				fs.updatePeopleList();
				fs.gotoNextPerson();
				
			}else{
				
				
				fs.activePairIndex = fs.activePairIndex +1;
				 
				//have they already seen the text we are about to show them? If so then go to one where they have not yet see the text
 				if(
					//fs.idLocalsSeen.indexOf(parseInt(fs.activePairs[fs.activePairIndex].question.idLocal)) != -1 
					$.inArray(parseInt(fs.activePairs[fs.activePairIndex].question.idLocal),fs.idLocalsSeen) != -1
					
					&& 
					//fs.idLocalsSeen.indexOf(parseInt(fs.activePairs[fs.activePairIndex].answer.idLocal)) != -1
					$.inArray(parseInt(fs.activePairs[fs.activePairIndex].answer.idLocal),fs.idLocalsSeen) != -1
				 ){
						
 					 fs.storeRelationship(button);
					 return false;					
				}
				
				fs.buildTranscript();
				
				
			}
			
			
			
		}
		
		fs.markupText = function(name,text){
			//mark it up
			text = text.replace(name,'<span class="highlight">'+name+'</span>');
			var nameParts = name.split(' ');
			for (x in nameParts){
				text = text.replace(' '+nameParts[x] + ' ','<span class="highlight"> '+nameParts[x]+' </span>');
				text = text.replace(nameParts[x] + '.','<span class="highlight">'+nameParts[x]+'</span>.');				
				text = text.replace(nameParts[x] + ',','<span class="highlight">'+nameParts[x]+'</span>,');				
 			}
			return text;
		}
		
		fs.addBubble = function(idLocal){
			
 			$.get("/52new/work/", { action: 'returnTextById', transcript: fs.transcriptMeta['md5'], id: idLocal },function(data){			
				
				//remeber that they have seen this text
				fs.idLocalsSeen.push(idLocal);
				
				var dom = fs.buildBubbleDom([data]);
				
				if (idLocal == fs.idLocalBottom +1){				
					fs.idLocalBottom = idLocal;
					$('#transcriptContent').append(dom);
					$("#transcriptHolder").animate({ scrollTop: $('#transcriptHolder')[0].scrollHeight}, 0);
				}else{
					fs.idLocalTop = idLocal;
					$('#transcriptContent').prepend(dom);
					$("#transcriptHolder").animate({scrollTop:$('#transcriptHolder').offset().top - 100}, 0);
 					
				}
					
								
			});			
			
			
		}
		
		fs.buildTranscript = function(){
			
			if(fs.showTutorial){return;}
			
			
			fs.idLocalsSeen = [];

			//due to the async processs we are going to weed out any dupe pairs here, a dupe can happen if the name appears in the question and answer, so it looks
			//like two occurances, and is, but we are seeing them both at the same time, so we don't need to show the pair twice
 			for (x in fs.activePairs){
				for (y in fs.activePairs){					
					if (x!=y && fs.activePairs[x].question.idLocal == fs.activePairs[y].question.idLocal && fs.activePairs[x].answer.idLocal == fs.activePairs[y].answer.idLocal){
  						fs.activePairs.splice(y,1);
					}
				}
			}



			$("#scoreBoardProgress h5").text('(' + Math.abs(fs.activePairIndex +1) + ' of ' + fs.activePairs.length + ' mentions)');

			var pairs = fs.activePairs[fs.activePairIndex];
								

			fs.idLocalTop=parseInt(pairs.question.idLocal);
			fs.idLocalBottom=parseInt(pairs.answer.idLocal);
 			 
			 
			$('#transcriptContent').fadeOut('fast', function() {			
				$('#transcriptContent').empty();
				$('#transcriptContent').append(fs.buildBubbleDom(pairs));				
				//if the intial build is two anwsers or two questions, then add the previous one
				if ((pairs.question.type=='A'&&pairs.answer.type=='A')||(pairs.question.type=='Q'&&pairs.answer.type=='Q')){
					fs.addBubble(fs.idLocalTop-1);
				}				
				$('#transcriptContent').fadeIn();
			});

		}
		 
		
		//builds a dom obj based on a pair and returns it
		fs.buildBubbleDom = function(pair){
		
			 
		
			var domObj = $("<div>");
			
			//it can be passed a pair obj or just a single one to build
			if (pair instanceof Array){
				var loopAry = pair;
			}else{
				var loopAry = [pair.question,pair.answer];	
			}
			
			
			for (x in loopAry){
			
				//what classes are we doing
				if (loopAry[x].type=='Q'){
					var useImage = "/52new/img/no_image_square.png";
					var title = "Interviewer";
					var bubbleClass = 'question';					
					var imageHolderClass = 'questionImage';
				}else{
					var useImage = "/image/square/" + fs.intervieweeImage + '.png';
					var title = "";
					var imageHolderClass = 'answerImage';		
					var bubbleClass = 'answer';			
				}
				
				
				//try to see if the person speaking is really the primary interviewee
				//if there is only one interviewee then nevermind
				
				if (fs.transcriptMeta['interviewees'] != '' && loopAry[x].type=='A' && loopAry[x].speaker != ''){
					
					
					
					//see if it doesn't	look like the main guy
					if (fs.transcriptMeta['interviewee'].toLowerCase().search(loopAry[x].speaker.toLowerCase()) == -1){
						
						
						
						//so we did not find the speaker name within the main person's name, see if we can find it in the others
						if (fs.transcriptMeta['interviewees'].toLowerCase().search(loopAry[x].speaker.toLowerCase()) != -1){
							
							//console.log("THis look like a otherasdfsaf ds", loopAry[x]);
							var useImage = "/52new/img/no_image_square.png";
							var title = loopAry[x].speaker;
									
							var imageHolderClass = 'answerIntervieweesImage';		
							var bubbleClass = 'answerInterviewees';							
							
						}
						
					}
					
				}
				
				if (loopAry[x].type=='Q' && loopAry[x].speaker != ''){
						
						
						//we have multiple interviewers, sometimes the output will have the code used to differeciate them (modifed transcript output)
						//TODO have more info output from the analyzer
						
						if (loopAry[x].speaker.length > 2){
							//not an abbrivation
							
							for (y in fs.transcriptMeta['interviewers'].split(",")){
							
								if (fs.transcriptMeta['interviewers'].split(",")[y].search(loopAry[x].speaker) != -1){
									title = fs.transcriptMeta['interviewers'].split(",")[y] + " (Interviewer)";
								}
								
							}
							
						}else{
							
 							
	
							for (y in fs.transcriptMeta['interviewers'].split(",")){
								
								var abb = fs.transcriptMeta['interviewers'].split(",")[y].replace("  "," ").trim().split(" ");
								
 								
								if (abb.length == 2){
								
									abb = abb[0][0] + abb[1][0];
									
									if (abb == loopAry[x].speaker){
										
										title = fs.transcriptMeta['interviewers'].split(",")[y] + " (Interviewer)";
										
									}
									
	
									
								}
								
							}
							
							
							
						}
				}
				
				if (loopAry[x].type=='Q' && loopAry[x].speaker == ''){
					
					
					if (fs.transcriptMeta['interviewers'].search(",") == -1){
						
						title = fs.transcriptMeta['interviewers'] + " (Interviewer)";
							
					}
					
					
					
					
				}		
						
				if (loopAry[x].type=='Q' &&  fs.transcriptMeta['interviewers'].search(",") == -1){
						
					title = fs.transcriptMeta['interviewers'] + " (Interviewer)";
							
				}
				

				var useText = fs.markupText(fs.activeName,loopAry[x].text);
				

					
				domObj
					.append(
						$("<div>")
							.addClass(imageHolderClass)
							.append(
								$("<img>")
									.attr("src",useImage)
							)
							.append(
								$("<div>")
									.text(title)
							)							
							
					).append(
						$("<div>")
							.addClass('bubble')
							.addClass(bubbleClass)
							.html(useText)						
					)
					.append(
						$("<br>")
							.css("clear", (useText.length < 100) ? "both" : "none")						
					);			
				
				
			}
			
			return domObj;
			
			  
		}
		
		
		fs.addComment = function(){
		
			
			
			var postObj = {}
			
			postObj.transcript =  this.transcriptMeta.md5;
			postObj.interviewee = this.transcriptMeta.intervieweeURI;
			postObj.mention = this.activeURI;
			postObj.comment = $("#addCommentText").val();
			postObj.pairs = [];
			
			
			for ( x in this.activePairs[this.activePairIndex]){
			


				if (typeof this.activePairs[this.activePairIndex][x] == "object"){
				
					postObj.pairs.push(this.activePairs[this.activePairIndex][x].idLocal);
				
					
				}
				
			}
			
			
			$.post("/52new/work/?action=storeComment", postObj, function(data){					

			
				
			});			
			
			
			
			$("#addCommentText").val("");
			$("#addCommentCancel").click();
			
			
			
		}
		
		
		
		fs.requestText = function(transcript, id){
			$.get("/52new/work/", { action: 'returnTextById', transcript: fs.transcriptMeta['md5'], id: id },function(data){			
				return data;				
			});
		}
		
		fs.requestMatches = function(){
			
			fs.activePoints = 0;
			$("#tutorialFinishedAll").css("display","none");
			$("#tutorialFinishedAll").popover('hide');			
			
			
			$.get("/52new/work/", { action: 'returnMatchesForURI', transcript: fs.transcriptMeta['md5'], uri: fs.activeURI },function(data){
				
				fs.working=false; 
				var pairs = [];
				
				//we have all the occurances of this name now, they can be answers or questions, build the pairs of questions/answers by grabbing their corrosponding pair
				for (aMatch in data){
					
					var aPair = {};
					
					aPair.question = null; 
					aPair.answer = null;
					aPair.points = 1;
					
					if (data[aMatch].type == 'A'){
						
						var lookingForId = parseInt(data[aMatch].idLocal) - 1;
						if (lookingForId<0){lookingForId=0}
						
						aPair.answer = data[aMatch]
						
						//make sure we do not have the corosponding question already
						for (questionSearch in data){	
							if (data[questionSearch].idLocal==lookingForId){
								aPair.question=data[questionSearch];
								aPair.points = 2;
							}
						}
						
						
						//did we find them both?
						if (aPair.question==null||aPair.answer == null){						
							//no, mark this part as one that needs to be retived beblow
							aPair.question = lookingForId
						}
						pairs.push(aPair);
						
					}
					
					if (data[aMatch].type == 'Q'){
						
						var lookingForId = parseInt(data[aMatch].idLocal) + 1;
 						
						aPair.question = data[aMatch]
						
						//make sure we do not have the corosponding question already
						for (answerSearch in data){	
							if (data[answerSearch].idLocal==lookingForId){
								aPair.answer=data[answerSearch];
								aPair.points = 2;
							}
						}
						
						
						//did we find them both?
						if (aPair.question==null||aPair.answer == null){						
							//no, mark this part as one that needs to be retived beblow
							aPair.answer = lookingForId
						}
						pairs.push(aPair);
						
					}					
					 
					
					
				}
				var foundNull = false;
				
 				for (x in pairs){
										
					if (typeof pairs[x].question == 'number'){	
						foundNull = true;					
						$.get("/52new/work/", { action: 'returnTextById', transcript: fs.transcriptMeta['md5'], id: pairs[x].question },function(data){
							
							//what was the id of the question we are looking for
							var questionIdex = parseInt(this.url.split('&id=')[1]);
							
							for (y in pairs){
								if 	(pairs[y].question==questionIdex){
									pairs[y].question=data; 	
								}
							}
							
							//since this whole mess is running async check if we are done and kick off the next part
							var done = true;
							for (y in pairs){
								if (pairs[y].question==null||pairs[y].answer == null){
									done = false;
								}
							}
							
							if (done==true&&fs.working==false){
								fs.working=true;
								fs.activePairs = pairs;
								fs.activePairIndex = 0;								
								fs.buildTranscript();								
							}
							
						});
						 
						
					}		
					
					if (typeof pairs[x].answer == 'number'){	
						foundNull = true;					
						$.get("/52new/work/", { action: 'returnTextById', transcript: fs.transcriptMeta['md5'], id: pairs[x].answer },function(data){
							
							//what was the id of the question we are looking for
							var answerIdex = parseInt(this.url.split('&id=')[1]);
 							for (y in pairs){
								if 	(pairs[y].answer==answerIdex){
									pairs[y].answer=data; 	
								}
							}
							
							//since this whole mess is running async check if we are done and kick off the next part
							var done = true;
							for (y in pairs){
								if (pairs[y].question==null||pairs[y].answer == null){
									done = false;
								}
							}
							
							if (done==true&&fs.working==false){
								fs.working=true;
								fs.activePairs = pairs;
								fs.activePairIndex = 0;								
								fs.buildTranscript();								
							}
							
						});
						 
						
					}							
					
					
				}				
				
				
				//we found all the needed parts of the pairs within the intial request
				if(!foundNull){
					fs.working=true;
					fs.activePairs = pairs;
					fs.activePairIndex = 0;						
					fs.buildTranscript();						
				}
				
 				
				
				
				
				
			}).error(function(data) { 
					alert("There was an error connecting to the server, please try again.");
			});
			
			

			
		}
	
	

		$(document).ready(function($) {
			
			
			
			
			
			
			$(".matchListItemLink").bind('click',function(event){			
			
				if(fs.showTutorial){
					event.preventDefault();
					return false;
				}	
				
				
				//remove other active markers
				$(".matchListItem").removeClass("matchListItemActive");
				
				//add active marker to this one
				$(this).children().first().addClass("matchListItemActive");
				
				fs.activeName = $(this).data('name')
				fs.activeURI = $(this).data('uri')				
				fs.requestMatches();
				
				
				var useImage = $(this).data('image');
				if (useImage==''){
					useImage = "/52new/img/no_image_square.png";	
					$("#scoreBoard img:last").css('opacity',0.5);	
				}else{
					useImage = "/image/square/" + useImage;
					$("#scoreBoard img:last").css('opacity',1);						
				}
				
				
				
				
				$("#scoreBoard img:first").attr('src',"/image/square/" + fs.intervieweeImage + '.png');
				$("#scoreBoard img:last").attr('src',useImage);				
				$("#scoreBoardInterviewee").text(fs.transcriptMeta['interviewee']);
				$("#scoreBoardMention").text($(this).data('name'));				
				$("#scoreBoard").css("visibility","visible");
				
				//Update all the buttons.....
				var popover = $('.btnKnows').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnKnows').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);	
				

				var popover = $('.btnHasMet').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnHasMet').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);					
				
				var popover = $('.btnAcquaintance').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnAcquaintance').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);						
				
				var popover = $('.btnCloseFriend').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnCloseFriend').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);	

				var popover = $('.btnCollaborated').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnCollaborated').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);								
				
				var popover = $('.btnInfluenced').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnInfluenced').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);						
							
				var popover = $('.btnMentor').data('popover');
 				var content = popover.options.contentOld;
				content = content.replace("<interviewee>",interviewee);
				content = content.replace("<person>",$(this).data('name'));
				$('.btnMentor').attr('data-content', content);
				popover.setContent();
    			popover.$tip.addClass(popover.options.placement);					
				
				
				
				event.preventDefault();
				return false;				
				
			});
			
			$("#transcriptHolderMoreDownAction").bind('click',function(event){
				if(fs.showTutorial){
					event.preventDefault();
					return false;
				}			
				
				//Only show this popover the first time, afterwards it is annoying
				if ($(this).parent().attr("rel")=="popover"){
					$("#transcriptHolderMoreUpAction,#transcriptHolderMoreDownAction").parent().attr("rel","popoverOld")	
					$("[rel=popoverOld]").popover('hide');
					$("[rel=popoverOld]").popover('disable');				
				}	
								
				fs.addBubble(fs.idLocalBottom+1);
				event.preventDefault();
				return false;						 
			});
			
			$("#transcriptHolderMoreUpAction").bind('click',function(event){
				if(fs.showTutorial){
					event.preventDefault();
					return false;
				}
				
				//Only show this popover the first time, afterwards it is annoying
				if ($(this).parent().attr("rel")=="popover"){
					$("#transcriptHolderMoreUpAction,#transcriptHolderMoreDownAction").parent().attr("rel","popoverOld")	
					$("[rel=popoverOld]").popover('hide');
					$("[rel=popoverOld]").popover('disable');				
				}
								
				fs.addBubble(fs.idLocalTop-1);
				event.preventDefault();
				return false;						 
			});			
			
			
			$(".relButton, .relButtonLink").bind('click',function(event){
				
				if(fs.showTutorial){
					event.preventDefault();
					return false;
				}
				
 				$("#colabDropdown").removeClass("open");
				
				fs.storeRelationship($(this));
				
				//event.preventDefault();
				//return false;					
				
			});
			
		
			$("#addPrivateComment").bind('click',function(event){
				
				
				fs.addComment();
				
				//event.preventDefault();
				//return false;					
				
			});			
			
			
						
			$(document).keydown(function(e){
				switch(e.which) {
					case 38: // up
						$("#transcriptHolderMoreUpAction").click();
						e.preventDefault();
						break;
			
			
					case 40: // down
						$("#transcriptHolderMoreDownAction").click();
						e.preventDefault();
						break;
			
					default: return; // exit this handler for other keys
				}
				
			});			
			 
			
			$(window).resize(function () { 
				
				//set the height on the text display to the max
				$("#matchList").css("height",$(window).height() - 80 + 'px');	
				$("#transcriptHolder").css("height",$(window).height() - 105 + 'px');	
				
				$("#viz").css("width",$("#rightBar").outerWidth(true) + ($("#rightBar").outerWidth(true)*0.20) + 'px');				
				$("#viz").css("height",$(window).height() - $("#progressBar").offset().top - $("#progressBar").height() - 10 + 'px');
				
				//keep the comment button and colab dropdown synced up
				$("#commentModalOpenButton").css("width",$("#relButtonSkip").css("width"))
				$("#colabDropdown").css("width",$("#relButtonText").css("width"))
				

			
			});
			
			$(window).resize();
			$('[rel="popover"]').popover({ html : true });
			
			
			
			
			//start it
			if (!fs.showTutorial){
				fs.updatePeopleList();
				fs.updatePercentComplete();
				fs.gotoNextPerson();
	
			}else{
				
				fs.tutorial(0);
				
			}
			
			if (fs.hasSVG){
				fs.viz.init();	
			}else{
				$("#vizAbout").css("display","none");
				$("#viz").css("display","none");					
			}
			
			
		})	
		
		
		if(!document.createElementNS || !document.createElementNS('http://www.w3.org/2000/svg','svg').createSVGRect){
			fs.hasSVG = false;
		}else{
			$.getScript("/52new/js/d3.v2.min.js");
			fs.hasSVG = true;			
		}
		
	
	</script>
    
    
    <script>
	
		fs.transcriptMeta = <? echo json_encode($transcriptMeta); ?>;
		fs.intervieweeImage = fs.transcriptMeta.intervieweeURI.split('/resource/')[1].replace('>','');
		fs.pointsTotal = parseInt(fs.transcriptMeta.totalPairs);
		
		fs.pointsEarned  = <?=$pointerEarned?>;
		<?=$allPeopleJS?>
		<?=$completedPeopleJS?>
		
		
		<?=$isNoob?>
		
		
	
	</script>
    <style>
	
		body{
			background-image:url(/52new/img/bgnoise.png);
			background-repeat:repeat;
		}
		
		hr {
			margin-top:8px;
			margin-bottom:8px;
		}
		
		#matchList{
			overflow:auto;
			position:relative; 			
			
		}
		
		#matchListTitle{
			font-size:16px;
			text-align:center;
			display:none;
		}
		.matchListItem{
			width:95%;
			margin:12px auto 12px auto;
			position:relative; 
		
			
			border-radius: 6px;	
			-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.9);
			-moz-box-shadow: 0 1px 2px rgba(0,0,0,.9);
			box-shadow: 0 1px 2px rgba(0,0,0,.9);	
			
			background-color: #F8F8F8;
			background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#F8F8F8), to(#fff));
			
			background: -moz-linear-gradient(top, #F8F8F8, #fff);
			background: -ms-linear-gradient(top, #F8F8F8, #fff);
			background: -o-linear-gradient(top, #F8F8F8, #fff);						
			
		}
		.matchListItemDone{
			background-color: #B6BFFF;
			background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#B6BFFF), to(#fff));			
			background: -moz-linear-gradient(top, #B6BFFF, #fff);
			background: -ms-linear-gradient(top, #B6BFFF, #fff);
			background: -o-linear-gradient(top, #B6BFFF, #fff);				
		}
		.matchListItemActive{
			background-color: #FAFF86;
			background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#FAFF86), to(#fff));			
			background: -moz-linear-gradient(top, #FAFF86, #fff);
			background: -ms-linear-gradient(top, #FAFF86, #fff);
			background: -o-linear-gradient(top, #FAFF86, #fff);				
		}		
		
		
		
		
		.matchListItem img{
			width:50px;
			height:auto;
			border: 1px solid #D1D1D1;
			border-radius: 35px;			
			
		}
		.matchListItem div{
			position:absolute;
			left:50px;
			top:15px; 
		 
			padding-left:5px;
			
		}
		.matchListItemLink{
			color:#333;
		}
	
	
		#transcriptHolder{
			border:1px solid #CCC;
			border-bottom:none;
			border-top:none;
			overflow:auto;
			position:relative;
			padding:10px;
			padding-top:25px;
			box-shadow:inset 0px 0px 85px rgba(0,0,0,.05);
			-webkit-box-shadow:inset 0px 0px 85px rgba(0,0,0,.05);
			-moz-box-shadow:inset 0px 0px 85px rgba(0,0,0,.05);			
		}
		
		#linkTranscriptSource{
			font-size:14px;
			position:absolute;
			min-width:115px;
			right:0px;
			top:48px;
		}
		
		#transcriptHolderMoreUp{
			position:fixed;
			left:45%;
			width:4%;
			top:66px; 
			text-align:center;
			z-index:100;
			
			
		}
		#transcriptHolderMoreUp div{ 			
				background-color:#F8F8F8;
				opacity:0.5;
 		        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.9);
        	    -moz-box-shadow: 0 1px 2px rgba(0,0,0,.9);
                box-shadow: 0 1px 2px rgba(0,0,0,.9);				
			
		}
		#transcriptHolderMoreDown{
			position:fixed;
			left:45%;
			width:4%;
			bottom:3px;
			text-align:center; 
			z-index:100;			
 
			
		}
		#transcriptHolderMoreDown div{
 				background-color:#F8F8F8;
				opacity:0.5;
				display:block;
				-webkit-box-shadow: 0px -1px 2px rgba(0, 0, 0, 0.9);
				-moz-box-shadow:    0px -1px 2px rgba(0, 0, 0, 0.9);
				box-shadow:         0px -1px 2px rgba(0, 0, 0, 0.9);			
		}				
					
					
					
		#headerHolder{
			float:left;
 			height:65px;
			width:99%; 
		}
		
		
		#logoHolder{
			height:65px; 
		}
		#logoHolder img{
			padding-top:5px;
		}
		
		
		#scoreBoard{
			height:61px;
 			padding:2px;
 			overflow:hidden;
			visibility:hidden;
			

		}
		
		#scoreBoardProgress{
			position:absolute;
			right:37%;
			top:30px; 
			
		}
		#scoreBoardProgress h5{
			font-size:11px;
			margin-top:15px;
			
		}
		#scoreBoard div{
 			color:#666; 
		}
		#scoreBoard img{
			height:60px;
			width:60px;;
			border:solid 1px #666;
			border-radius: 4px;  
		
		}
		
		#scoreBoardMention{
			font-style:oblique;
			font-size:110%;
		}
		
		
		
		.highlight{	
			/*background-color:#EEEED3;*/
			text-shadow: 2px 2px 1px #e0f052;
	        filter: dropshadow(color=#e0f052, offx=2, offy=2);
			
			
		}
		
		
		#relButtonText{
			text-align:center;
			margin-bottom:10px;
			font-size:16px;
		}
			
		.relButton{
			width:100%;
			margin-bottom:5px;
			
			
		}
		
		#rightBar{
			
			padding-right:5px; position:relative;
			
		}
		
		
		
		.btnKnows{
			  background-color: hsl(201, 13%, 37%) !important;
			  background-repeat: repeat-x;
			  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#83959f", endColorstr="#52626a");
			  background-image: -khtml-gradient(linear, left top, left bottom, from(#83959f), to(#52626a));
			  background-image: -moz-linear-gradient(top, #83959f, #52626a);
			  background-image: -ms-linear-gradient(top, #83959f, #52626a);
			  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #83959f), color-stop(100%, #52626a));
			  background-image: -webkit-linear-gradient(top, #83959f, #52626a);
			  background-image: -o-linear-gradient(top, #83959f, #52626a);
			  background-image: linear-gradient(#83959f, #52626a);
			  border-color: #52626a #52626a hsl(201, 13%, 32%);
			  color: #fff !important;
			  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
			  -webkit-font-smoothing: antialiased;			
		}
		.btnHasMet{		
		  background-color: hsl(201, 34%, 37%) !important;
		  background-repeat: repeat-x;
		  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#6c9cb6", endColorstr="#3e677e");
		  background-image: -khtml-gradient(linear, left top, left bottom, from(#6c9cb6), to(#3e677e));
		  background-image: -moz-linear-gradient(top, #6c9cb6, #3e677e);
		  background-image: -ms-linear-gradient(top, #6c9cb6, #3e677e);
		  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #6c9cb6), color-stop(100%, #3e677e));
		  background-image: -webkit-linear-gradient(top, #6c9cb6, #3e677e);
		  background-image: -o-linear-gradient(top, #6c9cb6, #3e677e);
		  background-image: linear-gradient(#6c9cb6, #3e677e);
		  border-color: #3e677e #3e677e hsl(201, 34%, 32%);
		  color: #fff !important;
		  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
		  -webkit-font-smoothing: antialiased;
		}			
		
		.btnAcquaintance{
		  background-color: hsl(201, 62%, 37%) !important;
		  background-repeat: repeat-x;
		  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#4da5d5", endColorstr="#236f98");
		  background-image: -khtml-gradient(linear, left top, left bottom, from(#4da5d5), to(#236f98));
		  background-image: -moz-linear-gradient(top, #4da5d5, #236f98);
		  background-image: -ms-linear-gradient(top, #4da5d5, #236f98);
		  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #4da5d5), color-stop(100%, #236f98));
		  background-image: -webkit-linear-gradient(top, #4da5d5, #236f98);
		  background-image: -o-linear-gradient(top, #4da5d5, #236f98);
		  background-image: linear-gradient(#4da5d5, #236f98);
		  border-color: #236f98 #236f98 hsl(201, 62%, 32%);
		  color: #fff !important;
		  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
		  -webkit-font-smoothing: antialiased;			
		}
		
		.btnCloseFriend {
		  background-color: hsl(201, 93%, 37%) !important;
		  background-repeat: repeat-x;
		  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#2baff7", endColorstr="#0678b6");
		  background-image: -khtml-gradient(linear, left top, left bottom, from(#2baff7), to(#0678b6));
		  background-image: -moz-linear-gradient(top, #2baff7, #0678b6);
		  background-image: -ms-linear-gradient(top, #2baff7, #0678b6);
		  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #2baff7), color-stop(100%, #0678b6));
		  background-image: -webkit-linear-gradient(top, #2baff7, #0678b6);
		  background-image: -o-linear-gradient(top, #2baff7, #0678b6);
		  background-image: linear-gradient(#2baff7, #0678b6);
		  border-color: #0678b6 #0678b6 hsl(201, 93%, 32%);
		  color: #fff !important;
		  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
		  -webkit-font-smoothing: antialiased;
		}		
		
		.btnCollaborated {
		  background-color: hsl(29, 13%, 37%) !important;
		  background-repeat: repeat-x;
		  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#9f9083", endColorstr="#6a5d52");
		  background-image: -khtml-gradient(linear, left top, left bottom, from(#9f9083), to(#6a5d52));
		  background-image: -moz-linear-gradient(top, #9f9083, #6a5d52);
		  background-image: -ms-linear-gradient(top, #9f9083, #6a5d52);
		  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #9f9083), color-stop(100%, #6a5d52));
		  background-image: -webkit-linear-gradient(top, #9f9083, #6a5d52);
		  background-image: -o-linear-gradient(top, #9f9083, #6a5d52);
		  background-image: linear-gradient(#9f9083, #6a5d52);
		  border-color: #6a5d52 #6a5d52 hsl(29, 13%, 32%);
		  color: #fff !important;
		  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
		  -webkit-font-smoothing: antialiased;
		}
		
				
				
		.btnInfluenced {
		  background-color: hsl(29, 39%, 37%) !important;
		  background-repeat: repeat-x;
		  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#bc8f66", endColorstr="#835d39");
		  background-image: -khtml-gradient(linear, left top, left bottom, from(#bc8f66), to(#835d39));
		  background-image: -moz-linear-gradient(top, #bc8f66, #835d39);
		  background-image: -ms-linear-gradient(top, #bc8f66, #835d39);
		  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #bc8f66), color-stop(100%, #835d39));
		  background-image: -webkit-linear-gradient(top, #bc8f66, #835d39);
		  background-image: -o-linear-gradient(top, #bc8f66, #835d39);
		  background-image: linear-gradient(#bc8f66, #835d39);
		  border-color: #835d39 #835d39 hsl(29, 39%, 32%);
		  color: #fff !important;
		  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
		  -webkit-font-smoothing: antialiased;
		}
		
		.btnMentor {
		  background-color: hsl(29, 77%, 37%) !important;
		  background-repeat: repeat-x;
		  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#e58e3c", endColorstr="#a65b15");
		  background-image: -khtml-gradient(linear, left top, left bottom, from(#e58e3c), to(#a65b15));
		  background-image: -moz-linear-gradient(top, #e58e3c, #a65b15);
		  background-image: -ms-linear-gradient(top, #e58e3c, #a65b15);
		  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #e58e3c), color-stop(100%, #a65b15));
		  background-image: -webkit-linear-gradient(top, #e58e3c, #a65b15);
		  background-image: -o-linear-gradient(top, #e58e3c, #a65b15);
		  background-image: linear-gradient(#e58e3c, #a65b15);
		  border-color: #a65b15 #a65b15 hsl(29, 77%, 32%);
		  color: #fff !important;
		  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.33);
		  -webkit-font-smoothing: antialiased;
		}
			
			
		#viz{
			position:absolute;
			right:0px;
			bottom:5px;
			height:50px;
			
		}
		
		
		/* viz svg syles */
		
		.vizroot{
			fill:white;
			stroke:black;
			stroke-width:2px;			
		}
		.vizknows{
			fill:#52626B;			
		}
		.vizhasMet{
			fill:#3E687E;			
		}		
		
		.vizacquaintance{
			fill:#247099;		
		}
		
		.vizfriend{
			fill:#0779B6;		
		}		
		.vizcollaborated{
			fill:#6B5E52;		
		}		
		
		.vizcollaboratedPlayedTogether{
			fill:#6B5E52;		
		}		
		.vizcollaboratedTouredWith{
			fill:#6B5E52;		
		}		
		.vizcollaboratedInBandTogether{
			fill:#6B5E52;		
		}		
		
		.vizcollaboratedWasBandleader{
			fill:#6B5E52;		
		}		
		.vizcollaboratedWasBandmember{
			fill:#6B5E52;		
		}		
				
		.vizinfluenced{
			fill:#835D3A;		
		}		
		.vizmentor{
			fill:#A75C16;		
		}		
 
		.link{
			stroke:#999;
		}
				
		#vizAbout{
			position:absolute;
			right:0px;
			bottom:5px;
			font-size:10px;
			margin-right:15%;
			z-index:100;
			
		}
		
		/*tutorial styles */
		
		.tutorial{
			position:absolute;
			width:100px;
			height:100px;
			background-color:#F8F8F8;
			border:1px soild #666;
			opacity:0.95;
			border-radius:10px;
			-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.9);
			-moz-box-shadow: 0 1px 2px rgba(0,0,0,.9);
			box-shadow: 0 1px 2px rgba(0,0,0,.9);
			display:none;							
		}
		
		#tutorialStart{			
			top:40%;
			left:30%;
		}
		
		#tutorialStartContentArea{
			top:40%;
			left:40%;		
			
		}
		
		#tutorialStartDone, #tutorialFinishedAll{
			top:40%;
			left:35%;				
		}
		
		
		
		/*bubble chat */
		.bubble{
			background-color: #F2F2F2;
			border-radius: 5px;
			box-shadow: 0 0 6px #B2B2B2;
			display: inline-block;
			padding: 10px 18px;
			position: relative;
			vertical-align: top;
		}
		
		.bubble::before {
			background-color: #F2F2F2;
			content: "\00a0";
			display: block;
			height: 16px;
			position: absolute;
			top: 11px;
			transform:             rotate( 29deg ) skew( -35deg );
				-moz-transform:    rotate( 29deg ) skew( -35deg );
				-ms-transform:     rotate( 29deg ) skew( -35deg );
				-o-transform:      rotate( 29deg ) skew( -35deg );
				-webkit-transform: rotate( 29deg ) skew( -35deg );
			width:  20px;
		}
		
		
		
		
		.question {
			float: left;
			max-width:71%;   
			margin: 5px 45px 15px 20px;         
		}
	
		
		.question::before {
			box-shadow: -2px 2px 2px 0 rgba( 178, 178, 178, .4 );
			left: -9px;           
		}
		
		
		.questionImage{
			display:inline-block; 
			width:10%; 
			float:left;
		}
		
		.questionImage img{
			height:auto;
			width:auto;
			border:solid 1px #666;
			border-radius:4px;
			opacity:0.45;
					
		}	
			
		.questionImage div{
 			text-align:center;
			
		}		
		
		
		.answer {
			float: right;    
			max-width:71%;
			margin: 5px 20px 15px 45px;         
		}
		
		.answer::before {
			box-shadow: 2px -2px 2px 0 rgba( 178, 178, 178, .4 );
			right: -9px;    
		}		
		  
		
		.answerImage{
			display:inline-block; 
			width:10%; 
			float:right;
		}
		
		
		.answerImage img{
			height:auto;
			width:auto;
			border:solid 1px #666;
			border-radius:4px;
		}
		
		
		 
		 
		 
		
		
		
		.answerInterviewees {
			float: right;    
			max-width:65%;
			margin: 20px 20px 15px 45px;         
		}
		
		.answerInterviewees::before {
			box-shadow: 2px -2px 2px 0 rgba( 178, 178, 178, .4 );
			right: -9px;    
		}		
		  
		
		.answerIntervieweesImage{
			display:inline-block; 
			width:10%; 
			float:right;
			margin:20px;
		}

		
		.answerIntervieweesImage div{
 			text-align:center;
			
		}			
		
		.answerIntervieweesImage img{
			height:auto;
			width:auto;
			border:solid 1px #666;
			border-radius:4px;
			opacity:0.65;
					
		}
		
		.answerIntervieweesImage img{
			height:auto;
			width:auto;
			border:solid 1px #666;
			border-radius:4px;
		}
				

		#colabDropdown button{
			width:100%;
			text-align:left;
			
			
		}
		#colabDropdown button:hover{
		  color: #ffffff !important;
		  text-decoration: none;
		  background-color: #0088cc;
		  background-color: #0081c2;
		  background-image: -moz-linear-gradient(top, #0088cc, #0077b3);
		  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0088cc), to(#0077b3));
		  background-image: -webkit-linear-gradient(top, #0088cc, #0077b3);
		  background-image: -o-linear-gradient(top, #0088cc, #0077b3);
		  background-image: linear-gradient(to bottom, #0088cc, #0077b3);
		  background-repeat: repeat-x;
		  filter: progid:dximagetransform.microsoft.gradient(startColorstr='#ff0088cc', endColorstr='#ff0077b3', GradientType=0);
		} 		
		
		
		/*scroll bar*/	
			
		::-webkit-scrollbar {
			width: 10px;
			height: 6px;
		}
		
		::-webkit-scrollbar-button:start:decrement,
		::-webkit-scrollbar-button:end:increment {
			height: 30px;
			display: block;
			background-color: transparent;
		}
		
		::-webkit-scrollbar-track-piece {
			background-color: #E7E7E7;
			-webkit-border-radius: 6px;
		}
		
		::-webkit-scrollbar-thumb:vertical {
			height: 50px;
			background-color: #999;
			-webkit-border-radius: 6px;
		}
		
		::-webkit-scrollbar-thumb:horizontal {
			width: 50px;
			background-color: #666;
			-webkit-border-radius: 3px;
		}	
	
	</style>
    
    
    
</head>



<body>


    <div id="workPageFeedback"><a href="https://docs.google.com/forms/d/1pW3dQv0eYcA1ZRVhEHLaYOpZZTePmQ-_re1abjW4Q-Q/viewform">Feedback&nbsp;<img src="/52ndStreet/img/feeback.png"></a></div>


    <div class="container-fluid">
    	
      <div class="row-fluid">
      	<div class="span2" style="border-bottom:1px solid #CCC">
          <div id="logoHolder" rel="popover" data-value="skip" data-title="Back to Start Page" data-placement="bottom" data-html="true" data-trigger="manual" data-content="If you want to work on another musician or return to the start page just click the Linked Jazz logo. Don't worry you progress is continually saved, you can come back and start working where you left off at anytime.<br><br><button class='btn tutorialNext btn-primary'>Next</button>">
	          <a href="/52new/" title="Return to homepage, select diffrent musican" alt="Return to homepage, select diffrent musican"><img src="/52new/img/lj_52_logo.png"/></a>
			  <div id="matchListTitle"><? echo count($matchList) . " People To Reivew"; ?></div>
          </div>        
        </div>
        
        <div class="span8" style="padding:0px; margin:0px; border-bottom:1px solid #CCC">
	    
        
        	<div class="row-fluid">
	    	  	<div class="span2">        
            
    	        </div>
	    	  	<div class="span8">        
                	<div id="scoreBoard">
                    	
                        <div class="row-fluid">
                            <div class="span2">                         
                            	<img class="pull-left" src="/image/square/Charles_Davis_(saxophonist).png">
                            </div>
                            <div class="span8" style="text-align:center">   
                            	<h4><span id="scoreBoardInterviewee"></span> talks about <span id="scoreBoardMention"></span></h4>                      
                                <div id="scoreBoardProgress"><h5></h5></div>
                            </div>
                            <div class="span2">                         
                            <img class="pull-right" src="/image/square/Charles_Davis_(saxophonist).png">
                            </div>
						</div>                                                                                    
                    </div>
    	        </div>        	
	    	  	<div class="span2" style="position:relative; white-space: nowrap;">        
            	
                	<?
						if ($transcriptMeta['interviewees'] != ''){
							
							$interviewees = " ( " . $transcriptMeta['interviewees'] . " also participated in the interview) ";
						
								
						}else{
							$interviewees = "";
						}
					
					?>
                
                    <a href="<?=$transcriptMeta['sourceURL']?>" id="linkTranscriptSource" target="_blank" rel="popover" data-value="skip" data-title="Transcript Source" data-placement="bottom" data-trigger="hover" data-content="The text used for this application comes from the <? echo str_replace('_',' ',$transcriptMeta['sourceName']);?>, <?=$transcriptMeta['interviewee']?><?=$interviewees?> was interviewed by <?=$transcriptMeta['interviewers']?>.">Transcript&nbsp;Source</a>
            
    	        </div>                
        	</div>
            
        </div>
        
      	<div class="span2" style="position:absolute; right:4%; min-width:200px;">
        
        	<? include('app/html/accountTab.php'); ?>
        
        </div>        
      
      </div>
    
    
    
      <div class="row-fluid">
       
        <div class="span2">
          <div id="matchList" rel="popover"  data-title="Names" data-placement="right" data-html="true" data-trigger="manual" data-content="These names are people that were found in the interview transcript, meaning the musician talked about them. You can jump around in the list and click a name to begin to work on that person. Otherwise the system will queue up the people for you and display them as you finish. Blue colored names mean that you have finished working on that person. The yellow color is the active person.<br><br><button class='btn tutorialNext btn-primary'>Next</button>">
			  <?
                //build the name list
                foreach ($matchList as $aPerson){
                
                    if ($aPerson['image']==''){
                        $image = '/52new/img/no_image.png';	
						$opacity = 0.75;						
                    }else{					
                        $image = "/image/round/" . $aPerson['image'];
						$opacity = 1;					
                    }
                
                
                    ?>
                    
                    	<a href="#" class="matchListItemLink" data-uri="<?=$aPerson['uri']?>" data-image="<?=$aPerson['image']?>" data-name="<?=$aPerson['name']?>"  >
                            <div class="matchListItem">
                                <img src="<?=$image?>" style="opacity:<?=$opacity?>">
                                <div><?=$aPerson['name']?></div>
                            </div>
                        </a>


                    
                    <?
                    
                }
              
              ?>
          </div>
          
          
        </div>
        <div class="span8" style="padding:0px; margin:0px;">
        
          <div id="transcriptHolder">
          	<div id="transcriptHolderMoreUp" class="transcriptMoreButtons" rel="popover" data-value="skip" data-title="More Context Button" data-placement="bottom" data-html="true" data-trigger="manual" data-content="If you need more context, more of the transcript text, to figure out the relationship click here to see previous questions and answers. You can click as many times as needed to see more text from the interview.<br><br><button class='btn tutorialNext btn-primary'>Next</button>">
            	<div rel="popover" data-title="" data-placement="bottom" data-trigger="hover" data-content="Click to show what was said before. You can also use the UP ARROW keyboard key.">
                	<a href="#" id="transcriptHolderMoreUpAction"><div><i class="icon-chevron-up"></i></div></a>
            	</div>
            </div>
          	<div id="transcriptHolderMoreDown" class="transcriptMoreButtons" rel="popover" data-value="skip" data-title="More Context Button" data-placement="top" data-html="true" data-trigger="manual" data-content="This button does the same as above, only it shows the next question/answer.<br><br><button class='btn tutorialNext btn-primary'>Next</button>">
            	<div rel="popover" data-title="" data-placement="top" data-trigger="hover" data-content="Click to show what was said after. You can also use the DOWN ARROW keyboard key.">
                	<a href="#" id="transcriptHolderMoreDownAction"><div><i class="icon-chevron-down"></i></div></a>
                </div>
            </div>                        
          	<div id="transcriptContent">
           
 			</div>
          
          </div>
          

          
        </div>
        <div class="span2" id="rightBar">
		
        	<div id="relButtonText">
            Based on this text, how would you describe this relationship?
            </div>
            <div id="relButtonHolder" rel="popover" data-value="skip" data-title="Relationship Buttons" data-placement="left" data-html="true" data-trigger="manual" data-content="Once you have read the transcript text you decide on what category best describes the two people's relationship. If you move you mouse over the buttons it will give you hints about what each category means. One you pick the system will queue up the next occurrence of the person's name or move on to the next individual.<br><br><button class='btn tutorialNext btn-primary'>Next</button>">
            	<button class="relButton btn btnKnows" rel="popover" data-value="knows" data-title="Knowns:" data-placement="left" data-trigger="hover" data-content="A person known by <?=$transcriptMeta['interviewee']?> (indicating some level of reciprocated interaction between the parties). This is the most basic level of relationship."  data-content-old='<interviewee> knows <person> (indicating some level of reciprocated interaction between the parties). This is the most basic level of relationship.'>Knows of</button>
				<button class="relButton btn btnHasMet" rel="popover" data-value="hasMet" data-title="Has Met:" data-placement="left" data-trigger="hover" data-content="A person who has met <?=$transcriptMeta['interviewee']?> whether in passing or longer." data-content-old="<interviewee> has met <person> whether in passing or longer.">Has met</button>                
				<button class="relButton btn btnAcquaintance" rel="popover" data-value="acquaintance" data-title="Acquaintance Of:" data-placement="left" data-trigger="hover" data-content="A person having more than slight or superficial knowledge of <?=$transcriptMeta['interviewee']?> but short of friendship." data-content-old="<interviewee> has more than slight or superficial knowledge of <person> but short of friendship.">Acquaintance of</button>                                
				<button class="relButton btn btnCloseFriend" rel="popover" data-value="friend" data-title="Close Friend Of:" data-placement="left" data-trigger="hover" data-content="A person who shares a close mutual friendship with <?=$transcriptMeta['interviewee']?>." data-content-old="<interviewee> shares a close mutual friendship with <person>.">Close friend of</button>                                                
				<!--<button class="relButton btn btnCollaborated" rel="popover" data-value="collaborated" data-title="Collaborated:" data-placement="left" data-trigger="hover" data-content="A person who has worked together with <?=$transcriptMeta['interviewee']?>. Click the button and select a more detailed detailed describption of their collaboration.">Collaborated with</button>
    			-->
                
				<div class="btn-group">
                <button class="btn btnCollaborated dropdown-toggle" style="width:100%; margin-bottom:5px" data-toggle="dropdown"  rel="popover" data-value="collaborated" data-title="Collaborated:" data-placement="left" data-trigger="hover" data-content-old="<interviewee> has worked together with <person>. Click the button and select a more detailed detailed describption of their collaboration." data-content="A person who has worked together with <?=$transcriptMeta['interviewee']?>. Click the button and select a more detailed detailed describption of their collaboration.">Collaborated with <span class="caret" style="border-top-color:#fff; border-bottom-color:#fff;"></span></button>
                <ul id="colabDropdown" class="dropdown-menu">
                  <li><button class="relButtonLink btn-link" data-value="collaboratedPlayedTogether" style="color:#333; text-decoration:none;" href="#">Played together</button></li> 
                  <li><button class="relButtonLink btn-link" data-value="collaboratedTouredWith" style="color:#333; text-decoration:none;" href="#">Toured with</button></li>
                  <li><button class="relButtonLink btn-link" data-value="collaboratedInBandTogether" style="color:#333; text-decoration:none;" href="#">In band together</button></li>
                  <li><button class="relButtonLink btn-link" data-value="collaboratedWasBandleader" style="color:#333; text-decoration:none;" href="#">Was bandleader of</button></li>
                  <li><button class="relButtonLink btn-link" data-value="collaboratedWasBandmember" style="color:#333; text-decoration:none;" href="#">Was band member of</button></li> 
                  <li class="divider"></li>
                  <li><button class="relButtonLink btn-link" data-value="collaborated" style="color:#333; text-decoration:none;" href="#">Other collaboration</button></li>
                </ul>
              </div>
                  
 
                
                <button class="relButton btn btnInfluenced" rel="popover" data-value="influenced" data-title="Influenced:" data-placement="left" data-trigger="hover" data-content="A person who has influenced this person." data-content-old="<person> has influenced <interviewee>.">Influenced by</button>
				<button class="relButton btn btnMentor" rel="popover" data-value="mentor" data-title="Mentor:" data-placement="left" data-trigger="hover" data-content="A person who serves as a trusted counselor or teacher to this person." data-content-old="<person> served as a trusted counselor or teacher to <interviewee>.">Mentor of</button>                
				<button class="relButton btn" id="relButtonSkip" rel="popover" data-value="skip" data-title="Skip:" data-placement="left" data-trigger="hover" data-content="Cannot tell from this text what the relationship was.">Skip</button>                                 
				
                <a class="btn" style="width:85%;" href="#commentModal"  id="commentModalOpenButton" role="button" data-toggle="modal" rel="popover" data-value="skip" data-title="" data-placement="left" data-trigger="hover" data-content="Do you know something more? Click to add a comment about this relationship."><img src="/52new/img/icon_comment.png" style="height:20px; width: auto;">&nbsp;Add Comment</a>                                 

                <hr>                
            </div>
                        
         
            <div style="position:relative" id="progressBar">
                <div class="progress progress-info progress-striped">
                  <div class="bar" style="width: 0%">
                    <div id="statsHeroComplete" style="top:2px; left:55px;">Progres Bar</div>
                  </div>
                </div>              
			</div>
            

        </div>        
      </div>
    </div>

    <div id="viz">
    
    	
	   
    </div>
	 <a href="#" id="vizAbout" rel="popover" data-title="Network" data-placement="top" data-html="true" data-trigger="hover" data-content="This is a network visualization of the data you are building.">What is this?</a>            

        
    
    <div class="tutorial" id="tutorialStart"    	
        rel="popover" data-value="skip" data-title="Hello!" data-placement="right" data-html="true" data-trigger="manual" data-content="Welcome to the Linked Jazz 52nd Street, thanks for stopping by! We are reviewing interview transcripts made with famous Jazz musicians in order to map their relationships. With this tool you can help review the transcripts and assign relationships between the individuals. I see you are new, would you like me to explain how everything works?<br><br><button class='btn tutorialNext btn-primary'>Okay</button>&nbsp;<button class='btn tutorialStop'>No Thanks</button>"
    >
    	<img src="/52new/img/logo_small.png"></div>          
    </div>
   
   <div class="tutorial" id="tutorialStartContentArea" 
   
	  rel="popover" data-value="skip" data-title="Transcript Area" data-placement="right" data-html="true" data-trigger="manual" data-content="This area is where the text from the interview will appear. It will show the question the interviewer asked and the musician's answer. Based off of this text you will determine the relationship between the two people.<br><br><button class='btn tutorialNext btn-primary'>Next</button>"
    >
    	<img src="/52new/img/logo_small.png"></div>          
    </div>
   
    
   <div class="tutorial" id="tutorialStartDone" 
   
	  rel="popover" data-value="skip" data-title="All Done" data-placement="right" data-html="true" data-trigger="manual" data-content="That's It! Thanks for contributing to the Linked Jazz Project. Please don't hesitate to  contact us with any problems or questions.<br><br><button class='btn tutorialNext btn-primary'>Start</button>"
    >
    	<img src="/52new/img/logo_small.png"></div>          
    </div>    
          
   <div class="tutorial" id="tutorialFinishedAll" 
   
	  rel="popover" data-title="Complete" data-placement="right" data-html="true" data-trigger="manual" data-content="Woah! You processed the entire transcript, way to go! <br><br> You have earned <span id='finalPoints'></span> points from this transcript.<br><br>You can <a href='/52new/'>return to the start page</a> if you would like try another musican."
    >
    	<img src="/52new/img/logo_small.png"></div>          
    </div>         
    
    
<div id="commentModal" class="modal hide fade"  role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="commentModalLabel">What comment would you like to add about this relationship?</h3>
  </div>
  <div class="modal-body">
    
    <textarea id="addCommentText" tabindex="-1"></textarea>
    
  </div>
  <div class="modal-footer">
  
  
    		<button class="btn" data-dismiss="modal" id="addCommentCancel" aria-hidden="true">Cancel</button>
    
    
            <button class="btn btn-primary" id="addPrivateComment">Add Comment</button>

  
  <? /*
  
  	<div class="commentModalFooterButton">
    	<button class="btn btn-primary" rel="popover" data-title="" data-placement="left" data-trigger="hover" data-content="This will post your comment to the system and create a page where others can see your comment and discuss.">Add Public Comment</button>
    </div>
    
  	<div class="commentModalFooterButton">
    	<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
    </div>    
  	<div class="commentModalFooterButton">
    	<button class="btn btn-primary" rel="popover" data-title="" data-placement="right" data-trigger="hover" data-content="This will post your comment to the system only.">Add Private Comment</button>
    </div>
	
	*/
	
	?>
    
  </div>
</div>

    
	<?
		include('app/html/userModal.php'); 
	?>
    

	
    
    
</body>
</html>
