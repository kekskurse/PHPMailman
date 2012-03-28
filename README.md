Use class mailman
-----------------
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


Use class mailmanManager
------------------------
In Development
