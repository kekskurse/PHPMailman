<?php
class mailman
{
	private $protokoll="http";
	private $domain = "";
	function getList($domain = NULL, $protokoll = NULL)
	{
		if($domain == NULL )
		{
			$domain = $this->domain;
		}
		if($protokoll == NULL )
		{
			$protokoll = $this->protokoll;
		}
		$url = $protokoll."://".$domain."/mailman/admin/";
		$plattern = '@<a href="../admin/(.*)">@';
		$content  = file_get_contents($url);
		preg_match_all($plattern, $content, $matchs);
		return $matchs[1];
	}
	public function isMember($mail, $list, $passwort, $domain = NULL, $protokoll = NULL)
	{
		if($domain == NULL )
		{
			$domain = $this->domain;
		}
		if($protokoll == NULL )
		{
			$protokoll = $this->protokoll;
		}
		$url = $protokoll."://".$domain."/mailman/admin/".$list."/members?findmember=".$mail."&setmemberopts_btn&adminpw=".$passwort;
		$content = file_get_contents($url);
		$found = strpos($content, "<em>1 Mitglieder insgesamt</em>");
		if($found>=1)
		{
			return true;
		}
		return false;
	}
	public function insert($mail, $list, $password, $domain = NULL, $protokoll = NULL)
	{
		if($domain == NULL )
		{
			$domain = $this->domain;
		}
		if($protokoll == NULL )
		{
			$protokoll = $this->protokoll;
		}
		$url = $protokoll."://".$domain."/mailman/admin/".$list."/members/add?subscribe_or_invite=0&send_welcome_msg_to_this_batch=0&notification_to_list_owner=0&subscribees_upload=".$mail."&adminpw=".$password;
		file_get_contents($url);
	}
	public function remove($mail, $list, $password, $domain = NULL, $protokoll = NULL)
	{
		if($domain == NULL )
		{
			$domain = $this->domain;
		}
		if($protokoll == NULL )
		{
			$protokoll = $this->protokoll;
		}
		$url = $protokoll."://".$domain."/mailman/admin/".$list."/members/remove?send_unsub_ack_to_this_batch=0&send_unsub_notifications_to_list_owner=0&unsubscribees_upload=".$mail."&adminpw=".$password;
		file_get_contents($url);
	}
	
	// Weitere funktionen
	public function AdvertiseList($status = 1, $list, $password, $domain = NULL, $protokoll = NULL)
	{
		if($domain == NULL )
		{
			$domain = $this->domain;
		}
		if($protokoll == NULL )
		{
			$protokoll = $this->protokoll;
		}
		$url = $protokoll."://".$domain."/mailman/admin/".$list."/privacy?advertised=".$status."&adminpw=".$password;
		file_get_contents($url);
	}
}
?>