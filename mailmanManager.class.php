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