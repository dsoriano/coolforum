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


use Database\Database_MySQLi;

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
$_SESSION['SessLieu']	=	_LOCATION_FORUM_;
$_SESSION['SessForum']	=	(int)$_GET['forumid'];
$_SESSION['SessTopic']	=	0;
////////////////////////////////////////////////////////////////////////////////






// #### Infos forum + page #### ////////////////////////////////////////////////
if(isset($_GET['page']))
	$_GET['page']			=	intval($_GET['page']);

require("entete.php");

$ForumInfo					=	getforumname($_GET['forumid'],$from);
$forumid 					= 	$ForumInfo['forumid'];

$nb 						= 	$ForumInfo['forumtopic'];
$nbtotmsg 					= 	$ForumInfo['forumtopic'] + $ForumInfo['forumposts'];

$query						=	$sql->query("SELECT COUNT(*) AS nbtotmsg FROM "._PRE_."topics WHERE idforum=%d AND date>%d", array($ForumInfo['forumid'], $from))->execute();
list($nbtopics_filtered)	=	$query->fetch_array();
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
$InfoMember							=	get_connected();

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
$resultat						=	$sql->query("SELECT "._PRE_."annonces.idpost,"._PRE_."annonces.sujet,"._PRE_."annonces.nbvues,"._PRE_."annonces.datederrep,"._PRE_."annonces.derposter,"._PRE_."annonces.icone,"._PRE_."annonces.idmembre,"._PRE_."user.login AS pseudo, "._PRE_."user.userstatus, "._PRE_."user.userid FROM "._PRE_."annonces LEFT JOIN "._PRE_."user ON "._PRE_."annonces.idmembre="._PRE_."user.userid WHERE "._PRE_."annonces.inforums REGEXP\"/%d/\" ORDER BY "._PRE_."annonces.date DESC", $ForumInfo['forumid'])->execute();
$nbannonces						=	$resultat->num_rows();

if($nbannonces>0)
	while($Topics = $resultat->fetch_array())
		$tpl->box['forumcontent'] .= afftopiclist(1);
////////////////////////////////////////////////////////////////////////////////






// #### Affichage des sujets #### //////////////////////////////////////////////
$resultat 	= $sql->query("SELECT 
				"._PRE_."topics.idtopic,
				"._PRE_."topics.sujet,
				"._PRE_."topics.nbrep,
				"._PRE_."topics.nbvues,
				"._PRE_."topics.datederrep,
				"._PRE_."topics.derposter,
				"._PRE_."topics.idderpost,
				"._PRE_."topics.icone,
				"._PRE_."topics.idmembre,
				"._PRE_."topics.pseudo,
				"._PRE_."topics.opentopic,
				"._PRE_."topics.poll,
				"._PRE_."topics.postit, 
				"._PRE_."user.userid,
				"._PRE_."user.userstatus 
			FROM "._PRE_."topics 
			LEFT JOIN "._PRE_."user ON "._PRE_."topics.idmembre="._PRE_."user.userid 
			WHERE "._PRE_."topics.idforum=%d AND datederrep>%d ORDER BY "._PRE_."topics.postit DESC, "._PRE_."topics.%s %s LIMIT %d,%d", array($ForumInfo['forumid'], $from, $sort, $order, $debut, $_FORUMCFG['topicparpage']))->execute();

$total		=	$resultat->num_rows();

if($total==0)
	$tpl->box['forumcontent'].=$tpl->gettemplate("list","ifnomsg");
else
{
	while($Topics = $resultat->fetch_array())
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




session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);


