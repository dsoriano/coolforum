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

$tpl->box['numberpages'] = NULLSTR;
$tpl->box['affpoll'] = NULLSTR;
$_GET['id'] = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$_GET['forumid'] = isset($_GET['forumid']) ? (int)$_GET['forumid'] : 0;
$PrintRedirect = NULLSTR;

if ($_GET['id'] == 0 || $_GET['forumid'] == 0) {
	geterror("novalidlink");
}

// #### définition du lieu ###
$SessLieu	=	'TOP';
$SessForum	=	$_GET['forumid'];
$SessTopic	=	$_GET['id'];
//////////////////////////////


$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$TopicInfo = gettopictitle($_GET['id']);

require("entete.php");

if (!$TopicInfo || $TopicInfo['idforum'] != $_GET['forumid']) {
	geterror("novalidlink");
}

getlangage("detail");

$table_smileys = getloadsmileys();
$ForumInfo = getforumname($_GET['forumid']);
$nbtotmsg = $ForumInfo['forumtopic'] + $ForumInfo['forumposts'];
$nbtotalmsg = $TopicInfo['nbrep']+1;

//----------- gestion des cookies ------------------------

$cookiespost = array();
if (isset($_COOKIE['listeforum_coolforum'])) { // si le cookie total message existe on le décode et l'extrait
      $zecook=cookdecode($_COOKIE['listeforum_coolforum']);
}

if (!isset($_COOKIE['listeforum_coolforum']) || !isset($zecook[$_GET['forumid']."m"]) || ($zecook[$_GET['forumid']."m"]!=$nbtotmsg)) {
    $zecook[$_GET['forumid']."m"] = $nbtotmsg;
    sendcookie("listeforum_coolforum",cookencode($zecook),-1);
}

$cookiedetails="CoolForumDetails";    
if (isset($_COOKIE[$cookiedetails])) {
	$cookiespost=cookdecode($_COOKIE[$cookiedetails]);
}

$IdString = $_GET['id'];

if (!isset($_COOKIE[$cookiedetails]) || !isset($cookiespost[$IdString."m"]) || ($cookiespost[$IdString."m"]!=$nbtotalmsg)) {
    addToArray($cookiespost,$IdString."m",$nbtotalmsg);

    if (count($cookiespost)>200) {
        $cookposttransfert	=	array_slice($cookiespost,-200,200);
    } else {
        $cookposttransfert	= 	$cookiespost;
    }

    sendcookie($cookiedetails,cookencode($cookposttransfert),-1);
}
	
if (!$_PERMFORUM[$_GET['forumid']][2]) {
	geterror("call_loginbox");
}

// #### Connectés ####
$InfoMember	=	get_connected($SessLieu, $SessForum, $SessTopic);

if ($_FORUMCFG['conn_topic'] == "Y") {
    $tpl->box['nb_connected'] = $tpl->attlang("board_connected");
    if (!empty($InfoMember['listconnected']) && strlen($InfoMember['listconnected'])>0) {
        if ($_GENERAL[0]) {
            $tpl->box['statsconnectes'] = $tpl->gettemplate("entete","statsconnectes");
        }
        $tpl->box['listconnected'] = $tpl->gettemplate("entete","listconnectes");
    } else {
        $tpl->box['listconnected'] = NULLSTR;
    }

    $tpl->box['boxconnected'] = $tpl->gettemplate("entete","boxconnectes");
} else {
	$tpl->box['boxconnected'] = "";
}

// ###### Navigation ######

$ForumInfo['cattitle'] = getformatrecup($ForumInfo['cattitle']);
$ForumInfo['forumtitle'] = getformatrecup($ForumInfo['forumtitle']);
$TopicInfo['sujet'] = getformatrecup($TopicInfo['sujet']);

$tpl->treenavs = $tpl->gettemplate("treenav","treedetail");
$cache .= $tpl->gettemplate("treenav","hierarchy");

// ##### Fin navigation #####
$tpl->box['nextpost']=$tpl->gettemplate("detail","topicnavig");

if (!isset($_GET['vu'])) {
	$query = $sql->query("UPDATE ".$_PRE."topics SET nbvues=nbvues+1 WHERE idtopic=".$_GET['id']);
}

// ###### Gestion des pages #####
$p = isset($_GET['p']) && (int)$_GET['p'] > 0 ? (int)$_GET['p'] : 1;

$tpl->box['navpages'] = getnumberpages($nbtotalmsg,"detail",$_FORUMCFG['msgparpage'],$p);
if ($nbpages>1) {
	$tpl->box['numberpages']=$tpl->gettemplate("detail","boxpages");
}

$debut = ($p * $_FORUMCFG['msgparpage']) - $_FORUMCFG['msgparpage'];

// ##### Gestion de la recherche #####
if (isset($_GET['s'])) {
	$SearchOrig 	= 	array();
	$SearchReplace 	= 	array();
	
	$s 		= 	getformatmsg($_GET['s']);
	$s 		= 	urldecode($s);
	
	$SearchOrig 	= 	explode('+',$s);
	foreach($SearchOrig as $SearchMask) {
		$SearchReplace[] = stripslashes($tpl->gettemplate('detail','SearchMask'));
    }
}

// ##### Gestion de la réponse rapide #####
if ($_FORUMCFG['repflash']=="Y" && $_PERMFORUM[$_GET['forumid']][3]==true && $TopicInfo['opentopic'] != "N") {
	if ($_USER['userid']==0) {
		$tpl->box['pseudobox'] 		= 	$tpl->gettemplate("detail","boxguest");
    } else {
		$posteurpseudo 				= 	getformatrecup($_USER['username']);
		$tpl->box['pseudobox'] 		= 	$tpl->gettemplate("detail","boxmembre");
	}
	
	$LimiteLength 					= 	$_PERMFORUM[$_GET['forumid']]['MaxChar'];

	if ($LimiteLength > 0) {
		$tpl->box['limitmsgdef']		=		$LimiteLength;
    } else {
		$tpl->box['limitmsgdef']		=		$tpl->attlang("unlimited");
    }

	$tpl->box['javascript'] 		= 	$tpl->gettemplate("entete","getjscompter");
	$tpl->box['repflash']   		= 	$tpl->gettemplate("detail","repflash");
}

$query 	= 	$sql->query("SELECT 
			".$_PRE."posts.idpost AS idpost,
			".$_PRE."posts.sujet AS sujetpost, 
			".$_PRE."posts.date AS datepost,
			".$_PRE."posts.msg AS msgpost, 
			".$_PRE."posts.icone AS iconpost, 
			".$_PRE."posts.idmembre AS posterid,
			".$_PRE."posts.pseudo AS pseudo,
			".$_PRE."posts.smiles AS smiles,
			".$_PRE."posts.parent AS parent,
			".$_PRE."posts.bbcode AS afbbcode, 
			".$_PRE."posts.poll as poll, 
			".$_PRE."user.* 
		FROM ".$_PRE."posts
		LEFT JOIN ".$_PRE."user ON ".$_PRE."posts.idmembre=".$_PRE."user.userid
		WHERE ".$_PRE."posts.parent='".$_GET['id']."'
		ORDER BY ".$_PRE."posts.date LIMIT ".$debut.",".$_FORUMCFG['msgparpage']);

InitBBcode();
if ($_FORUMCFG['use_grades'] == "Y") {
	$Grades	=	unserialize($_FORUMCFG['grades']);
}

$tpl->box['forumcontent'] = "";


// #### Gestion des sondages ####
if ($TopicInfo['poll']>0) {
	$pollreq	=	$sql->query("SELECT * FROM ".$_PRE."poll WHERE id=".$TopicInfo['poll']);
	$sd		=	mysql_fetch_array($pollreq);
	
	$tpl->box['questpoll'] = getformatrecup($sd['question']);
	
	if (preg_match("|-".$_USER['userid']."-|",$sd['votants']) == 0 && $_USER['userid'] != 0 && $TopicInfo['opentopic'] == "Y") {
		$tpl->box['buttonvote']	=	$tpl->gettemplate("detail","votebutton");
		$canvote		=	true;
	} else {
		$tpl->box['buttonvote']	=	"";
		$canvote		=	false;
	}
	
	$nbrep		=	explode(" >> ",$sd['rep']);
	$choix		=	explode(" >> ",$sd['choix']);
	$nbtotalrep	=	0;

	for ($i = 0; $i < count($choix); $i++) {
		$nbtotalrep += $nbrep[$i];
    }
		
	$swapbgcolor=true;
	
	$tpl->box['pollchoice'] = "";
	for ($i=0;$i<count($choix);$i++) {
		if ($swapbgcolor) {
			$pollbgcolor=$_SKIN['bgtable1'];
        } else {
			$pollbgcolor=$_SKIN['bgtable2'];
        }
	
		if ($nbtotalrep>0) {
			$percent = round(($nbrep[$i]*100)/$nbtotalrep);
        } else {
			$percent = 0;
        }
		$tpl->box['altpoll']=getformatrecup($choix[$i]);
		if ($canvote) {
			$tpl->box['radiopoll']=$tpl->gettemplate("detail","votechoice");
        }
		$tpl->box['pollchoice'].=$tpl->gettemplate("detail","lignesondage");

		$swapbgcolor=!$swapbgcolor;
	}
	$tpl->box['affpoll'] = $tpl->gettemplate("detail","boxsondage");
}


$topicpassed = false;
	
while ($DetailMsg = mysql_fetch_array($query)) {
	if (!$topicpassed && $debut == 0) {
		$IdTopic = $DetailMsg['idpost'];
		$topicpassed = true;
	}
	
	$PrintRedirect	=	"idpost=".$DetailMsg['idpost'];
	$tpl->box['affsujetpost']=NULLSTR;
	$tpl->box['forumcontent'].=affdetailtopic();
}

if (isset($_PERMFORUM[$_GET['forumid']][5]) && $_PERMFORUM[$_GET['forumid']][5]) {
	$tpl->box['buttonpoll'] = $tpl->gettemplate("detail","buttonaddpoll");
} else {
	$tpl->box['buttonpoll'] = NULLSTR;
}

$tpl->box['barrebouttons'] = $tpl->gettemplate("detail","boxbuttons");

$PrintRedirect = "idtopic=".$_GET['id'];

$cache .= $tpl->gettemplate("detail","boxdetail");

$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
