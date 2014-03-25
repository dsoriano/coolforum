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

require("functions.php");

// #### Initialisation des variables #### //////////////////////////////////////
$_USER 				= 	array();	// renseignement du membre
$_PERMCAT 			= 	array(); 	// permissions sur catégories
$_PERMFORUM 		= 	array(); 	// permissions sur forums
$_SKIN 				= 	array(); 	// infos sur skin
$_GENERAL 			= 	array(		false,	false,	false,	false,
									false,	false,	false,	false,
									false,	false,	false,	false,
									false,	false,	false,	false,
									false,	false,	false,	false, false);
$cache				=	NULLSTR;
$error				=	NULLSTR;

if(empty($_REQUEST['action']) || !isset($_REQUEST['action']))		$_REQUEST['action']	=	NULLSTR;
////////////////////////////////////////////////////////////////////////////////






// #### Initialisation membre + langage #### ///////////////////////////////////
$_USER						=	getuserid();
getlangage("adm_entete");
////////////////////////////////////////////////////////////////////////////////






// #### Chargement du skin #### ///////////////////////////////////////////////
getskin();
$_SKIN['repimg']				=	"../skins/".$_SKIN['repimg'];

if($_SKIN['affdegrad']=="Y")
	$tpl->box['affdegrad'] 		= 	$tpl->gettemplate("adm_entete","affdegrad");
else
	$tpl->box['affdegrad'] 		= 	NULLSTR;

	//Initialisation des couleurs des groupes
$tpl->box['grpcolor'] 			= 	NULLSTR;

foreach($ListColorGroup AS $gpcolor)
{
	$groupcolor 				= 	$_SKIN['grp'.$gpcolor];
	$tpl->box['grpcolor'] 	   .=	$tpl->gettemplate("adm_entete","groupscolor");	
}
////////////////////////////////////////////////////////////////////////////////


if(preg_match("|MSIE|", $_SERVER['HTTP_USER_AGENT']) > 0)
{
	define("NAVIGATEUR","MSIE");
	$tpl->box['cssform']	=	$tpl->gettemplate("adm_entete","formie");
}
elseif(preg_match("|Mozilla/5.0|", $_SERVER['HTTP_USER_AGENT']) > 0)
{
	define("NAVIGATEUR","MOZILLA");
	$tpl->box['cssform']	=	$tpl->gettemplate("adm_entete","formie");
}
else
	$tpl->box['cssform']	=	$tpl->gettemplate("adm_entete","formns");

$cache.=$tpl->gettemplate("adm_entete","htmlheader");

if(!isset($nocache))
	$cache.=$tpl->gettemplate("adm_entete","logobar");

//////////////////////////////////////////////////////////////////////

$error="";

if(file_exists("../install/install.php"))
	$error = $tpl->attlang('errinstall');

if($_REQUEST['action']=="login")
{
	$pseudo		=		getformatmsg($_POST['pseudo'],false);
	$query		=		mysql_query("SELECT userid,login,password,usermail,userstatus FROM "._PRE_."user WHERE login='$pseudo'");
	$nb			=		mysql_num_rows($query);
	
	if($nb==0)
		$error = $tpl->attlang('errnotfound');
	
	else
	{
		$j=mysql_fetch_array($query);
		$tmp=rawurldecode($j['password']);
		$passwd=getdecrypt($tmp,$_FORUMCFG['chainecodage']);
		
		if($passwd==$_POST['password'])
		{
			$send['userid']=$j['userid'];
			$send['username']=$j['login'];
			$send['userpass']=$tmp;
			
			sendcookie("CoolForumID",urlencode(serialize($send)),time()+3600*24);
			
			if(!isset($_COOKIE['CF_LastINI']))
				sendcookie("CF_LastINI",time(),-1);
			
			header("location: ".$_SERVER['PHP_SELF']);
			exit; 
		}
		else
			$error = $tpl->attlang('errlogin');
	}
	$_REQUEST['action'] = NULLSTR;
}

if($_GENERAL[20] && strlen($error)==0)
{
	if(!isset($nocache))
		$cache.=$tpl->gettemplate("adm_entete","interface");
}
else
{
	if(strlen($error)>0)
		$tpl->box['error']=$tpl->gettemplate("adm_entete","afferror");
	else
		$tpl->box['error']=NULLSTR;
	
	$cache.=$tpl->gettemplate("adm_entete","formulaire");
	
	include("bas.php");
	exit;

}
