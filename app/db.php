<?
	/*
	/	Controls the database interactions
	/
	/
	/
	*/

	class dataBase {
		
		
		
		public $workFactor = 2;		//the number of people needed to review a question before it can be considered complete
		
		private $host = '';
		private $dbname = '';
		private $username = '';
		private $password = '';
		
		private $dbh = null;
		
		
		private $relationshipLookup = array(
			"knows" => "foaf:knows",
			"hasMet" => "rel:has_met",
			"acquaintance" => "rel:acquaintance_of",
			"friend" => "rel:friend_of",
			"collaborated" => "mo:collaborated_with",
			
			"collaboratedPlayedTogether" => "mo:collaborated_with_played_together",

			"collaboratedTouredWith" => "mo:collaborated_with_toured_with",


			"collaboratedInBandTogether" => "mo:collaborated_with_in_band_together",


			"collaboratedWasBandleader" => "mo:collaborated_with_was_bandleader",


			"collaboratedWasBandmember" => "mo:collaborated_with_was_bandmember",

			
			"influenced" => "rel:influenced_by",
			"mentor" => "rel:mentor_of" ,
			"skip" => "skip"
		);
		
		

		private function conenct(){	
		
		
			if(!$this->dbh){
				
				$this->dbh = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname . "", $this->username, $this->password);
			
				if ($this->dbh){
					return true;
				}else{
					$util->routeError();				
				}
			}
		}
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////						
		
		//
		//	USER Fucntions
		//
		
		
		public function storeUser($userAry){
			
			if ($userAry['name'] == '' || $userAry['type'] == ''){
				$util->routeError();	
			}
			
			$this->conenct();
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `name` = ? AND `type` = ?');
			$sth->execute(array($userAry['name'],$userAry['type']));
 			 
			//does this user exist already
			if ($sth->rowCount()==0){				
				//nope
				
				//if they use external login then they are already verified
				if ($userAry['type'] == 'organic'){
					$verififed = 0;	
				}else{
					$verififed = 1;	
					 			
				}
				
				//bcrypt the password
				if ($userAry['pass'] != ''){					
					require 'vendor/phpass/PasswordHash.php';
					$t_hasher = new PasswordHash(8, FALSE);					
 					$hash = $t_hasher->HashPassword($userAry['pass']);										
					$userAry['pass'] = $hash;	
				}
				
				$userAry['name'] = htmlentities($userAry['name'], ENT_QUOTES);
				
				
				$sth = $this->dbh->prepare('INSERT INTO `cs_users` (`name`,`type`,`email`,`pass`,`oauth_token`,`oauth_verifier`, `verified`) VALUES (?,?,?,?,?,?,?)');
				$sth->execute(array($userAry['name'],$userAry['type'],$userAry['email'],$userAry['pass'],$userAry['oauth_token'],$userAry['oauth_verifier'],$verififed));				
				
				//now do it and get the id
				$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `name` = ? AND `type` = ?');
				$sth->execute(array($userAry['name'],$userAry['type']));				
				$results = $sth->fetch();
				
				//assign a generic screen name if they did not make one
				if ($userAry['type'] == 'facebook' || $userAry['type'] == 'google'){
					$screenName = "Hep Cat " . $results['id'];					
					$sth = $this->dbh->prepare('UPDATE `cs_users` SET `screenName` = ? WHERE `id` = ?');
					$sth->execute(array($screenName,$results['id']));	
				}				
				 
				return $results['id'];				
			
			}else{
				
				//they do return user id
				$results = $sth->fetch();
				return $results['id'];
				
			}
		}
		
		
		public function verify($hash){
			
			$this->conenct();		
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `reset` = ?');
			$sth->execute(array($hash));			
			
 			if ($sth->rowCount()==0){				
				return false;
			}else{
				$sth = $this->dbh->prepare('UPDATE `cs_users` SET `verified` = 1 WHERE `reset` = ?');
				$sth->execute(array($hash));						
				return true;				
			}			
			
		}		
		
		public function changeScreenName($new,$id){
			
			
			$new = htmlentities($new,ENT_QUOTES);
			
			$this->conenct();		
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `name` = ? or `screenName` = ?');
			$sth->execute(array($new,$new));			
			
 			if ($sth->rowCount()==0){				
			
				$sth = $this->dbh->prepare('UPDATE `cs_users` SET `screenName` = ? WHERE `id` = ?');
				$sth->execute(array($new,$id));						
				return true;
			
			}else{
				return false;
			}			
			
		}
		
		
		public function isUserName($name){
						
			$this->conenct();		
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `name` = ?');
			$sth->execute(array($name));
 			 
 			if ($sth->rowCount()==0){				
				return false;
			}else{
				return true;
			}
			
			
		}
		public function isEmail($email){
						
			$this->conenct();		
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `email` = ?');
			$sth->execute(array($email));
 			 
 			if ($sth->rowCount()==0){				
				return false;
			}else{
				return true;
			}
			
			
		}		
		public function setUserHash($email,$type,$hash){
						
			$this->conenct();		
			if ($type == 'reset'){
				$sth = $this->dbh->prepare('UPDATE `cs_users` SET reset = ? WHERE `email` = ?');
			}
			if ($type == 'cookie'){
				$sth = $this->dbh->prepare('UPDATE `cs_users` SET cookie = ? WHERE `id` = ?');
			}			
			
			$sth->execute(array($hash,$email));
 			 
 			if ($sth->rowCount()==0){				
				return false;
			}else{
				return true;
			}
			
			
		}			
		
		public function returnUserHash($email,$type){
		
			if ($type == 'reset'){
				$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `email` = ?');
			}
			$sth->execute(array($email));				
			$results = $sth->fetch();
			return $results['reset'];		
						
		}
		
		public function returnUserByHash($hash){
			$this->conenct();
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `reset` = ? OR `cookie` = ?');
			$sth->execute(array($hash,$hash));
 			if ($sth->rowCount()==0){				
				return 0;
			}else{
				$results = $sth->fetch();
				return $results['id'];
			}			
			
				
		}
		
		
		public function returnUserSingle($id){
			$this->conenct();
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `id` = ?');
			$sth->execute(array($id));
			return $sth->fetch();
		}
		

				
		
		public function resetUserPassword($hash,$pass){
			
			$this->conenct();
			
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `reset` = ?');
			$sth->execute(array($hash));
			$results = $sth->fetch();
			$useId = $results['id'];			
			
			require 'vendor/phpass/PasswordHash.php';
			$t_hasher = new PasswordHash(8, FALSE);					
			$pass = $t_hasher->HashPassword($pass);			 		
			
			$sth = $this->dbh->prepare('UPDATE `cs_users` SET reset = ?, `pass` = ? WHERE `reset` = ?');
			$sth->execute(array('',$pass,$hash));
			
			return $useId;
		}
		
		
		public function validateUser($name,$pass){
			
			require 'vendor/phpass/PasswordHash.php';
			$t_hasher = new PasswordHash(8, FALSE);					
			 

			$this->conenct();
			$sth = $this->dbh->prepare('SELECT * FROM `cs_users` WHERE `email` = ?');
			$sth->execute(array($name));
			$results = $sth->fetch();
			$correct = $results['pass'];			
			$check = $t_hasher->CheckPassword($pass, $correct);
 			if ($check){				
				return $results['id'];
			}else{
				return false;				
			}				
				
		
			
		}
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////				
		
		//
		//	DATA FUNCTIONS
		//
		
		
		
		public function returnTranscriptList() {
		
			
			$this->conenct();
			
			$SQL = "SELECT 
					transcripts.*, 
					cs_transcripts.*,
					 
					cs_transcripts.totalPairs * ? as totalPairsWork,
					
					(cs_transcripts.totalResponse) / (cs_transcripts.totalPairs * ?) * 100 as percentComplete
					
					FROM `transcripts` INNER JOIN `cs_transcripts` on transcripts.md5=cs_transcripts.transcript
					
					ORDER BY percentComplete, interviewee ASC";
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute(array($this->workFactor,$this->workFactor));			
			$results = $sth->fetchAll();
			
			return $results;
			
		}
		
		
		public function returnMatchesForURI($transcript,$uri){
			
			
			
			$this->conenct();
			
			$SQL = "SELECT 					
					text.id, 
					text.idLocal, 
					text.text, 
					matches.type,
					text.speaker 
					FROM `matches` 
					INNER JOIN text on matches.transcript=text.transcript and matches.idLocal=text.idLocal 
					WHERE 
					matches.transcript = ? 
					AND matches.personURI = ?";
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute(array($transcript,$uri));			
			
			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$json=json_encode($results);
							
			return $json;
			
			
			
		}
		
		
		public function returnTextById($transcript,$id){
			
			$this->conenct();
			
			$SQL = "SELECT * FROM `text` WHERE `transcript` = ? AND `idLocal` = ?;";
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute(array($transcript,$id));			
			$results = $sth->fetch(PDO::FETCH_ASSOC);	
			$json=json_encode($results);
							
			return $json;					
		}
		
		
		public function returnProjectProgress() {
			
			$this->conenct();
			
			$SQL = "SELECT sum(`totalPairs`) as totalPairs, sum(`totalResponse`) as totalResponse FROM `cs_transcripts`;";
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute();			
			$results = $sth->fetch();
						
			
			$total = $results['totalPairs'];
			$totalDone = $results['totalResponse'];			
			
			 
			
			$progress = round($totalDone / ($total * $this->workFactor) * 100);
			  
			return $progress;
			
		}
		
		
		public function returnTranscriptMetaByURI($uri){
				
			$this->conenct();	
			$sth = $this->dbh->prepare('SELECT transcripts.*, cs_transcripts.* FROM `transcripts` INNER JOIN `cs_transcripts` on transcripts.md5=cs_transcripts.transcript WHERE transcripts.intervieweeURI LIKE ?');
			$sth->execute(array('%'. $uri . '%'));	
	
			if ($sth->rowCount()!=0){
			
				$transcriptMeta = $sth->fetch(PDO::FETCH_ASSOC);
				return  $transcriptMeta;
				
			}else{
				
				
				
			}
						
		}
		
		
		public function returnMatchPeople($transcript) {
		
			$this->conenct();
			
			$SQL = "SELECT DISTINCT 
				matches.personURI, 
				authority.name, 
				SUBSTRING_INDEX(authority.name,' ',-1) as lastName, 
				matches.idLocal,
				authority.image 
				FROM `matches` 
				INNER JOIN `authority` on matches.personURI=authority.uri 
				WHERE matches.transcript = ? ORDER BY lastName ASC;";
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute(array($transcript));			
			$results = $sth->fetchAll();		
			
			
			return $results;
		 
		}
		
		
		public function storeComment($transcript, $interviewee, $mention, $comment, $pairs){
			
			
			
			$pairsText = '';
			
			foreach($pairs as $x){
				
				$pairsText = $pairsText . $x . ",";
				
				
			}
			
			$pairsText = substr($pairsText,0,strlen($pairsText)-1);
			
			
			$this->conenct();	
			$sth = $this->dbh->prepare('INSERT INTO `cs_comments` SET `transcript` = ?, `interviewee` = ?, `mention` = ?, `user` = ?, `comment` = ?, `pairs` = ?');
			$sth->execute(array($transcript, $interviewee, $mention, $_SESSION['userId'], $comment, $pairsText));
			
			return "{}";
			
			
		}
		
		
		public function storeRelationship($source, $target, $transcript, $value, $idlocals, $points) {
			
			//Are we overwriting or is this a new one?
			
			$this->conenct();	
			$sth = $this->dbh->prepare('SELECT * FROM `cs_results` WHERE `transcript` = ? and `user` = ? and `target` = ? and `idLocals` = ?');
			$sth->execute(array($transcript,$_SESSION['userId'],$target,$idlocals));
			
			
			
			if ($sth->rowCount()==0){
				
				//new
				$sth = $this->dbh->prepare('INSERT INTO `cs_results` SET `source` = ?, `target` = ?, `transcript` = ?, `user` = ?, `value` = ?, `idLocals` = ?, `points` = ?');
				$sth->execute(array($source, $target, $transcript, $_SESSION['userId'], $this->relationshipLookup[$value], $idlocals, $points));
				
				//also update the master count
				$sth = $this->dbh->prepare('UPDATE `cs_transcripts` SET `totalResponse` = `totalResponse` + ? WHERE `transcript` = ?');
				$sth->execute(array($points, $transcript));
				
				
				return json_encode(array("points" => true));
				
			}else{
				
				$results = $sth->fetch();
				$sth = $this->dbh->prepare('UPDATE `cs_results` SET `value` = ? WHERE `id` = ?');
				$sth->execute(array($this->relationshipLookup[$value],$results['id']));
				return json_encode(array("points" => false));
				
			}
		}
		

		public function isNoob() {
		
			$this->conenct();
			
			$SQL = "SELECT 
					count(`transcript`) as count					
					from cs_results 
					where 
					`user` = ?";
					 
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute(array($_SESSION['userId']));			
			
			$results = $sth->fetch(PDO::FETCH_ASSOC);		
			
			
			return $results;
		 
		}				
		
		
		public function returnProgress($transcript) {
		
			$this->conenct();
			
			$SQL = "SELECT 
					count(`transcript`) as count, 
					`target`, 
					sum(`points`) as `points` 
					from cs_results 
					where 
					`transcript` = ? and 
					`user` = ? 
					group by target";
					 
		
			$sth = $this->dbh->prepare($SQL);
			
			$sth->execute(array($transcript,$_SESSION['userId']));			
			
			$results = $sth->fetchAll(PDO::FETCH_ASSOC);		
			
			
			return $results;
		 
		}		
		
		
		
		
		
		
		
		
		
		
		
		//magics
		function __construct() {		
		
		
		
		}
		
		
		
		
		
	}













?>