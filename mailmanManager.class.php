<?php 
class mailmanManager
{
	private $server;
	private $protokoll;
	private $port;
	private $pfad;
	public function mailmanManager($server, $port = 80, $protokoll = "http", $pfad = "cgi-bin/mailman/")
	{
		$this->server = $server;
		$this->port = $port;
		$this->protokoll = $protokoll;
		$this->pfad = $pfad;
	}
	
	
	public function createList($listName, $ownserMail, $authPW, $listPw = null, $moderate=0, $langs="en", $notify=1)
	{
		$url = $this->getLink("create");
		$param = array();
		$param["listname"]=$listName;
		$param["owner"]=$ownserMail;
		
		if($listPw!=null)
		{
			$param["autoge"]=0;
			$param["password"]=$listPw;
			$param["confirm"]=$listPw;
		}
		else
		{
			$param["autoge"]=1;
		}
		$param["moderate"]=$moderate;
		$param["langs"]=$langs;
		$param["notify"]=$notify;
		$param["auth"]=$authPW;
		$param["doit"]=true;
		$re = $this->post_request($url, $param);	
		//echo $re['content'];
	}
	public function getName($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@name\=\"real\_name\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
		
	}
	public function setName($list, $pw, $name)
	{
		$oldName = $this->getName($list, $pw);
		if(strtolower($oldName)==strtolower($name))
		{
			$url = $this->getLink("admin/".$list);
			$param["real_name"]=$name;
			$param["adminpw"]=$pw;
			$re = $this->post_request($url, $param);
		}
		else
		{
			return false;
		}
	}
	//Owner
	public function getOwner($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@\<TEXTAREA\sNAME\=owner\sROWS\=3\sCOLS\=40\sWRAP\=off\>(.*?)\<\/TEXTAREA>@ms';
		preg_match($plattern, $content, $treffer);
		$mails = explode("\n", $treffer[1]);
		return $mails;
		//return $treffer[1];
	}
	public function delOwnser($list, $pw, $ownerToDel)
	{
		$ownsers = $this->getOwner($list, $pw);
		$newOwnerList = array();
		foreach($ownsers as $owner)
		{
			if($owner!=$ownerToDel)
			{
				$newOwnerList[] = $owner;
			}
		}
		$ownserString = "";
		foreach($newOwnerList as $owner)
		{
			$ownserString.=$owner."\n";
		}
		$url = $this->getLink("admin/".$list);
		$param["owner"]=$ownserString;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
	}
	public function addOwner($list, $pw, $ownerToAdd)
	{
		$ownsers = $this->getOwner($list, $pw);
		if(!in_array($ownerToAdd, $ownsers))
		{
			$ownsers[] = $ownerToAdd;
		}
		$ownserString = "";
		foreach($ownsers as $owner)
		{
			$ownserString.=$owner."\n";
		}
		$url = $this->getLink("admin/".$list);
		$param["owner"]=$ownserString;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
	}
	//Moderatoren
	public function getModerator($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@\<TEXTAREA\sNAME\=moderator\sROWS\=3\sCOLS\=40\sWRAP\=off\>(.*?)\<\/TEXTAREA>@ms';
		preg_match($plattern, $content, $treffer);
		$mails = explode("\n", $treffer[1]);
		return $mails;
		//return $treffer[1];
	}
	public function delModerator($list, $pw, $ownerToDel)
	{
		$ownsers = $this->getModerator($list, $pw);
		$newOwnerList = array();
		foreach($ownsers as $owner)
		{
			if($owner!=$ownerToDel)
			{
				$newOwnerList[] = $owner;
			}
		}
		$ownserString = "";
		foreach($newOwnerList as $owner)
		{
			$ownserString.=$owner."\n";
		}
		$url = $this->getLink("admin/".$list);
		$param["moderator"]=$ownserString;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
	}
	public function addModerator($list, $pw, $ownerToAdd)
	{
		$ownsers = $this->getModerator($list, $pw);
		if(!in_array($ownerToAdd, $ownsers))
		{
			$ownsers[] = $ownerToAdd;
		}
		$ownserString = "";
		foreach($ownsers as $owner)
		{
			$ownserString.=$owner."\n";
		}
		$url = $this->getLink("admin/".$list);
		$param["moderator"]=$ownserString;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
	}
	//Description
	public function getDescription($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@name\=\"description\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setDescription($list, $pw, $Description)
	{
		$url = $this->getLink("admin/".$list);
		$param["description"]=$Description;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
	}
	//Info
	public function getInfo($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		$plattern = '@\<TEXTAREA\sNAME\=info\sROWS\=7\sCOLS\=40\sWRAP\=soft\>(.*?)\<\/TEXTAREA>@ms';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setInfo($list, $pw, $info)
	{
		$url = $this->getLink("admin/".$list);
		$param["info"]=$info;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getInfo($list, $pw, 0);
		if($check==$info)
		{
			return true;
		}
		return false;
	}
	
	public function advertiseList($status = 1, $list, $password, $domain = NULL, $protokoll = NULL) {
		if ($domain == NULL) {
			$domain = $this -> domain;
		}
		if ($protokoll == NULL) {
			$protokoll = $this -> protokoll;
		}
		$url = $protokoll . "://" . $domain . "/mailman/admin/" . $list . "/privacy?advertised=" . $status . "&adminpw=" . $password;
		file_get_contents($url);
	}
	//SubjectPrefix
	public function getSubjectPrefix($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		$plattern = '@name\=\"subject\_prefix\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setSubjectPrefix($list, $pw, $subjectPrefix)
	{
		$url = $this->getLink("admin/".$list);
		$param["subject_prefix"]=$subjectPrefix;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$subject = $this->getSubjectPrefix($list, $pw, 0);
		if($subject==$subjectPrefix)
		{
			return true;
		}
		return false;
	}
	//Anonyme List
	public function getAnonymousList($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="anonymous_list" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setAnonymousList($list, $pw, $status)
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["anonymous_list"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getAnonymousList($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	//first_strip_reply_to
	public function getFirstStripReplyTo($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="first_strip_reply_to" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setFirstStripReplyTo($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["first_strip_reply_to"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getFirstStripReplyTo($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//Replay Goes To List
	public function getReplyGoesToList($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="reply_goes_to_list" type="RADIO" value="0" CHECKED >')!=FALSE)
		{
			return 0;
		}
		if(strpos($content, '<INPUT name="reply_goes_to_list" type="RADIO" value="1" CHECKED >')!=FALSE)
		{
			return 1;
		}
		if(strpos($content, '<INPUT name="reply_goes_to_list" type="RADIO" value="2" CHECKED >')!=FALSE)
		{
			return 2;
		}

	}
	public function setReplyGoesToList($list, $pw, $status) //0 = Poster 1 = This list 2 = Explicit address
	{
		$url = $this->getLink("admin/".$list);
		$param["reply_goes_to_list"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getReplyGoesToList($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	//reply_to_address
	public function getReplyToAddress($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		$plattern = '@name\=\"reply\_to\_address\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setReplyToAddress($list, $pw, $subjectPrefix)
	{
		$url = $this->getLink("admin/".$list);
		$param["reply_to_address"]=$subjectPrefix;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$subject = $this->getReplyToAddress($list, $pw, 0);
		if($subject==$subjectPrefix)
		{
			return true;
		}
		return false;
	}
	//umbrella_list
	public function getUmbrellaList($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="umbrella_list" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setUmbrellaList($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["umbrella_list"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getUmbrellaList($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	//umbrella_member_suffix
	public function getUmbrellaMemberSuffix($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		$plattern = '@name\=\"umbrella\_member\_suffix\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setUmbrellaMemberSuffix($list, $pw, $UmbrellaMemberSuffix)
	{
		$url = $this->getLink("admin/".$list);
		$param["umbrella_member_suffix"]=$UmbrellaMemberSuffix;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$subject = $this->getUmbrellaMemberSuffix($list, $pw, 0);
		if($subject==$UmbrellaMemberSuffix)
		{
			return true;
		}
		return false;
	}
	//send_reminder
	public function getSendReminders($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="send_reminders" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setSendReminders($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["send_reminders"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getSendReminders($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//welcome_msg
	public function getWelcomeMsg($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@\<TEXTAREA\sNAME\=welcome\_msg\sROWS\=4\sCOLS\=40\sWRAP\=soft\>(.*?)\<\/TEXTAREA>@ms';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
		//return $treffer[1];
	}
	public function setWelcomeMsg($list, $pw, $p)
	{
		$url = $this->getLink("admin/".$list);
		$param["welcome_msg"]=$p;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getSendReminders($list, $pw, 0);
		if($check==$p)
		{
			return true;
		}
		return false;
	}
	
	//send_welcome_msg
	public function getSendWelcomeMsg($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="send_welcome_msg" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setSendWelcomeMsg($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["send_welcome_msg"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getSendWelcomeMsg($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//goodbye_msg
	public function getGoodbyeMsg($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@\<TEXTAREA\sNAME\=goodbye\_msg\sROWS\=4\sCOLS\=40\sWRAP\=soft\>(.*?)\<\/TEXTAREA>@ms';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
		//return $treffer[1];
	}
	public function setGoodbyeMsg($list, $pw, $p)
	{
		$url = $this->getLink("admin/".$list);
		$param["goodbye_msg"]=$p;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getGoodbyeMsg($list, $pw, 0);
		if($check==$p)
		{
			return true;
		}
		return false;
	}
	
	//send_goodbye_msg
	public function getSendGoodbyeMsg($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="send_goodbye_msg" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setSendGoodbyeMsg($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["send_goodbye_msg"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getSendGoodbyeMsg($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//admin_immed_notify
	public function getAdminImmedNotify($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="admin_immed_notify" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setAdminImmedNotify($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["admin_immed_notify"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getAdminImmedNotify($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	//respond_to_post_requests
	
	public function getRespondToPostRequests($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="respond_to_post_requests" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setRespondToPostRequests($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["respond_to_post_requests"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getRespondToPostRequests($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//admin_notify_mchanges
	public function getAdminNotifyMchanges($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="admin_notify_mchanges" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setAdminNotifyMchanges($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["admin_notify_mchanges"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getAdminNotifyMchanges($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//emergency
	public function getEmergency($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="emergency" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setEmergency($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["emergency"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getEmergency($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//administrivia
	public function getAdministrivia($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="administrivia" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setAdministrivia($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["administrivia"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getAdministrivia($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//max_message_size
	public function getMaxMessageSize($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@name\=\"max\_message\_size\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setMaxMessageSize($list, $pw, $p)
	{
		$url = $this->getLink("admin/".$list);
		$param["max_message_size"]=$p;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getMaxMessageSize($list, $pw, 0);
		if($check==$p)
		{
			return true;
		}
		return false;
	}
	
	//admin_member_chunksize
	public function getAdminMemberChunksize($list, $pw)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = file_get_contents($url);
		$plattern = '@name\=\"admin\_member\_chunksize\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setAdminMemberChunksize($list, $pw, $p)
	{
		$url = $this->getLink("admin/".$list);
		$param["admin_member_chunksize"]=$p;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getAdminMemberChunksize($list, $pw, 0);
		if($check==$p)
		{
			return true;
		}
		return false;
	}
	
	//host_name
	public function getHostName($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		$plattern = '@name\=\"host\_name\"\stype\=\"TEXT\"\svalue\=\"(.*?)\"@';
		preg_match($plattern, $content, $treffer);
		return $treffer[1];
	}
	public function setHostName($list, $pw, $p)
	{
		$url = $this->getLink("admin/".$list);
		$param["host_name"]=$p;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$subject = $this->getHostName($list, $pw, $p);
		if($subject==$p)
		{
			return true;
		}
		return false;
	}
	
	//include_rfc2369_headers
	public function getIncludeRfc2369Headers($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="include_rfc2369_headers" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setIncludeRfc2369Headers($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["include_rfc2369_headers"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getIncludeRfc2369Headers($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//include_list_post_header
	public function getIncludeListPostHeader($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="include_list_post_header" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setIncludeListPostHeader($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["include_list_post_header"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getIncludeListPostHeader($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}
	
	//include_sender_header
	public function getIncludeSenderHeader($list, $pw, $cache = 86400)
	{
		$url = $this->getLink("admin/".$list);
		$url .= "?&adminpw=".$pw;
		$content = $this->getFileContent($url, $cache);
		if(strpos($content, '<INPUT name="include_sender_header" type="RADIO" value="0" CHECKED >')==FALSE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function setIncludeSenderHeader($list, $pw, $status) //true or false
	{
		if($status==TRUE)
		{
			$status = 1;
		}
		if($status==FALSE)
		{
			$status = 0;
		}
		$url = $this->getLink("admin/".$list);
		$param["include_sender_header"]=$status;
		$param["adminpw"]=$pw;
		$re = $this->post_request($url, $param);
		$check = $this->getIncludeSenderHeader($list, $pw, 0);
		if($check==$status)
		{
			return true;
		}
		return false;
	}

	
	
	
	//Helper Funktion
	
	private function getLink($funktion = "")
	{
		$url = $this->protokoll."://".$this->server;
		if($this->port != 80)
		{
			$url .= ":".$port;
		}
		$url .= "/".$this->pfad;
		if($funktion!="")
		{
			$url.=$funktion;
		}
		return $url;
		
	}
	private function getFileContent($url, $cache = 0)
	{
		$name = md5($url);
		if(file_exists("cache/".$name))
		{
			$time = filectime("cache/".$name);
			if($time > time() - $cache)
			{
				return file_get_contents("cache/".$name);
			}
		}
		//echo "DOWNLOAD";
		$content = file_get_contents($url);
		$fp = fopen("cache/".$name, "w");
		fputs($fp, $content);
		fclose($fp);
		return $content;
	}
	private function post_request($url, $data, $referer = '') {

		// Convert the data array into URL Parameters like a=b&foo=bar etc.
		$data = http_build_query($data);

		// parse the given URL
		$url = parse_url($url);

		if ($url['scheme'] != 'http') {
			die('Error: Only HTTP request are supported !');
		}

		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];

		// open a socket connection on port 80 - timeout: 30 sec
		$fp = fsockopen($host, 80, $errno, $errstr, 30);

		if ($fp) {

			// send the request headers:
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");

			if ($referer != '')
				fputs($fp, "Referer: $referer\r\n");

			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($data) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $data);

			$result = '';
			while (!feof($fp)) {
				// receive the results of the request
				$result .= fgets($fp, 128);
			}
		} else {
			return array('status' => 'err', 'error' => "$errstr ($errno)");
		}

		// close the socket connection:
		fclose($fp);

		// split the result header from the content
		$result = explode("\r\n\r\n", $result, 2);

		$header = isset($result[0]) ? $result[0] : '';
		$content = isset($result[1]) ? $result[1] : '';

		// return as structured array:
		return array('status' => 'ok', 'header' => $header, 'content' => $content);
	}
	

}
?>