<?php
//*********************************************************************************
//*                                                                               *
//*                  CoolForum v.0.8.5 Beta : Forum de discussion                   *
//*              Copyright ©2001-2014 SORIANO Denis alias Cool Coyote             *
//*                                                                               *
//*                                                                               *
//*       This program is free software; you can redistribute it and/or           *
//*       modify it under the terms of the GNU General Public License             *
//*       as published by the Free Software Foundation; either version 2          *
//*       of the License, or (at your option) any later version.                  *
//*                                                                               *
//*       This program is distributed in the hope that it will be useful,         *
//*       but WITHOUT ANY WARRANTY; without even the implied warranty of          *
//*       MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           *
//*       GNU General Public License for more details.                            *
//*                                                                               *
//*       You should have received a copy of the GNU General Public License       *
//*       along with this program; if not, write to the Free Software             *
//*	      Foundation, Inc., 59 Temple Place - Suite 330,                          *
//*	      Boston, MA  02111-1307, USA.                                            *
//*                                                                               *
//*                                                                               *
//*       Forum Créé par SORIANO Denis (Cool Coyote)                              *
//*       contact : coyote@coolcoyote.net                                         *
//*       site web et téléchargement : http://www.coolforum.net                   *
//*                                                                               *
//*********************************************************************************

////////////////////////////////////////////////////
function create_table($nom ,$query, $sql)
{
	$result = $sql->query($query)->execute();

    if ($result === false) {
        throw new Exception(' --! Création de la table ' . $nom . ' non effectuée ! Problème !! -- '.mysql_error().'<br />');
    }

    echo(" --&gt; Création de la table {$nom} effectuée...<br />");
}

function next_steps()
{
	echo('<form action="install.php" method="get">
	<input type="hidden" name="action" value="install" />
	<input type="hidden" name="steps" value="'.($_REQUEST['steps']+1).'" />
	<input type="submit" value="Continuer ->>" class="form" />
	</form>');
}

function insert_table($nom, $query, $sql)
{
	$result = $sql->query($query)->execute();

    if ($result === false) {
        throw new Exception(' --! Insertion dans la table ' . $nom . ' non effectuée ! Problème !! -- ' . mysql_error() . '<br />');
    }

    echo(' --&gt; Insertions dans la table ' . $nom .' effectuée...<br />');
}

function testemail($email) 
{ 
    return( preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+'.
                 '@'. 
                 '([-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]{2,}\.){1,3}'. 
                 '[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]{2,4}$/',
                 $email) > 0 );
}

function testurl($url) 
{
	if(preg_match("'^(http|ftp|https):\/\/([a-zA-Z0-9-\/\.@:%=?&;~_]+(?<![\.:%?&;]))$'",$url)==0)
		return false;
	else
		return true; 
}

function teststring($string)
{
	$testchain	=	preg_replace("/([\s]{1,})/","",$string);
	if(strlen($testchain)==0)
		return false;
	else
		return true;	
}

function getformatmsg($msg,$activenl2br=true)
{
	if(get_magic_quotes_gpc()==0)
		$msg=addslashes($msg);
		
	$msg=htmlentities($msg, ENT_COMPAT,'ISO-8859-1', true);
	
	if($activenl2br)
		$msg=nl2br($msg);
	return($msg);
}

function getencrypt($txt,$cle) 
{ 
	srand((double)microtime()*1000000); 
	$getencrypt_key = md5(rand(0,32000));
		
	$ctr=0; 
	$tmp = ""; 
	for ($i=0;$i<strlen($txt);$i++) 
	{
		if ($ctr==strlen($getencrypt_key)) 
			$ctr=0;
		$aff1 = substr($getencrypt_key,$ctr,1);
		
		$tmp.= $aff1.(substr($txt,$i,1) ^ $aff1);
		$ctr++;
	}
	
	$ctr=0;
	$code="";
	for ($i=0;$i<strlen($tmp);$i++)
	{
		if ($ctr==strlen($cle)) 
			$ctr=0;
		$code.=(substr($tmp,$i,1)) ^ (substr($cle,$ctr,1));
		$ctr++;	
	}
return($code);
}

function updatemembers($sql)
{

	$query = $sql->query("SELECT login FROM "._PRE_."user WHERE userstatus <> 0 ORDER BY registerdate DESC");
	$nbuser = $query->num_rows;
	list($lastmember) = $query->fetch_row();
	
	if (get_magic_quotes_runtime()==0) {
        $lastmember=addslashes($lastmember);
    }

	$query = $sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='statnbuser'", $nbuser)->execute();
	$query = $sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='statlastmember'", $lastmember)->execute();
}

////////////////////////////////////////////////////

if(!isset($_REQUEST['steps']))
	$_REQUEST['steps'] = "";

if($_REQUEST['steps']==5)
{
	$error = "";
	$ok = true;

	$query = $sql->query("SELECT COUNT(*) as nbentry FROM "._PRE_."user")->execute();
	list($nbentry) = $query->fetch_row();
	
	if($nbentry==0)
	{	
		$query = $sql->query("SELECT valeur FROM "._PRE_."config WHERE options='confirmparmail'")->execute();
		list($confirmparmail) = $query->fetch_row();

		if($confirmparmail == 6 && !teststring($_POST['quest']))
			$error = "Question non valide";
		if($confirmparmail == 6 && !teststring($_POST['rep']))
			$error = "Réponse non valide";					
		if(!teststring($_POST['pseudo']))
			$error = "Pseudonyme non valide";
		if(!teststring($_POST['pass1']))
			$error = "mot de passe non valide";
		if($_POST['pass1']!=$_POST['pass2'])
			$error = "Confirmation de mot de passe non valide";
		if(!testemail($_POST['adminmail']))
			$error = "email non valide";
			
		if(strlen($error)==0)
		{
			$pseudo = getformatmsg($_POST['pseudo']);
			$pass1 = $_POST['pass1'];
			$adminmail = $_POST['adminmail'];
			$urlweb = $_POST['urlweb'];
			
			if($confirmparmail == 3)
			{
				$quest	=	"";
				$rep	=	"";
			}
			else
			{
				$quest	=	getformatmsg($_POST['quest']);
				$rep	=	getformatmsg($_POST['rep']);
			}
						
			$query = $sql->query("SELECT valeur FROM "._PRE_."config WHERE options='chainecodage'")->execute();
			list($codage) = $query->fetch_row();
			$password=rawurlencode(getencrypt($pass1,$codage));
			
			$query = $sql->query("INSERT INTO "._PRE_."user (login,password,userstatus,registerdate,usermsg,usermail,usersite,timezone,lng) VALUES ('%s', '%s', 4 , %d, 0, '%s','%s',0,'fr')", array($pseudo, $password, time(), $adminmail, $urlweb))->execute();
			$query = $sql->query("INSERT INTO "._PRE_."userplus (idplus,mailorig,question,reponse) VALUES (1,'%s','%s', '%s')", array($adminmail, $quest, $rep))->execute();

			updatemembers($sql);

			if($ok)
			{
				echo("L'installation du forum est maintenant terminée!<p>
				
				Avant d'administrer votre forum vous DEVEZ supprimer le dossier <font color=red>install</font> de votre compte FTP.<br>
				Vous pourrez ensuite rejoindre l'administration de votre forum et commencer à l'administrer<br>
				en cliquant <a href=\"../admin/index.php\" class=men>ICI!</a><P>
							
				Merci d'avoir choisi CoolForum !");
			}
		}
		else
		{
			$_REQUEST['steps'] = 4;
			$pseudo = $_POST['pseudo'];
			$adminmail = $_POST['adminmail'];
			$urlweb = $_POST['urlweb'];
			$quest = "";
			$rep = "";
			
			if(isset($_POST['quest']))
				$quest = $_POST['quest'];
			if(isset($_POST['rep']))
				$rep = $_POST['rep'];
		}
	}
	else
		echo("Erreur: L'administrateur a déjà été créé...");
	
}

if ($_REQUEST['steps']==4) {
	$query = $sql->query("SELECT valeur FROM "._PRE_."config WHERE options = 'confirmparmail'")->execute();
    list($confirmparmail) = $query->fetch_row();

	$errorchain = "";
	if (isset($error) && strlen($error)>0) {
		$errorchain = "<tr>
	    <td class=\"corp\" bgcolor=\"#265789\" colspan=2 align=center><font size=2><b>*** $error ***</b></font></td>
	  </tr>";
    } else {
		$pseudo			=	"";
		$adminmail		=	"";
		$urlweb			=	"";
		$quest			=	"";
		$rep			=	"";
	}
	
	echo("<u>Configuration du compte de l'administrateur du forum</u><p>

	<form action=\"install.php\" method=\"post\">
	<table border=1 bordercolor=\"black\" cellpadding=2 cellspacing=0 style=\"border-collapse: collapse;\" align=center>
	$errorchain
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Choisissez un pseudo* :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"pseudo\" class=\"form\" value=\"$pseudo\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Choisissez un mot de passe* :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=password name=\"pass1\" class=\"form\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Confirmez le mot de passe* :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=password name=\"pass2\" class=\"form\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Adresse email* :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"adminmail\" class=\"form\" value=\"$adminmail\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Adresse de votre site web :</b></font><br><font size=1>(ex: http://www.monsite.com)</font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"urlweb\" class=\"form\" value=\"$urlweb\"></td>
	  </tr>");
	
	if($confirmparmail > 4)
		echo("	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Question: (vous sera posée afin de <BR>récupérer votre mot de passe en cas de perte)</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"quest\" class=\"form\" value=\"$quest\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Réponse: (Réponse qu'il vous faudra<BR>donner pour récupérer votre mot de passe)</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"rep\" class=\"form\" value=\"$rep\"></td>
	  </tr>");
	  		
	echo("</table><p>
	
	<center><font class=\"corp2\" size=1>* champs obligatoires</font></center><p>
	
	<input type=\"hidden\" name=\"action\" value=\"install\">
	<input type=\"hidden\" name=\"steps\" value=\"".($_REQUEST['steps']+1)."\">
	<input type=\"submit\" value=\"Continuer ->>\" class=\"form\">
	</form>");
	
	
}

if($_REQUEST['steps']==3)
{
	$error = "";
	$ok = true;
	
	if(!testemail($_POST['mail']))
		$error 	= 	"email non valide";
	if(!testurl($_POST['urlforum']))
		$error 	= 	"url du forum non valide";
	if(!teststring($_POST['forumname']))
		$error 	= 	"nom du forum non valide";
		
	if(strlen($error)==0)
	{
		$date=time();
		$forumname		=		getformatmsg($_POST['forumname']);
		$sitename		=		getformatmsg($_POST['sitename']);
		$siteurl		=		getformatmsg($_POST['siteurl']);
		$mail			=		getformatmsg($_POST['mail']);
		$urlforum		=		getformatmsg($_POST['urlforum']);			
		
		if($_POST['canmail'] == "Y")
		{
			$canmail	=		3;
			$usemails	=		"Y";
		}
		else
		{
			$canmail	=		6;
			$usemails	=		"N";
		}

		srand((double)microtime()*1000000); 
		$codage 		= 		md5(rand(0,32000));
		
		echo("<u>Insertion des valeurs de base dans les tables</u><p>
	
		<table border=1 bordercolor=\"black\" cellpadding=10 cellspacing=0 style=\"border-collapse: collapse;\" align=center>
		  <TR>
		    <TD class=\"corp\" bgcolor=\"#537FAC\">
		      <font size=2><b>");
		
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('openforum', 'Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('closeforummsg', 'Le forum est actuellement fermé, veuillez revenir plus tard.')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('forumname', '$forumname')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('sitename', '$sitename')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('siteurl', '$siteurl')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('contactmail', '$mail')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('topicparpage', 30)", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('msgparpage', 20)", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('confirmparmail', '$canmail')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('cookiedomain', '')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('cookierep', '/')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('limittopiclength', '40')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('limittimepost', '10')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('forumjump', 'Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('chainecodage', '$codage')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('ajouthtml', '')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('htmlbas', '')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('initialise', '$date')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('indexnews', '')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('mailnotify', 'N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('limitloginlength', '15')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('canpostmsgcache','N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('openinscriptions','Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('viewmsgedit','Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('urlforum','$urlforum')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('defaulttimezone','0')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('bbcodeinsign','N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('smileinsign','N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('topmembers','0')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('timetopmembers','0')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('catseparate','»')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('limitpoll','5')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('ForumDBVersion','0.8 beta')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('closeregmsg','Les inscriptions sont actuellement closes. Nous n\'acceptons plus de membres.')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('defaultlangage','fr')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('emailmask','|at|')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('statnbtopics','0')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('statnbposts','0')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('statnbuser','0')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('statlastmember','')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('defaultskin','1')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('mustbeidentify','N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('mailfunction','normal')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('censuredwords','')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('usepub','N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('usemails','$usemails')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('logos','Y-Y-Y-N-150-150-15')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('birth','')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('nextdailyupdate','')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('sendpmbymail','N')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('grades','a:5:{i:1;a:3:{i:0;s:7:\"Nouveau\";i:1;i:1;i:2;i:1;}i:2;a:3:{i:0;s:8:\"Visiteur\";i:1;i:50;i:2;i:2;}i:3;a:3:{i:0;s:14:\"Habitu&eacute;\";i:1;i:100;i:2;i:3;}i:4;a:3:{i:0;s:9:\"Titulaire\";i:1;i:200;i:2;i:4;}i:5;a:3:{i:0;s:6:\"Pilier\";i:1;i:500;i:2;i:5;}}')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('repflash','Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('use_grades','Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('conn_accueil','Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('conn_forum','Y')", $sql);
			insert_table(_PRE_."config","INSERT INTO "._PRE_."config VALUES ('conn_topic','Y')", $sql);

			echo("<center>");
			affseparate();
			echo("</center>");
			
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bg1', '#909090')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bg2', '#C1C1C1')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bordercolor', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bgtable1', '#537FAC')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bgtable2', '#A4B6C9')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bgtable3', '#265789')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'grp4', 'red')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'grp3', 'blue')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'grp2', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'grp1', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bangrp', 'yellow')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'lien1col', 'white')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'lien1visit', 'white')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'lien1hov', 'white')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'lien2col', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'lien2visit', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'lien2hov', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'textcol1', 'white')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'textcol2', 'black')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'smallfont', '1')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'middlefont', '2')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'bigfont', '3')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'repimg', 'defaut')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'reptpl', 'defaut')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'skinname', 'Defaut')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'font', 'verdana')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'affdegrad', 'Y')", $sql);
			insert_table(_PRE_."skins","INSERT INTO "._PRE_."skins VALUES (1, 'searchcolor', 'orange')", $sql);
			
			echo("<center>");
			affseparate();
			echo("</center>");
			
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (1, 'smile1.gif', '::)', 1)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (2, 'smile2.gif', '::(', 2)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (3, 'smile3.gif', '::o', 3)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (4, 'smile4.gif', '::D', 4)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (5, 'smile5.gif', ':;)', 5)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (6, 'smile6.gif', '::P', 6)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (7, 'smile7.gif', ':angry:', 7)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (8, 'smile8.gif', ':lol:', 8)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (9, 'smile9.gif', ':crazy:', 9)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (10, 'smile10.gif', ':sun:', 10)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (11, 'smile11.gif', ':love:', 11)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (12, 'smile12.gif', ':what:', 12)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (13, 'smile13.gif', ':gne:', 13)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (14, 'smile14.gif', ':baille:', 14)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (15, 'smile15.gif', ':toufou:', 15)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (16, 'smile16.gif', ':corne:', 16)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (17, 'smile17.gif', ':pilote:', 17)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (18, 'smile18.gif', ':invis:', 18)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (19, 'smile19.gif', ':fuk:', 19)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (20, 'smile20.gif', ':sleep:', 20)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (21, 'smile21.gif', ':cry:', 21)", $sql);
			insert_table(_PRE_."smileys","INSERT INTO "._PRE_."smileys VALUES (22, 'smile22.gif', ':bomb:', 22)", $sql);
			
			echo("<center>");
			affseparate();
			echo("</center>");
			
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (1,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (2,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (3,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (4,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (5,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (6,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (7,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (8,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (9,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (10,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (11,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (12,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (13,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (14,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (15,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (16,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (17,'.gif')", $sql);
			insert_table(_PRE_."avatars","INSERT INTO "._PRE_."avatars VALUES (18,'.gif')", $sql);

			echo("<center>");
			affseparate();
			echo("</center>");
			
			insert_table(_PRE_."language","INSERT INTO "._PRE_."language VALUES (1, 'fr', 'français')", $sql);

			echo("<center>");
			affseparate();
			echo("</center>");

			insert_table(_PRE_."groups","INSERT INTO "._PRE_."groups VALUES (1, 0, 'Visiteur', 3, 0, 0, 0, 0)", $sql);
			insert_table(_PRE_."groups","INSERT INTO "._PRE_."groups VALUES (2, 0, 'Membre', 3, 3500, 50, 400, 250)", $sql);
			insert_table(_PRE_."groups","INSERT INTO "._PRE_."groups VALUES (3, 0, 'Modérateur', 524291, 3500, 50, 400, 250)", $sql);
			insert_table(_PRE_."groups","INSERT INTO "._PRE_."groups VALUES (4, 0, 'Administrateur', 1572867, 3500, 50, 400, 250)", $sql);

		echo("</b></font>
	    </td>
	  </tr>
	</table><p>");
	
	if($ok)
		next_steps();
	}
	else
	{
		$_REQUEST['steps'] = 2;
		$forumname = $_POST['forumname'];
		$sitename = $_POST['sitename'];
		$siteurl = $_POST['siteurl'];
		$urlforum = $_POST['urlforum'];
		$mail = $_POST['mail'];
		$canmail = $_POST['canmail'];	
	}		
}

if($_REQUEST['steps']==2)
{
	$errorchain = "";
	if(isset($error) && strlen($error)>0)
		$errorchain = "<tr>
	    <td class=\"corp\" bgcolor=\"#265789\" colspan=2 align=center><font size=2><b>*** $error ***</b></font></td>
	  </tr>";
	else
	{
		$forumname 		= "";
		$sitename 		= "";
		$siteurl 		= "";
		$urlforum 		= "";
		$mail 			= "";
		
		$parse=parse_url($_SERVER['HTTP_REFERER']);
		$urlforum=$parse['scheme']."://".$parse['host']."/";
					
		$path=explode("/",$parse['path']);
		for($i=1;$i<count($path)-2;$i++)
			$urlforum.=$path[$i]."/";
	}


	echo("<u>Configuration du forum</u><p>

	<form action=\"install.php\" method=\"post\">
	<table border=1 bordercolor=\"black\" width=\"500\" cellpadding=2 cellspacing=0 style=\"border-collapse: collapse;\" align=center>
	$errorchain
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\" width=\"300\"><font size=2><b>Nom du Forum* :</b></font></td>
	    <td bgcolor=\"#537FAC\" width=\"200\"><input type=text name=\"forumname\" class=\"form\" value=\"$forumname\" size=\"30\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Nom du Site :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"sitename\" class=\"form\" value=\"$sitename\" size=\"30\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Url du site :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"siteurl\" class=\"form\" value=\"$siteurl\" size=\"30\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Url du forum* :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"urlforum\" class=\"form\" value=\"$urlforum\" size=\"30\"></td>
	  </tr>
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Email du contact* :</b></font></td>
	    <td bgcolor=\"#537FAC\"><input type=text name=\"mail\" class=\"form\" value=\"$mail\" size=\"30\"></td>
	  </tr>		
	  <tr>
	    <td class=\"corp\" bgcolor=\"#265789\"><font size=2><b>Votre hébergeur peut-il utiliser<BR>les fonctions d'envoi d'email en PHP?</b></font></td>
	    <td bgcolor=\"#537FAC\"><select name=\"canmail\" class=\"form\"><option value=\"Y\">Oui<option value=\"N\">Non</select></td>
	  </tr>
	</table><p>
	
	<font size=1>
	Pour l'url du forum, veuillez saisir l'url complète de votre forum.<br>
	Exemple: <B>http://www.coolforum.net/forum/</B>.<BR>
	N'oubliez pas le <B>http://</B> et le <B>/</B> à la fin. N'indiquez pas de fichier d'index.</font><P>
	
	<input type=\"hidden\" name=\"action\" value=\"install\">
	<input type=\"hidden\" name=\"steps\" value=\"".($_REQUEST['steps']+1)."\">
	<input type=\"submit\" value=\"Continuer ->>\" class=\"form\">
	</form>");	
	
}

if ($_REQUEST['steps']==1) {
	$ok = true;
	
	echo('<u>Création des tables nécessaires</u><p>

	<table border="1" bordercolor="black" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" align="center">
	  <tr>
	    <td class="corp" bgcolor="#537FAC">
	      <font size="2"><b>
	      <center><u>Création des tables en cours</u></center><p>');

        try {
            create_table(_PRE_."annonces","CREATE TABLE "._PRE_."annonces (
						  idpost int(11) NOT NULL auto_increment,
						  sujet varchar(200) default NULL,
						  date bigint(20) default NULL,
						  msg text,
						  nbvues int(10) default NULL,
						  datederrep bigint(20) default NULL,
						  derposter varchar(100) default NULL,
						  icone varchar(50) default NULL,
						  idmembre int(10) default NULL,
						  smiles enum('Y','N') NOT NULL default 'Y',
						  bbcode enum('Y','N') NOT NULL default 'Y',
						  inforums varchar(150) NOT NULL default '',
						  poll int(11) NOT NULL default '0',
						  PRIMARY KEY  (idpost)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."avatars","CREATE TABLE "._PRE_."avatars (
						  idlogo int(5) NOT NULL auto_increment,
						  ext varchar(5) NOT NULL default '',
						  PRIMARY KEY  (idlogo)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."banlist","CREATE TABLE "._PRE_."banlist (
   					userid int(11) DEFAULT '0' NOT NULL,
   					login text NOT NULL,
   					mail1 text NOT NULL,
   					mail2 text NOT NULL,
   					KEY userid (userid)
					) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."campagnes","CREATE TABLE "._PRE_."campagnes (
						  id int(11) NOT NULL auto_increment,
						  nom varchar(250) NOT NULL default '',
						  url varchar(250) NOT NULL default '',
						  banniere varchar(250) NOT NULL default '',
						  typefin enum('click','date','aff') NOT NULL default 'click',
						  dtedebut int(11) NOT NULL default '0',
						  fincamp int(11) NOT NULL default '0',
						  ratio int(1) NOT NULL default '0',
						  nbaffichages int(11) NOT NULL default '0',
						  clicks int(11) NOT NULL default '0',
						  regie text NOT NULL,
						  lastvue int(11) NOT NULL default '0',
						  todayvue int(11) NOT NULL default '0',
						  todayclick int(11) NOT NULL default '0',
						  PRIMARY KEY  (id)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."categorie","CREATE TABLE "._PRE_."categorie (
						  catid int(5) NOT NULL auto_increment,
						  cattitle varchar(200) default NULL,
						  catcoment varchar(250) NOT NULL default '',
						  catorder int(5) NOT NULL default '0',
						  KEY catid (catid)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."config","CREATE TABLE "._PRE_."config (
						  options varchar(50) NOT NULL default '',
						  valeur text NOT NULL,
						  PRIMARY KEY  (options)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."forums","CREATE TABLE "._PRE_."forums (
						  forumid int(10) NOT NULL auto_increment,
						  forumcat int(5) default NULL,
						  forumtitle varchar(100) default NULL,
						  forumcomment varchar(200) default NULL,
						  forumorder int(10) default NULL,
						  lastforumposter varchar(100) NOT NULL default '',
						  lastdatepost int(11) NOT NULL default '0',
						  lastidpost int(11) NOT NULL default '0',
						  forumtopic int(11) NOT NULL default '0',
						  forumposts int(11) NOT NULL default '0',
						  openforum enum('Y','N') NOT NULL default 'Y',
						  PRIMARY KEY  (forumid)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."groups","CREATE TABLE "._PRE_."groups (
						  id_group int(11) NOT NULL auto_increment,
						  parent int(11) NOT NULL default '0',
						  Nom_group varchar(100) NOT NULL default '',
						  Droits_generaux int(11) NOT NULL default '0',
						  Max_Pm int(11) NOT NULL default '0',
						  Max_Cit int(11) NOT NULL default '0',
						  Max_Sign int(11) NOT NULL default '0',
						  Max_Desc int(11) NOT NULL default '0',
						  PRIMARY KEY  (id_group),
						  KEY parent (parent)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."groups_perm","CREATE TABLE "._PRE_."groups_perm (
						  id_group int(11) NOT NULL default '0',
						  id_forum int(11) NOT NULL default '0',
						  droits int(11) NOT NULL default '0',
						  MaxChar int(11) NOT NULL default '0',
						  UNIQUE KEY id_group (id_group,id_forum)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."language","CREATE TABLE "._PRE_."language (
						  id int(11) NOT NULL auto_increment,
						  code varchar(6) NOT NULL default '',
						  langue varchar(20) NOT NULL default '',
						  PRIMARY KEY  (id)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."mailing","CREATE TABLE "._PRE_."mailing (
						  id int(11) NOT NULL auto_increment,
						  date bigint(20) NOT NULL default '0',
						  titre varchar(250) NOT NULL default '',
						  message text NOT NULL,
						  type enum('text','html') NOT NULL default 'text',
						  PRIMARY KEY  (id)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."moderateur","CREATE TABLE "._PRE_."moderateur (
						  forumident int(11) NOT NULL default '0',
						  idusermodo int(11) NOT NULL default '0',
						  modologin varchar(100) NOT NULL default '',
						  modoorder int(2) NOT NULL default '0',
						  modorights int(11) NOT NULL default '0',
						  KEY forumident (forumident)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."poll","CREATE TABLE "._PRE_."poll (
						  id int(11) NOT NULL auto_increment,
						  date int(11) NOT NULL default '0',
						  question text NOT NULL,
						  choix text NOT NULL,
						  rep text NOT NULL,
						  votants text NOT NULL,
						  PRIMARY KEY  (id)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."posts","CREATE TABLE "._PRE_."posts (
						  idpost int(11) NOT NULL auto_increment,
						  idforum int(10) default NULL,
						  sujet varchar(200) default NULL,
						  date bigint(20) default NULL,
						  parent int(10) default NULL,
						  msg text,
						  icone varchar(50) default NULL,
						  idmembre int(10) default NULL,
						  pseudo varchar(100) NOT NULL default '',
						  postip varchar(15) NOT NULL default '',
						  smiles enum('Y','N') NOT NULL default 'Y',
						  bbcode enum('Y','N') NOT NULL default 'Y',
						  poll int(11) NOT NULL default '0',
						  notifyme enum('Y','N') NOT NULL default 'N',
						  PRIMARY KEY  (idpost),
						  KEY idforum (idforum),
						  KEY idmembre (idmembre)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."privatemsg","CREATE TABLE "._PRE_."privatemsg (
						  id int(11) NOT NULL auto_increment,
						  iddest int(11) NOT NULL default '0',
						  idexp int(11) NOT NULL default '0',
						  date int(11) NOT NULL default '0',
						  pseudo varchar(100) NOT NULL default '',
						  sujet varchar(100) NOT NULL default '',
						  msg text NOT NULL,
						  vu int(2) NOT NULL default '0',
						  smiles enum('Y','N') NOT NULL default 'Y',
						  bbcode enum('Y','N') NOT NULL default 'Y',
						  PRIMARY KEY  (id)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."search","CREATE TABLE "._PRE_."search (
						  idsearch varchar(100) NOT NULL default '',
						  keyword varchar(200) NOT NULL default '',
						  time bigint(20) NOT NULL default '0',
						  search text NOT NULL,
						  PRIMARY KEY  (idsearch)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."session","CREATE TABLE "._PRE_."session (
						  sessionID varchar(200) NOT NULL default '',
						  username varchar(100) NOT NULL default '0',
						  userid int(11) NOT NULL default '0',
						  userstatus smallint(6) NOT NULL default '0',
						  time int(11) NOT NULL default '0',
						  typelieu enum('FOR','ACC','SEA','ADM','STA','HLP','PRO','TOP') NOT NULL default 'ACC',
						  forumid int(11) NOT NULL default '0',
						  topicid int(11) NOT NULL default '0',
						  UNIQUE KEY sessionID (sessionID)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."skins","CREATE TABLE "._PRE_."skins (
						  id smallint(6) NOT NULL default '0',
						  propriete varchar(15) NOT NULL default '',
						  valeur varchar(15) NOT NULL default '',
						  KEY id (id)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."smileys","CREATE TABLE "._PRE_."smileys (
						  idsmile int(5) NOT NULL auto_increment,
						  imgsmile varchar(50) NOT NULL default '',
						  codesmile varchar(20) NOT NULL default '',
						  ordersmile int(5) NOT NULL default '0',
						  PRIMARY KEY  (idsmile)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."statcamp","CREATE TABLE "._PRE_."statcamp (
						  iddate varchar(13) NOT NULL default '',
						  vu int(11) NOT NULL default '0',
						  clicks int(11) NOT NULL default '0',
						  UNIQUE KEY iddate (iddate)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."topics","CREATE TABLE "._PRE_."topics (
						  idtopic int(11) NOT NULL auto_increment,
						  idforum int(11) NOT NULL default '0',
						  sujet varchar(200) NOT NULL default '',
						  date bigint(20) NOT NULL default '0',
						  nbrep int(11) NOT NULL default '0',
						  nbvues int(11) NOT NULL default '0',
						  datederrep bigint(20) NOT NULL default '0',
						  derposter varchar(100) NOT NULL default '',
						  idderpost int(11) NOT NULL default '0',
						  icone varchar(50) NOT NULL default '',
						  idmembre int(11) NOT NULL default '0',
						  pseudo varchar(100) NOT NULL default '',
						  opentopic char(1) NOT NULL default '',
						  poll int(11) NOT NULL default '0',
						  postit char(1) NOT NULL default '0',
						  PRIMARY KEY  (idtopic)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."user","CREATE TABLE "._PRE_."user (
						  userid int(10) NOT NULL auto_increment,
						  login varchar(100) default NULL,
						  password varchar(200) default NULL,
						  userstatus int(2) default NULL,
						  registerdate bigint(20) default NULL,
						  usermsg int(10) default NULL,
						  usermail varchar(200) default NULL,
						  usersite varchar(200) default NULL,
						  usersign text,
						  usercitation varchar(200) NOT NULL default '',
						  showmail enum('Y','N') NOT NULL default 'Y',
						  showusersite enum('Y','N') NOT NULL default 'Y',
						  userlogo varchar(100) NOT NULL default '',
						  skin int(5) NOT NULL default '1',
						  timezone int(3) NOT NULL default '0',
						  lng varchar(5) NOT NULL default '',
						  notifypm enum('Y','N') NOT NULL default 'N',
						  popuppm enum('Y','N') NOT NULL default 'Y',
						  lastpost int(11) NOT NULL default '0',
						  lastvisit int(11) NOT NULL default '0',
						  nbpmtot int(11) NOT NULL default '0',
						  nbpmvu int(11) NOT NULL default '0',
						  mailing enum('Y','N') NOT NULL default 'Y',
						  wysiwyg enum('Y','N') NOT NULL default 'N',
						  PRIMARY KEY  (userid)
						) ENGINE=MyISAM
				", $sql);

            create_table(_PRE_."userplus","CREATE TABLE "._PRE_."userplus (
						  idplus int(11) NOT NULL default '0',
						  question varchar(200) NOT NULL default '',
						  reponse varchar(200) NOT NULL default '',
						  icq varchar(10) NOT NULL default '',
						  showicq enum('Y','N') NOT NULL default 'N',
						  aim varchar(16) NOT NULL default '',
						  yahoomsg varchar(50) NOT NULL default '',
						  msn varchar(50) NOT NULL default '',
						  birth varchar(10) NOT NULL default '',
						  sex enum('M','F') NOT NULL default 'M',
						  description text NOT NULL,
						  mailorig varchar(200) NOT NULL default '',
						  UNIQUE KEY idplus (idplus)
						) ENGINE=MyISAM
				", $sql);
        } catch (Exception $e) {
            $ok = false;
        }
        echo("</b></font>
	    </td>
	  </tr>
	</table><p>");
	
	if ($ok) {
		next_steps();
    }
	
}

if(empty($_REQUEST['steps']))
{
		
	echo("Vous vous apprêtez à installer <font color=\"red\">CoolForum v0.8 beta</font> sur votre serveur.<P>
	Avant de commencer à configurer votre forum, vérifiez que les tables suivantes n'existent pas déjà dans votre base de données MySQL:<BR>
	<table border=1 bordercolor=\"black\" cellpadding=10 cellspacing=0 style=\"border-collapse: collapse;\">
	  <TR>
	    <TD class=\"corp\" bgcolor=\"#537FAC\">
              <font size=2><b>
              - "._PRE_."annonces<BR>
              - "._PRE_."avatars<BR>
              - "._PRE_."banlist<BR>
              - "._PRE_."campagnes<br>
              - "._PRE_."categorie<BR>
              - "._PRE_."config<BR>
              - "._PRE_."forums<BR>
              - "._PRE_."groups<br>
              - "._PRE_."groups_perm<br>
              - "._PRE_."language<br>
              - "._PRE_."mailing<br>
              - "._PRE_."moderateur<BR>
              - "._PRE_."poll<BR>
              - "._PRE_."posts<BR>
              - "._PRE_."privatemsg<BR>
              - "._PRE_."search<BR>
              - "._PRE_."session<BR>
              - "._PRE_."skins<BR>
              - "._PRE_."smileys<BR>
              - "._PRE_."statcamp<br>
              - "._PRE_."topics<br>
              - "._PRE_."user<br>
              - "._PRE_."userplus
      	</b></font>
	</TD></TR></table><P>
	
	cliquez sur le bouton \"Continuer\" pour commencer la configuration du forum.<P>
	<FORM action=\"install.php\" method=\"get\">
	<input type=\"hidden\" name=\"action\" value=\"install\">
	<input type=\"hidden\" name=\"steps\" value=\"1\">
	<input type=\"submit\" value=\"Continuer ->>\" class=\"form\">	
	</form>");	

}
