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

// #### Initialisation des variables #### //////////////////////////////////////
$cache						=	NULLSTR;
$ListColorGroup 			= 	array();
$_USER 						= 	array(); 	// renseignement du membre
$_PERMCAT 					= 	array(); 	// permissions sur catégories
$_PERMFORUM 				= 	array(); 	// permissions sur forums
$_SKIN 						= 	array(); 	// infos sur skin
$_GENERAL 					= 	array(		false,	false,	false,	false,
											false,	false,	false,	false,
											false,	false,	false,	false,
											false,	false,	false,	false,
											false,	false,	false,	false, false);
////////////////////////////////////////////////////////////////////////////////






// #### Initialisation membre + langage #### ///////////////////////////////////
$_USER						=	getuserid();
getlangage("general");
////////////////////////////////////////////////////////////////////////////////






// #### Chargement du skin #### ///////////////////////////////////////////////
getskin();
$_SKIN['repimg']				=	"skins/".$_SKIN['repimg'];

if ($_SKIN['affdegrad'] == "Y") {
	$tpl->box['affdegrad'] 		= 	$tpl->gettemplate("entete","affdegrad");
} else {
	$tpl->box['affdegrad'] 		= 	NULLSTR;
}

	//Initialisation des couleurs des groupes
$tpl->box['grpcolor'] 			= 	NULLSTR;

foreach ($ListColorGroup AS $gpcolor) {
	$groupcolor 				= 	$_SKIN['grp'.$gpcolor];
	$tpl->box['grpcolor'] 	   .=	$tpl->gettemplate("entete","groupscolor");
}
////////////////////////////////////////////////////////////////////////////////






// #### Update session #### ////////////////////////////////////////////////////
if (preg_match("|popup.php|",$_SERVER['REQUEST_URI']) == 0) {
	$NombreConnectes			=	$session->checkConnected();
}






// #### Définition navigateur #### /////////////////////////////////////////////
if (preg_match("|MSIE|", $_SERVER['HTTP_USER_AGENT']) > 0) {
	define("NAVIGATEUR","MSIE");
	$tpl->box['cssform']	=	$tpl->gettemplate("entete","formie");
} elseif (preg_match("|Mozilla/5.0|", $_SERVER['HTTP_USER_AGENT']) > 0) {
	define("NAVIGATEUR","MOZILLA");
	$tpl->box['cssform']	=	$tpl->gettemplate("entete","formie");
} else {
	$tpl->box['cssform']	=	$tpl->gettemplate("entete","formns");
}
////////////////////////////////////////////////////////////////////////////////






/*
if(isset($GetWYSIWYG) && $GetWYSIWYG)
{
	$tpl->box['javascript']	=	$tpl->gettemplate("writebox_wysiwyg","wysiwygjs");
	//$tpl->box[onload]	=	$tpl->gettemplate("writebox","wysiwygonload");
}	*/






// #### Chargement en-tête du forum #### ///////////////////////////////////////
if (isset($TopicInfo)) {
	$TopicName 				= 	": ".$TopicInfo['sujet'];
}
$cache.=$tpl->gettemplate("entete","htmlheader");
////////////////////////////////////////////////////////////////////////////////






if (!isset($nocache)) {
	// #### Utilisation gestionnaire de pub #### ///////////////////////////////
	if ($_FORUMCFG['usepub']=="Y") {
		$date								=		time();
		$result 							= 		array();

		$query								=		$sql->query("SELECT * FROM "._PRE_."campagnes WHERE dtedebut < %d", $date)->execute();

		if ($query->num_rows() > 0) {
			while ($i = $query->fetch_array()) {
				if (($i['typefin'] == "aff" && $i['nbaffichages'] < $i['fincamp']) || ($i['typefin'] == "click" && $i['clicks'] < $i['fincamp']) || ($i['typefin'] == "date" && $date < $i['fincamp'])) {
					for ($x = 0; $x < $i['ratio']; $x++) {
						$result[]			=	$i;
                    }
                }
            }

			if (count($result) > 0) {
				$nocamp 					= 	rand(0,(count($result)-1));

				$date_derniere_vue_camp		=	strftime("%Y-%m-%d",$result[$nocamp]['lastvue']);
			  	$date_maintenant			=	strftime("%Y-%m-%d",$date);
			  	$inserchampcamp				=	$result[$nocamp]['id']."-".strftime("%Y%m%d",$result[$nocamp]['lastvue']);

			  	if ($date_derniere_vue_camp == $date_maintenant) {
			  		$query					=	$sql->query("UPDATE "._PRE_."campagnes SET nbaffichages = nbaffichages+1, lastvue = %d, todayvue = todayvue+1 WHERE id = %d", array($date, $result[$nocamp]['id']))->execute();
                } else {
			  		$query					=	$sql->query("INSERT INTO "._PRE_."statcamp (iddate,vu,clicks) VALUES (%d,%d,%d)", array($inserchampcamp, $result[$nocamp]['todayvue'], $result[$nocamp]['todayclick']))->execute();
			  		$query					=	$sql->query("UPDATE "._PRE_."campagnes SET nbaffichages=nbaffichages+1, lastvue = %d, todayvue = 1, todayclick = 0 WHERE id = %d", array($date, $result[$nocamp]['id']))->execute();
			  	}

			 	if (strlen($result[$nocamp]['regie'])==0)  {
			 		$idads 					= 	$result[$nocamp]['id'];
			 		$adsban 				= 	$result[$nocamp]['banniere'];
			 		$tpl->box['ajouthtml'] 	= 	$tpl->gettemplate("entete","ads");
				} else {
					$tpl->box['ajouthtml'] 	= 	stripslashes($result[$nocamp]['regie']);
                }
			} else {
				$tpl->box['ajouthtml'] 		= 	"&nbsp;";
            }
		} else {
			$tpl->box['ajouthtml'] 			= 	"&nbsp;";
        }
	}
	////////////////////////////////////////////////////////////////////////////






	// #### Affichage de HTML haut #### ///////////////////////////////////////
	else {
        $tpl->box['ajouthtml'] = !empty($_FORUMCFG['ajouthtml']) ? stripslashes($_FORUMCFG['ajouthtml']) : "&nbsp;";
	}
	////////////////////////////////////////////////////////////////////////////






	$cache								   .=	$tpl->gettemplate("entete","logo");

    $tpl->box['administrer'] = $_GENERAL[20] ? $tpl->gettemplate("entete","administrer") : NULLSTR;
    $tpl->box['affstatlink'] = $_GENERAL[0] ? trim($tpl->gettemplate("entete","affstatlink")) : NULLSTR;

	$cache								   .=	$tpl->gettemplate("entete","navbar");
}






// #### Série de tests sur le forum #### ///////////////////////////////////////
	// **** Mise en forme du nom du site ****
$tpl->box['forumsite'] = strlen($_FORUMCFG['sitename']) > 0 && strlen($_FORUMCFG['siteurl']) > 0 ? $tpl->gettemplate("baspage","siteurl") : NULLSTR;

	// **** La version de DB est-elle bonne? ****
if ($_FORUMCFG['ForumDBVersion'] != $ForumDBVersion) {
	geterror("notgoodDB");
}
	// **** test de la dernière initialisation cookie du forum ****
if (isset($_COOKIE['CF_LastINI']) && $_COOKIE['CF_LastINI'] < $_FORUMCFG['initialise']) {
	geterror("initcookie");
}

	// **** Le board est-il ouvert? ****
if ($_FORUMCFG['openforum'] == "N" && !$_GENERAL[20]) {
	geterror("closedforum");
}

	// **** Le visiteur est-il bannit? ****
if ($_USER['userstatus'] < 0) {
	geterror("mbbanned");
}

	// **** Le forum oblige-t-il à être identifié? ****
if ($_USER['userid'] == 0 && $_FORUMCFG['mustbeidentify'] == "Y" && preg_match("|identify.php|",$_SERVER['REQUEST_URI']) == 0 && preg_match("|register.php|",$_SERVER['REQUEST_URI']) == 0) {
	geterror("call_loginbox");
}

	// **** Popup sur les nouveux PM ****
if (!isset($_COOKIE['nbpmvu'])) {
	$_COOKIE['nbpmvu'] = 0;
}

if ($_USER['popuppm']=="Y" && $_USER['nbpmvu']>$_COOKIE['nbpmvu']) {
	$cache .= $tpl->gettemplate("entete","jspopuppm");
	sendcookie("nbpmvu",$_USER['nbpmvu'],-1);
}

if (!isset($_REQUEST['action'])) {
    $_REQUEST['action'] = NULLSTR;
}
////////////////////////////////////////////////////////////////////////////////
