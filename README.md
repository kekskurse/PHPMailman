# PHPMailmann 
PHPMailman are PHP-Classes to Manage a Mailman Server.

* mailman.class.php -> Basic function to Manage the Members of a List
* MailmanManager.class.php -> General Config of a List and create a new List
* MailmanManagerPW.class.php -> Set/Change the Admin and the Moderator Passwort

# Parts of PHPMailman
## Use class mailman

you can use the Mailman Class to Get/Set the Member of a Mailinglist and get Informationen from the Mailman Server.

How to Use this Class:

     require_once 'class/mailman.class.php';
     $mailman = new mailman();

Get List of public Mailinglists:

     $list = $mailman->getList("list.example.com/cgi-bin");

Check if a Mail is Reading the Mailinglist

     $reading = $mailman->isMember("exampla@mail.de", "listname", "listpassword", "list.example.com/cgi-bin");

Add a Mail to a List:

     $mailman->insert("exampla@mail.de", "listname", "listpassword", "list.example.com/cgi-bin");

Remove a Mail from a List:

     $mailman->remove("exampla@mail.de", "listname", "listpassword", "list.example.com/cgi-bin");


## Use class mailmanManager

You can use the MailmanManager Class to Manage your Mailman Server. You can change the Settings of a List or Create new Lists. 

Create a Class-Instanz:

	require_once 'mailmanManager.class.php';
	$mm = new mailmanManager($server, $port, $protokoll, $pfad);

You must only give the $server parameter, the others are optional. 

* Server is the Servername (e.g. lists.example.com)
* port is the Port of the Server (normal 80)
* protokoll is the Portokoll Typ (only use http)
* pfad the url/pfad to the Mailman (normal cgi-bin/mailman/)

You can create a new Mailinglist. But u need the Mailman Passwort to Create new Mailinglist

	$mm->createList($listName, $ownserMail, $authPW, $listPw = null, $moderate=0, $langs="en", $notify=1)

You can get and set the 'real' Name of a Mailinglist. But your are only allowd to make case-changes

	$mm->getName($list, $pw); //Return the Name of the List
	$mm->setName($list, $pw, $p); //Return a Boolen

You can get the Owner as an Array:

	$mm->getOwner($list, $pw); //Return a String Array

You can add a new Owner. This function really add not change the Ownser!

	$mm->addOwner($list, $pw, $ownerToAdd);

You can dell a Ownser. This function only delete ONE E-Mail

	$mm->delOwnser($list, $pw, $ownerToDel);
	
You can get the Moderator as an Array:

	$mm->getModerator($list, $pw); //Return a String Array

You can add a new Moderator. This function really add not change the Moderator!

	$mm->addModerator($list, $pw, $ownerToAdd);

You can dell a Moderator. This function only delete ONE E-Mail

	$mm->delModerator($list, $pw, $ownerToDel);

You can get and set the Discription of the List

	$mm->getDescription($list, $pw); //Return the Name of the List
	$mm->setDescription($list, $pw, $p); //Return a Boolen

You can get and set the Info of the List

	$mm->getInfo($list, $pw); //Return the Name of the List
	$mm->setInfo($list, $pw, $p); //Return a Boolen

and much more ;)

