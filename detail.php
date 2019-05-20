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

$tpl->box['numberpages'] = NULLSTR;
$tpl->box['affpoll'] = NULLSTR;
$_GET['id'] = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$_GET['forumid'] = isset($_GET['forumid']) ? (int)$_GET['forumid'] : 0;
$PrintRedirect = NULLSTR;

if ($_GET['id'] == 0 || $_GET['forumid'] == 0) {
	geterror("novalidlink");
}

// #### définition du lieu ###
$_SESSION['SessLieu']	=	_LOCATION_TOPIC_;
$_SESSION['SessForum']	=	$_GET['forumid'];
$_SESSION['SessTopic']	=	$_GET['id'];
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
$InfoMember	=	get_connected();

if ($_FORUMCFG['conn_topic'] == "Y") {
    $tpl->box['statsconnectes']		=	NULLSTR;
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
	$query = $sql->query("UPDATE "._PRE_."topics SET nbvues=nbvues+1 WHERE idtopic = %d", $_GET['id'])->execute();
}

// ###### Gestion des pages #####
$p = isset($_GET['p']) && (int)$_GET['p'] > 0 ? (int)$_GET['p'] : 1;

$tpl->box['navpages'] = getnumberpages($nbtotalmsg,"detail",$_FORUMCFG['msgparpage'],$p);
if ($nbpages>1) {
	$tpl->box['numberpages']=$tpl->gettemplate("detail","boxpages");
}

$debut = ($p * $_FORUMCFG['msgparpage']) - $_FORUMCFG['msgparpage'];

// ##### Gestion de la recherche #####
$SearchOrig 	= 	array();
$SearchReplace 	= 	array();

if (isset($_GET['s'])) {
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
} else {
    $tpl->box['repflash'] = NULLSTR;
}

$query 	= 	$sql->query("SELECT 
			"._PRE_."posts.idpost AS idpost,
			"._PRE_."posts.sujet AS sujetpost, 
			"._PRE_."posts.date AS datepost,
			"._PRE_."posts.msg AS msgpost, 
			"._PRE_."posts.icone AS iconpost, 
			"._PRE_."posts.idmembre AS posterid,
			"._PRE_."posts.pseudo AS pseudo,
			"._PRE_."posts.smiles AS smiles,
			"._PRE_."posts.parent AS parent,
			"._PRE_."posts.bbcode AS afbbcode, 
			"._PRE_."posts.poll as poll, 
			"._PRE_."user.* 
		FROM "._PRE_."posts
		LEFT JOIN "._PRE_."user ON "._PRE_."posts.idmembre="._PRE_."user.userid
		WHERE "._PRE_."posts.parent=%d
		ORDER BY "._PRE_."posts.date LIMIT %d,%d", array($_GET['id'], $debut, $_FORUMCFG['msgparpage']))->execute();

InitBBcode();
if ($_FORUMCFG['use_grades'] == "Y") {
	$Grades	=	unserialize($_FORUMCFG['grades']);
}

$tpl->box['forumcontent'] = "";


// #### Gestion des sondages ####
if ($TopicInfo['poll']>0) {
	$pollreq	=	$sql->query("SELECT * FROM "._PRE_."poll WHERE id=%d", $TopicInfo['poll'])->execute();
	$sd		=	$pollreq->fetch_array();

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

while ($DetailMsg = $query->fetch_array()) {
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

session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
