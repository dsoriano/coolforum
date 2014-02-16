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

require("secret/connect.php"); 
require("admin/functions.php");

// #### définition du lieu ###
$SessLieu	=	'ACC';
$SessForum	=	0;
$SessTopic	=	0;
//////////////////////////////

require("entete.php");

getlangage("identify");

$tpl->box['logincontent']=NULLSTR;

if($_REQUEST['action']=="questrep")
{
	//$pseudo=$_POST['pseudo'];
	$iduser		=	intval($_POST['idident']);
	$repuser	=	getformatmsg($_POST['repuser'],false);
	$query		=	$sql->query("SELECT ".$_PRE."userplus.reponse,".$_PRE."user.password FROM ".$_PRE."userplus LEFT JOIN ".$_PRE."user ON ".$_PRE."userplus.idplus=".$_PRE."user.userid WHERE ".$_PRE."userplus.idplus=$iduser AND ".$_PRE."userplus.reponse='$repuser'");
	$nb		=	mysql_num_rows($query);
	
	if($nb==1 && $_FORUMCFG['confirmparmail']==0)
	{
		$j=mysql_fetch_array($query);
		if(strlen($j['reponse'])==0)
			$tpl->box['logincontent']		=	$tpl->attlang("notquestrep");
		else
		{
			$tmp=rawurldecode($j['password']);
			$tpl->tmp['passwd']		=	getdecrypt($tmp,$_FORUMCFG['chainecodage']);
			$tpl->box['logincontent']		=	$tpl->gettemplate("identify","affmdp");
		}
	}
	else
		$tpl->box['logincontent']			=	$tpl->attlang("errordonnees");
}

if($_REQUEST['action']=="sendmdp")
{
	$pseudo	=	getformatmsg($_POST['pseudo'],false);
	$mail 	= 	getformatmsg($_POST['mail']);
	
	$query = $sql->query("SELECT ".$_PRE."user.userid,".$_PRE."user.login,".$_PRE."user.password,".$_PRE."user.usermail,".$_PRE."userplus.question,".$_PRE."userplus.reponse FROM ".$_PRE."user LEFT JOIN ".$_PRE."userplus ON ".$_PRE."userplus.idplus=".$_PRE."user.userid WHERE ".$_PRE."user.login='$pseudo' AND ".$_PRE."user.usermail='$mail'");
	$nb=mysql_num_rows($query);
	
	if($nb==1)
	{
		$Result=mysql_fetch_array($query);
		if($_FORUMCFG['confirmparmail']==0)
		{
			if(strlen($Result['reponse'])==0 || strlen($Result['reponse'])==0)
				$tpl->box['logincontent']		=	$tpl->attlang("notquestrep");
			else
			{
				$Result['question'] = getformatrecup($Result['question']);
				$tpl->box['logincontent']		=	$tpl->gettemplate("identify","askquestrep");
			}
		}
		else
		{
			$tmp				=	rawurldecode($Result['password']);
			$passwd				=	getdecrypt($tmp,$_FORUMCFG['chainecodage']);

			$forumname	=	$_FORUMCFG['mailforumname'];
			$passwd		=	formatstrformail(stripslashes(recupDBforMail($passwd)));
		
			eval("\$msg			=	\"".$tpl->attlang("mailmsg")."\";");
			eval("\$subject			=	".$tpl->attlang("mailsubject").";");
			$email				=	$Result['usermail'];
		
			if(!sendmail($email,$subject,$msg))
				$tpl->box['logincontent']	=	$tpl->attlang("errormail");
			else
				$tpl->box['logincontent']	=	$tpl->attlang("mailok");
		}
	}
	else
	{
		$tpl->box['logincontent']=$tpl->attlang("errordonnees");
		$GLOBALS['action']="mdp";
	}
		
}

if($_REQUEST['action']=="mdp")
	$tpl->box['logincontent']=$tpl->gettemplate("identify","askmdpbox");

if(empty($_REQUEST['action']))
{
	if($_USER['userid']==0)
	{
		if(isset($_GET['error']) && $_GET['error'] == 1)
			$tpl->box['errorbox']	=	$tpl->gettemplate("identify","errorbox");
		else
			$tpl->box['errorbox']	=	NULLSTR;
			
		$tpl->box['logincontent']=$tpl->gettemplate("identify","loginbox");
	}
	else
		$tpl->box['logincontent']=$tpl->gettemplate("identify","alreadylogged");
}

$cache.=$tpl->gettemplate("identify","identifyaccueil");

$tps = number_format(get_microtime() - $tps_start,4);
$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
