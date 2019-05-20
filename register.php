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

require("admin/functions.php");

// #### définition du lieu ###
$_SESSION['SessLieu']	=	_LOCATION_HOME_;
$_SESSION['SessForum']	=	0;
$_SESSION['SessTopic']	=	0;
//////////////////////////////

require("entete.php");

getlangage("register");

if($_USER['userid']==0)
{
	$error	=	NULLSTR;

	if($_FORUMCFG['openinscriptions']=="Y")
	{

		// #### Enregistrement du membre ####
		if($_REQUEST['action']=="save")
		{
			$error="";

			//**** test du pseudo ****
			$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['pseudo']);
			if(strlen($testchain)==0)
				$error	=	$tpl->attlang("errorpseudo1");

			$rgpseudo	=	trim($_POST['pseudo']);
			$rgpseudo	=	getformatmsg($rgpseudo,false);
			$query		=	$sql->query("SELECT COUNT(*) AS nbpseudos FROM "._PRE_."user WHERE login='%s'", $rgpseudo)->execute();
			list($nbpseudos)=$query->fetch_array();
			if ($nbpseudos>0)
				$error	=	$tpl->attlang("errorpseudo2");

			// test du mot de passe:
			$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['password1']);
			if(strlen($testchain)==0)
				$error	=	$tpl->attlang("errormdp1");

			if($_POST['password1']!=$_POST['password2'])
				$error	=	$tpl->attlang("errormdp2");

			// test de l'email
			if(!testemail($_POST['email']))
				$error	=	$tpl->attlang("errormail1");
			else
			{
				$regemail=$_POST['email'];
				$query=$sql->query("SELECT COUNT(*) AS nbmail1 FROM "._PRE_."user WHERE usermail='%s'", $regemail)->execute();
				list($nbmail1)=$query->fetch_array();

				$query=$sql->query("SELECT COUNT(*) AS nbmail2 FROM "._PRE_."userplus WHERE mailorig='%s'", $regemail)->execute();
				list($nbmail2)=$query->fetch_array();

				if($nbmail1>0 || $nbmail2>0)
					$error=$tpl->attlang("errormail2");
			}

			// test question/réponse si confirmation d'email désactivée
			if ($_FORUMCFG['confirmparmail'] < 2)
			{
				$testchain=preg_replace("/([\s]{1,})/","",$_POST['question']);
				if(strlen($testchain)==0)
					$error=$tpl->attlang("errorquest");
				$testchain=preg_replace("/([\s]{1,})/","",$_POST['reponse']);
				if(strlen($testchain)==0)
					$error=$tpl->attlang("errorrep");
			}

			if(strlen($error)==0)
			{
				$date=time();
				$send=array();

				$send['userpass']=getencrypt($_POST['password1'],$_FORUMCFG['chainecodage']);
				$password=rawurlencode($send['userpass']);

				$query=$sql->query("INSERT INTO "._PRE_."user (login,password,userstatus,registerdate,usermsg,usermail,skin,timezone,lng) VALUES ('%s','%s',0,'%s',0,'%s','%s','%s','%s')", array($rgpseudo, $password, $date, $regemail, $_FORUMCFG['defaultskin'], $_FORUMCFG['defaulttimezone'], $_FORUMCFG['defaultlangage']))->execute();
				$rguserid = $sql->insertId();

				if($_FORUMCFG['confirmparmail']=="3")
				{
				    $mailpseudo	=	urlencode(trim($_POST['pseudo']));

					$password = md5($password);

					$forumname	=	$_FORUMCFG['mailforumname'];

					eval("\$subject = ".$tpl->attlang("mailsujet").";");
					eval("\$msg = ".$tpl->attlang("mailmsg").";");

					$question="";
					$reponse="";

					if(!sendmail($regemail,$subject,$msg))
						$tpl->box['infomsg']=$tpl->attlang("cantsendmail");
					else
						$tpl->box['infomsg']=$tpl->attlang("mailsent");

				}
				elseif($_FORUMCFG['confirmparmail']==0)
				{
					$question=getformatmsg($_POST['question']);
					$reponse=getformatmsg($_POST['reponse']);

					$query=$sql->query("UPDATE "._PRE_."user SET userstatus=2 WHERE userid=%d", $rguserid)->execute();
					updatemembers();
					$tpl->box['infomsg']=$tpl->attlang("registerok");

					$send['userid']=$rguserid;
					$send['username']=$rgpseudo;
					sendcookie("CoolForumID",urlencode(serialize($send)),time()+86400*30);

				}
				else
				{
					if(!isset($_POST['question']))		$_POST['question']=NULLSTR;
					if(!isset($_POST['reponse']))		$_POST['reponse']=NULLSTR;
					$question=getformatmsg($_POST['question']);
					$reponse=getformatmsg($_POST['reponse']);

					$tpl->box['infomsg']=$tpl->attlang("waitforadmin");
				}

				$query=$sql->query("INSERT INTO "._PRE_."userplus(idplus,question,reponse,mailorig) VALUES (%d,'%s','%s','%s')", array($rguserid, $question, $reponse, $regemail))->execute();

				$tpl->box['content']=$tpl->gettemplate("register","infobox");
			}
			else
			{
				$tpl->box['errorbox']=$tpl->gettemplate("register","errorbox");
				$Result=$_POST;
				$Result['pseudo']	=	getrecupforform($Result['pseudo']);
				$_REQUEST['action']="formulaire";
			}
		}


		// #### Formulaire d'enregistrement ####
		if($_REQUEST['action']=="formulaire")
		{
			if(strlen($error)>0)
				$tpl->box['errorbox']=$tpl->gettemplate("register","errorbox");
			else
				$tpl->box['errorbox']=NULLSTR;

			if($_FORUMCFG['confirmparmail'] < 2)
				$tpl->box['isquestion']=$tpl->gettemplate("register","questrepform");
			else
				$tpl->box['isquestion']=NULLSTR;

			$tpl->box['content']=$tpl->gettemplate("register","registerform");
		}
	}
	else
	{
		$tpl->box['infomsg']=getformatrecup($_FORUMCFG['closeregmsg']);
		$tpl->box['content']=$tpl->gettemplate("register","infobox");
	}


	// #### Confirmation par mail ####
	if($_REQUEST['action']=="confirm" && $_FORUMCFG['confirmparmail']==3)
	{
		$login		=	getformatmsg(urldecode($_GET['login']));

		////////////////////////////////////////////////////////////////
		$query = $sql->query("SELECT userid,login,password,usermail,userstatus FROM "._PRE_."user WHERE login='%s'", $login)->execute();
		$nb=$query->num_rows();
		if($nb==1)
		{
			$j=$query->fetch_array();
			$pass1 = md5($j['password']);
			$pass2 = $_GET['s'];
			if($pass1==$pass2)
			{
				if($j['userstatus'] == 0)
				{
					$query = $sql->query("UPDATE "._PRE_."user SET userstatus=2 WHERE login='%s'", $login)->execute();
					if($query)
					{
						updatemembers();

						$send=array();
						$send['userid']=$j['userid'];
						$send['username']=$j['login'];
						$send['userpass']=rawurldecode($j['password']);
						sendcookie("CoolForumID",urlencode(serialize($send)),time()+86400*30);

						$tpl->box['infomsg']=$tpl->attlang("confirmok");
					}
				}
				else
					$tpl->box['infomsg']=$tpl->attlang("alreadyconfirm");
			}
			else
				$tpl->box['infomsg']=$tpl->attlang("confirmnotok");
		}
		else
			$tpl->box['infomsg']=$tpl->attlang("confirmnotok");

		$tpl->box['content']=$tpl->gettemplate("register","infobox");
	}

	if(empty($_REQUEST['action']))
	{
		if($_FORUMCFG['openinscriptions']=="Y")
			$tpl->box['content']=$tpl->gettemplate("register","charte");
		else
		{
			$tpl->box['infomsg']=getformatrecup($_FORUMCFG['closeregmsg']);
			$tpl->box['content']=$tpl->gettemplate("register","infobox");
		}
	}
}
else
{
	$tpl->box['infomsg']=$tpl->attlang("alreadylogged");
	$tpl->box['content']=$tpl->gettemplate("register","infobox");
}

$cache.=$tpl->gettemplate("register","accueilregister");

session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
