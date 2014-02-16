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
$SessLieu				=	'ACC';
$SessForum				=	0;
$SessTopic				=	0;
//////////////////////////////


$tablo=parse_url($_SERVER['HTTP_REFERER']);

if(!isset($tablo['path']))		$tablo['path'] = NULLSTR;
if(!isset($tablo['query']))		$tablo['query'] = NULLSTR;
$redirecturl=basename($tablo['path'])."?".$tablo['query'];
//echo("test:" $redirecturl);

if(empty($_REQUEST['action']))
{
	$pseudo=getformatmsg($_POST['pseudo'],false);
	$query=$sql->query("SELECT userid,login,password,usermail,userstatus FROM ".$_PRE."user WHERE login='$pseudo'");
	$nb=mysql_num_rows($query);
	
	if($nb==0)
		header("location: identify.php?error=1");
	
	else
	{
		$j=mysql_fetch_array($query);
		if($j['userstatus']==0)
		{
			require("entete.php");
			if($_FORUMCFG['confirmparmail']==3)
				geterror("confirmregister");
			else
				geterror("waitforadmin");
		}
		$tmp=rawurldecode($j['password']);
		$passwd=getdecrypt($tmp,$_FORUMCFG['chainecodage']);
		
		if($passwd==$_POST['password'])
		{
			$send['userid']=$j['userid'];
			$send['username']=$j['login'];
			$send['userpass']=$tmp;
			
			switch ($_POST['duree'])
			{
			case "0":
				sendcookie("CoolForumID",urlencode(serialize($send)),time()+3600*24);
				break;
			case "1":
				sendcookie("CoolForumID",urlencode(serialize($send)),time()+86400*30);
				break;
			case "2":
				sendcookie("CoolForumID",urlencode(serialize($send)),time()+86400*365);
				break;
			case "3":
				sendcookie("CoolForumID",urlencode(serialize($send)),mktime(0,0,0,1,1,2010));
				break;
			}
			
			if(!isset($_COOKIE['CF_LastINI']))
				sendcookie("CF_LastINI",time(),-1);
			
			if(isset($_COOKIE['CF_sessionID']))
			{
				$_COOKIE['CF_sessionID'] = getrecupfromcookie($_COOKIE['CF_sessionID']);
				$now	=	time();
				$query	=	$sql->query("DELETE FROM ".$_PRE."session WHERE username='$pseudo'");
				$query	=	$sql->query("UPDATE ".$_PRE."session SET username='$pseudo', userid=".$j['userid'].", userstatus=".$j['userstatus'].", time=$now  WHERE sessionID='".$_COOKIE['CF_sessionID']."'");	
			}
			
			if(isset($_POST['backurl']))
			{
				$tablo=parse_url($_POST['referrer']);
				$redirecturl=basename($tablo['path'])."?".$tablo['query'];	
				header("location: $redirecturl");
			}
			else
				header("location: index.php");
			exit; 
		}
		else
			header("location: identify.php?error=1");
	}
}
if($_REQUEST['action']=="savepoll")
{

	$_USER = array(); // renseignement du membre
	$_USER=getuserid();

	if(isset($_POST['choixpoll']))
	{
		$choixpoll 	= 	intval($_POST['choixpoll']);
		$idpoll		=	intval($_POST['idpoll']);
		$id		=	intval($_POST['id']);
		$forumid	=	intval($_POST['forumid']);		
		
		$query	=	$sql->query("SELECT * FROM ".$_PRE."poll WHERE id='$idpoll'");
		$nb	=	mysql_num_rows($query);
		
		if($nb==1)
		{
			$j	=	mysql_fetch_array($query);
			
			$nbrep	=	explode(" >> ",$j['rep']);
			
			if($choixpoll >= 0 && $choixpoll < count($nbrep) && preg_match("|-".$_USER['userid']."-|",$j['votants']) == 0)
			{
				$nbrep[$choixpoll]++;
				
				$chainerep 	= 	implode(" >> ",$nbrep);
				$participant	=	$j['votants'].$_USER['userid']."-";
				$query 		= 	$sql->query("UPDATE ".$_PRE."poll SET rep='$chainerep', votants='$participant' WHERE id=".$j['id']);
			}
		}
	}
	
	header("location: $redirecturl");
}
