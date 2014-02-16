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

getlangage("list");

// #### Initialisation des variables #### //////////////////////////////////////
$stringtoinsert				=	NULLSTR;
$strorderby					=	NULLSTR;
$strfromdate				=	NULLSTR;
$strsortby					=	NULLSTR;

$tpl->box['numberpages']	= 	NULLSTR;
$tpl->box['forumcontent']	=	NULLSTR;

$ForumInfo					=	array();
$checkorder					=	array(1 => NULLSTR, 2 => NULLSTR);
$checkfromdate				=	array(1 => NULLSTR, 2 => NULLSTR, 3 => NULLSTR);
$checksortby				=	array(1 => NULLSTR, 2 => NULLSTR, 3 => NULLSTR);
////////////////////////////////////////////////////////////////////////////////






// #### Filtres d'affichage #### ///////////////////////////////////////////////
if(empty($_GET['orderby']))			$_GET['orderby']	=	0;
if(empty($_GET['fromdate']))		$_GET['fromdate']	=	0;
if(empty($_GET['sortby']))			$_GET['sortby']		=	0;

	// test croissant ou décroissant
switch($_GET['orderby'])
{
	case "1":
		$order				=	"ASC";
		$strorderby			=	"&orderby=1";
		$checkorder[1]		=	" SELECTED";
		break;
	case "2":
		$order				=	"DESC";
		$strorderby			=	"&orderby=2";
		$checkorder[2]		=	" SELECTED";
		break;
	default :
		$order				=	"DESC";
		$strorderby			=	NULLSTR;
		$checkorder[2]		=	" SELECTED";
}

	// depuis quand on affiche
$now=time();
switch($_GET['fromdate'])
{
	case "1":
		$from 				= 	$now - (30*86400);
		$strfromdate		=	"&fromdate=1";
		$checkfromdate[1]	=	" SELECTED";
		break;
	case "2":
		$from 				= 	$now - (100*86400);
		$strfromdate		=	"&fromdate=2";
		$checkfromdate[2]	=	" SELECTED";
		break;
	default :
		$from 				= 	0;
		$strfromdate		=	"";
		$checkfromdate[3]	=	" SELECTED";
}

	// type de filtre
switch($_GET['sortby'])
{
	case "2":
		$sort 				= 	"date";
		$strsortby			=	"&sortby=1";
		$checksortby[2]		=	" SELECTED";
		break;
	case "3":
		$sort 				= 	"sujet";
		$strsortby			=	"&sortby=2";
		$checksortby[3]		=	" SELECTED";
		break;
	default :
		$sort 				= 	"datederrep";
		$strsortby			=	"";
		$checksortby[1]		=	" SELECTED";
}

$stringtoinsert=$strorderby.$strfromdate.$strsortby;
////////////////////////////////////////////////////////////////////////////////






// #### Définition du lieu #### ///////////////////////////////////////////////
$SessLieu	=	'FOR';
$SessForum	=	intval($_GET['forumid']);
$SessTopic	=	0;
////////////////////////////////////////////////////////////////////////////////






// #### Infos forum + page #### ////////////////////////////////////////////////
if(isset($_GET['page']))
	$_GET['page']			=	intval($_GET['page']);

require("entete.php");

$ForumInfo					=	getforumname($_GET['forumid'],$from);
$forumid 					= 	$ForumInfo['forumid'];

$nb 						= 	$ForumInfo['forumtopic'];
$nbtotmsg 					= 	$ForumInfo['forumtopic'] + $ForumInfo['forumposts'];

$query						=	$sql->query("SELECT COUNT(*) AS nbtotmsg FROM ".$_PRE."topics WHERE idforum='".$ForumInfo['forumid']."' AND date>$from");
list($nbtopics_filtered)	=	mysql_fetch_array($query);
////////////////////////////////////////////////////////////////////////////////






// #### gestion des cookies #### //////////////////////////////////////////////
if(isset($_COOKIE['listeforum_coolforum']))
      $zecook						=	cookdecode($_COOKIE['listeforum_coolforum']);

if(!isset($_COOKIE['listeforum_coolforum']) || !isset($zecook[$_GET['forumid']."m"]) || ($zecook[$_GET['forumid']."m"]!=$nbtotmsg))
    {
      $zecook[$_GET['forumid']."m"]	= 	$nbtotmsg;
      sendcookie("listeforum_coolforum",cookencode($zecook),-1);
    }

$cookiedetails						=	"CoolForumDetails";    
if(isset($_COOKIE[$cookiedetails]))
	$cookiespost					=	cookdecode($_COOKIE[$cookiedetails]);
////////////////////////////////////////////////////////////////////////////////






// #### Test droit visualisation sujets #### ///////////////////////////////////
if(!$_PERMFORUM[$_GET['forumid']][1])
	geterror("call_loginbox");
////////////////////////////////////////////////////////////////////////////////






// #### Gestion des connectés #### /////////////////////////////////////////////
$InfoMember							=	get_connected($SessLieu,$SessForum);

if($_FORUMCFG['conn_forum'] == "Y")
{
	$tpl->box['statsconnectes']		=	NULLSTR;
	$tpl->box['nb_connected']		=	$tpl->attlang("board_connected");
	if(!empty($InfoMember['listconnected']) && strlen($InfoMember['listconnected'])>0)
	{
		if($_GENERAL[0])
			$tpl->box['statsconnectes'] = $tpl->gettemplate("entete","statsconnectes");
		$tpl->box['listconnected']	=	$tpl->gettemplate("entete","listconnectes");
	}
	else
		$tpl->box['listconnected']	=	NULLSTR;
			
	$tpl->box['boxconnected']		=	$tpl->gettemplate("entete","boxconnectes");
}
else
	$tpl->box['boxconnected']		=	NULLSTR;
////////////////////////////////////////////////////////////////////////////////






// #### Navigation #### ////////////////////////////////////////////////////////

$ForumInfo['cattitle']			=	getformatrecup($ForumInfo['cattitle']);
$ForumInfo['forumtitle']		=	getformatrecup($ForumInfo['forumtitle']);
$tpl->treenavs					=	$tpl->gettemplate("treenav","treelist");
$cache						   .=	$tpl->gettemplate("treenav","hierarchy");
////////////////////////////////////////////////////////////////////////////////






// #### Gestion des pages pour requete #### ////////////////////////////////////
if(!isset($_GET['page']) || empty($_GET['page']))		$page	=	1;
else													$page	= intval($_GET['page']);

$tpl->box['navpages']			=	getnumberpages($nbtopics_filtered,"list",$_FORUMCFG['topicparpage'],$page);
if($nbpages>1)
	$tpl->box['numberpages']	=	$tpl->gettemplate("list","boxpages");

$debut							=	($page*$_FORUMCFG['topicparpage']) - $_FORUMCFG['topicparpage'];
////////////////////////////////////////////////////////////////////////////////






// #### Affichage des annonces #### ////////////////////////////////////////////
$resultat						=	$sql->query("SELECT ".$_PRE."annonces.idpost,".$_PRE."annonces.sujet,".$_PRE."annonces.nbvues,".$_PRE."annonces.datederrep,".$_PRE."annonces.derposter,".$_PRE."annonces.icone,".$_PRE."annonces.idmembre,".$_PRE."user.login AS pseudo, ".$_PRE."user.userstatus, ".$_PRE."user.userid FROM ".$_PRE."annonces LEFT JOIN ".$_PRE."user ON ".$_PRE."annonces.idmembre=".$_PRE."user.userid WHERE ".$_PRE."annonces.inforums REGEXP\"/".$ForumInfo['forumid']."/\" ORDER BY ".$_PRE."annonces.date DESC");
$nbannonces						=	mysql_num_rows($resultat);

if($nbannonces>0)
	while($Topics = mysql_fetch_array($resultat))
		$tpl->box['forumcontent'] .= afftopiclist(1);
////////////////////////////////////////////////////////////////////////////////






// #### Affichage des sujets #### //////////////////////////////////////////////
$resultat 	= $sql->query("SELECT 
				".$_PRE."topics.idtopic,
				".$_PRE."topics.sujet,
				".$_PRE."topics.nbrep,
				".$_PRE."topics.nbvues,
				".$_PRE."topics.datederrep,
				".$_PRE."topics.derposter,
				".$_PRE."topics.idderpost,
				".$_PRE."topics.icone,
				".$_PRE."topics.idmembre,
				".$_PRE."topics.pseudo,
				".$_PRE."topics.opentopic,
				".$_PRE."topics.poll,
				".$_PRE."topics.postit, 
				".$_PRE."user.userid,
				".$_PRE."user.userstatus 
			FROM ".$_PRE."topics 
			LEFT JOIN ".$_PRE."user ON ".$_PRE."topics.idmembre=".$_PRE."user.userid 
			WHERE ".$_PRE."topics.idforum='".$ForumInfo['forumid']."' AND datederrep>$from ORDER BY ".$_PRE."topics.postit DESC, ".$_PRE."topics.$sort $order LIMIT ".$debut.",".$_FORUMCFG['topicparpage']);
  
$total		=	mysql_num_rows($resultat);

if($total==0)
	$tpl->box['forumcontent'].=$tpl->gettemplate("list","ifnomsg");
else
{
	while($Topics = mysql_fetch_array($resultat))
		$tpl->box['forumcontent'].=afftopiclist();
}
////////////////////////////////////////////////////////////////////////////////






// #### Divers permissions + options #### //////////////////////////////////////
if(isset($_PERMFORUM[$ForumInfo['forumid']][2]) && $_PERMFORUM[$ForumInfo['forumid']][2])		$tpl->box['canread']  	=  $tpl->attlang("youcan");
else																							$tpl->box['canread']  	=  $tpl->attlang("youcant");
	
if(isset($_PERMFORUM[$ForumInfo['forumid']][4]) && $_PERMFORUM[$ForumInfo['forumid']][4])		$tpl->box['cantopic']  	=  $tpl->attlang("youcan");
else																							$tpl->box['cantopic']  	=  $tpl->attlang("youcant");
	
if(isset($_PERMFORUM[$ForumInfo['forumid']][3]) && $_PERMFORUM[$ForumInfo['forumid']][3])		$tpl->box['canrep']    	=  $tpl->attlang("youcan");
else																							$tpl->box['canrep']    	=  $tpl->attlang("youcant");

if(isset($_PERMFORUM[$ForumInfo['forumid']][5]) && $_PERMFORUM[$ForumInfo['forumid']][5])
{
	$tpl->box['buttonpoll']			= 	trim($tpl->gettemplate("list","buttonaddpoll"));
	$tpl->box['canpoll'] 			= 	$tpl->attlang("youcan");
}
else
{
	$tpl->box['buttonpoll']			= 	NULLSTR;
	$tpl->box['canpoll'] 			= 	$tpl->attlang("youcant");
}

if($_USER['userid'] > 0)
	$tpl->box['searchmy']			=	$tpl->gettemplate("list","searchmy");
else
	$tpl->box['searchmy']			=	NULLSTR;

if ($_FORUMCFG['forumjump']=="Y")
{
	getjumpforum();
	$tpl->box['boxforumjump']		=	$tpl->gettemplate("list","boxforumjump");
}

$tpl->box['debut'] 					= $debut+1;
$tpl->box['fin']   					= $debut+$_FORUMCFG['topicparpage'];

$cache.=$tpl->gettemplate("list","boxlist");
////////////////////////////////////////////////////////////////////////////////






$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
	

