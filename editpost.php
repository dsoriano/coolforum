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

$posterid = isset($_REQUEST['posterid']) ? (int)$_REQUEST['posterid'] : 0;
$parent = isset($_REQUEST['parent']) ? (int)$_REQUEST['parent'] : 0;
$post = isset($_REQUEST['post']) ? (int)$_REQUEST['post'] : 0;
$forumid = isset($_REQUEST['forumid']) ? (int)$_REQUEST['forumid'] : 0;
$addpostit = isset($_REQUEST['addpostit']) ? (int)$_REQUEST['addpostit'] : 0;
$p = isset($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0;

$tpl->box['boxconnected']		=		NULLSTR;
$tpl->box['afferrormodo']		=		NULLSTR;
$tpl->box['javascript']			=		NULLSTR;
$tpl->box['treenav']			=		NULLSTR;

$error							=		NULLSTR;

// #### définition du lieu ###
$SessLieu	=	'TOP';
$SessForum	=	$forumid;
$SessTopic	=	$post;
//////////////////////////////

require("entete.php"); 

if ($post == 0 || $forumid == 0) {
	geterror("novalidlink");
}
	
function isicon($icone,$i)
{
	global $EditForum,$Icon_Select;
    $Icon_Select[$i] = $EditForum['iconpost'] == $icone ? " CHECKED" : "";
}

getlangage("editpost");

$canedit		=	getrightedit($post,$_REQUEST['forumid']);
$table_smileys	=	getloadsmileys();

if (!$canedit) {
	$tpl->box['msg']	 =	$tpl->attlang("cantedit");
	$tpl->box['editcontent'] =	$tpl->gettemplate("editpost","msgbox");
	$tpl->box['editcontent'] .=	getjsredirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php',3000);
} else {
	// ---------------------------------
	// Bannissement d'un membre
	// ---------------------------------
	if (isset($_REQUEST['action']) && $_REQUEST['action']=="banmember" && $_MODORIGHTS[3]) {
		$query 		   = $sql->query("SELECT userstatus FROM ".$_PRE."user WHERE userid='$posterid'");
		list($ToBanStatus) = mysql_fetch_array($query);
		 
		if ($_MODORIGHTS[3] && $ToBanStatus < $_USER['userstatus']) {
			$query	=	$sql->query("UPDATE ".$_PRE."user SET userstatus=-userstatus WHERE userid='$posterid'");
			$query	=	$sql->query("SELECT ".$_PRE."user.userid,".$_PRE."user.login,".$_PRE."user.usermail,".$_PRE."userplus.mailorig FROM ".$_PRE."user LEFT JOIN ".$_PRE."userplus ON ".$_PRE."userplus.idplus=".$_PRE."user.userid WHERE userid='$posterid'");
			$j	=	mysql_fetch_array($query);
			$query	=	$sql->query("INSERT INTO ".$_PRE."banlist (userid,login,mail1,mail2) VALUES ('".$j['userid']."','".$j['login']."','".$j['usermail']."','".$j['mailorig']."')");
		} else {
			$tpl->box['msgerrormodo']		=	$tpl->attlang('modocantban');
			$tpl->box['afferrormodo']		=	$tpl->gettemplate('editpost','afferrormodo');
		}
	}


	// ---------------------------------
	// Suppresion des messages
	// ---------------------------------	
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && $_MODORIGHTS[2]) {
		if ($_POST['IsTopic']=="Y") {
			$query			=	$sql->query("SELECT poll FROM ".$_PRE."topics WHERE idtopic='$parent'");
			list($id_poll)	=	mysql_fetch_array($query);
			
			if($id_poll > 0) {
				$query		=	$sql->query("DELETE FROM ".$_PRE."poll WHERE id=".$id_poll);
            }
				
			$query			=	$sql->query("DELETE FROM ".$_PRE."posts WHERE parent='$parent'");
			$query			=	$sql->query("DELETE FROM ".$_PRE."topics WHERE idtopic='$parent'");
			
			updatenbtopics();
		} else {
			$query	=	$sql->query("DELETE FROM ".$_PRE."posts WHERE idpost='$post'");
			updatetopiclastposter($parent);
		}
			
		updateforumlastposter($forumid);
		updatenbposts();
			
		if ($_POST['IsTopic']=="Y") {
            $tpl->box['msg']=$tpl->attlang("deltopicok");
        } else {
            $tpl->box['msg']=$tpl->attlang("delmsgok");
        }
			
		$tpl->box['editcontent'] =	$tpl->gettemplate("editpost","msgbox");
		$tpl->box['editcontent'].=	getjsredirect("list.php?forumid=".$forumid,3000);
	}		


	// ---------------------------------
	// Scinder les messages
	// ---------------------------------
	if( isset($_REQUEST['action']) && $_REQUEST['action'] == "split" && $_MODORIGHTS[8]) {
		$testchain=preg_replace("/([\s]{1,})/","",$_POST['sujet']);
		if (strlen($testchain)==0) {
			$error=$tpl->attlang("nosujetwhensplit");
        }
		
		if (strlen($error)==0) {
			$sql->query("INSERT INTO ".$_PRE."topics (idforum,sujet,date,icone,idmembre,pseudo) SELECT idforum,'".$_POST['sujet']."',date,icone,idmembre,pseudo FROM ".$_PRE."posts WHERE idpost='".$post."'");
			$id=mysql_insert_id();
			$sql->query("UPDATE ".$_PRE."posts SET parent='".$id."' WHERE parent='".$_REQUEST['parent']."' && idpost>='".$post."'");
			$sql->query("UPDATE ".$_PRE."posts SET sujet='".$_POST['sujet']."' WHERE idpost='".$post."'");
			
			updatetopiclastposter($id);
			updatetopiclastposter($_REQUEST['parent']);
			updateforumlastposter($_REQUEST['forumid']);
			
			$tpl->box['msg']=$tpl->attlang("splitok");
			$tpl->box['editcontent']=$tpl->gettemplate("editpost","msgbox");
			$tpl->box['editcontent'].=getjsredirect("list.php?forumid=".$_REQUEST['forumid'],3000);
		}
	}


	// ---------------------------------
	// Gestion des post-it
	// ---------------------------------	
	if (isset($_REQUEST['action']) && $_REQUEST['action']=="addpostit" && $_MODORIGHTS[7]) {
		$query = $sql->query("UPDATE ".$_PRE."topics SET postit='$addpostit' WHERE idtopic='$parent'");
    }


	// ---------------------------------
	// Close/Open topic
	// ---------------------------------	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == "closetopic" && $_MODORIGHTS[4]) {
		$opentopic = $_POST['opentopic']=="N" ? "N" : "Y";

		$query = $sql->query("UPDATE ".$_PRE."topics SET opentopic='$opentopic' WHERE idtopic='$parent'");
	}


	// ---------------------------------
	// Changer de forum
	// ---------------------------------	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=="changeforum" && $_MODORIGHTS[6] && $_POST['forumdest'] > 0) {
		$forumdest	=	intval($_POST['forumdest']);
		
		$query		=	$sql->query("UPDATE ".$_PRE."topics SET idforum='$forumdest' WHERE idtopic='$parent'");
		$query		=	$sql->query("UPDATE ".$_PRE."posts SET idforum='$forumdest' WHERE parent='$parent'");
			
		updateforumlastposter($forumid);
		updateforumlastposter($forumdest);
			
		$tpl->box['msg']		=	$tpl->attlang("topicmoved");
		$tpl->box['editcontent']	=	$tpl->gettemplate("editpost","msgbox");
		$tpl->box['editcontent'] .=	getjsredirect("list.php?forumid=".$forumdest,3000);
	}
	
	
	// ---------------------------------
	// Edition du message
	// ---------------------------------
	if (isset($_REQUEST['action']) && $_REQUEST['action']=="update") {
		$error="";
		
		// **** test du sujet ****
		if ($_POST['IsTopic']=="Y") {
			$testchain=preg_replace("/([\s]{1,})/","",$_POST['sujet']);
			if (strlen($testchain) == 0) {
				$error = $tpl->attlang("badsujet");
            }
		}

		// **** test et formattage du message ****
		$testchain = preg_replace("/([\s]{1,})/","",$_POST['msg']);
		if (strlen($testchain) == 0) {
			$error = $tpl->attlang("badmsg");
        }

		// **** si tout est ok on formatte et on enregistre tout ****
		if (strlen($error) == 0) {
			$sujet		=	getformatmsg($_POST['sujet'],false);	// formattage du sujet

			if ($_USER['wysiwyg'] == "Y") {
				$msg		=	convert_html_to_bbcode($_POST['msg']);
				$msg		=	getformatmsghtml($msg);
			} else {
				$msg		=	getformatmsg($_POST['msg']);	// formattage du message
            }

			
			$msg		=	test_max_length($msg,$_PERMFORUM['MaxChar']);
			$sujet		=	test_max_length($sujet,$_FORUMCFG['limittopiclength']);

			// **** traitement de l'icône ****
			$icon = preg_match("|^icon([0-9]{1,2})$|",$_POST['icon']) == 0 ? "icon1" : $_POST['icon'];

			$bbita1="";
			$bbita2="";
	
			if ($_POST['bbcode'] == "non") { // test si bbcode actif ou non
                $nobb		=	"N";
            } else {
                $nobb		=	"Y";
                $bbita1		=	"[ita]";
                $bbita2		=	"[/ita]";
            }
				
			if ($_POST['smilecode'] == "non") { // active ou non smileys
                $smiles		=	"N";
            } else {
                $smiles		=	"Y";
            }
		
					
			if ($_FORUMCFG['viewmsgedit'] == "Y") {
				$DateEdit 	= 	gmstrftime("%d/%m/%Y %H:%M",time()+(3600*($_FORUMCFG['defaulttimezone']+intval(date("I")))));
				$LoginEdit 	= 	addslashes($_USER['username']);
				$msg	       .=	$tpl->gettemplate("editpost","editline");
			}
			
			if ($_POST['IsTopic'] == "Y") {
				$query = $sql->query("UPDATE ".$_PRE."topics SET sujet='$sujet',icone='$icon' WHERE idtopic='$parent'");
            }
			
			$query = $sql->query("UPDATE ".$_PRE."posts SET sujet='$sujet',
						msg='$msg', 
						icone='$icon', 
						smiles='$smiles', 
						bbcode='$nobb' 
					WHERE idpost='$post'");

            $tpl->box['editcontent'] = $query ? $tpl->gettemplate("editpost","editok") : $tpl->gettemplate("editpost","editnok");

			$tpl->box['editcontent'] .= getjsredirect("detail.php?forumid=".$forumid."&id=".$parent."&p=".$p."#".$post,3000);
		}
	}
	
	
	if (!isset($tpl->box['editcontent'])) {
		$_REQUEST['action'] = NULLSTR;
    }
	

	if (empty($_REQUEST['action'])) {
		$tpl->box['affoptions']			=	NULLSTR;
		$tpl->box['smilechecked']		=	NULLSTR;
		$tpl->box['bbcodechecked']		=	NULLSTR;
		$tpl->box['mailnotify']			=	NULLSTR;
		$tpl->box['sondage']			=	NULLSTR;
		
		$p = isset($_REQUEST['p']) && (int)$_REQUEST['p'] > 0 ? (int)$_REQUEST['p'] : 1;

		getlangage("writebox");
		$tpl->box['editcontent']="";
		$query=$sql->query("SELECT ".$_PRE."posts.idpost AS idpost,
					".$_PRE."posts.sujet AS sujet, 
					".$_PRE."posts.date AS datepost,
					".$_PRE."posts.parent AS parent,
					".$_PRE."posts.msg AS msgpost, 
					".$_PRE."posts.icone AS iconpost, 
					".$_PRE."posts.idmembre AS posterid, 
 					".$_PRE."posts.pseudo AS pseudo,
 					".$_PRE."posts.smiles, 
					".$_PRE."posts.bbcode AS afbbcode, 
					".$_PRE."user.*
				FROM ".$_PRE."posts
				LEFT JOIN ".$_PRE."user ON ".$_PRE."posts.idmembre=".$_PRE."user.userid
				WHERE idpost='".$post."'");

		$EditForum = mysql_fetch_array($query);
		
		if (isset($error) && strlen($error)>0) {
			$EditForum['sujet']	=	getrecupforform($_POST['sujet']);
			$EditForum['msg']	=	getrecupforform($_POST['msg']);
			$EditForum['icon']	=	$_POST['icon'];
			$EditForum['smiles']	=	$_POST['smilecode'];
			$EditForum['afbbcode']	=	$_POST['bbcode'];
			
			$tpl->box['error']	=	$tpl->gettemplate("editpost","errorbox");
		} else {
			$tpl->box['error']	=	NULLSTR;
        }
			
		// **** on vérifie si le message est un sujet ****
		$query = $sql->query("SELECT idpost,sujet FROM ".$_PRE."posts WHERE parent='".$EditForum['parent']."' ORDER BY date LIMIT 0,1");
		list($TopicPost,$TopicSujet) = mysql_fetch_array($query);

		// Barre de Navigation
		$TopicSujet = getformatrecup($TopicSujet);
		$ForumInfo = getforumname($_REQUEST['forumid']);
		$ForumInfo['cattitle'] = getformatrecup($ForumInfo['cattitle']);
		$ForumInfo['forumtitle'] = getformatrecup($ForumInfo['forumtitle']);
		$tpl->treenavs = $tpl->gettemplate("treenav","treeeditpost");
		$tpl->box['treenav'] = $tpl->gettemplate("treenav","hierarchy");

        $IsTopic = $TopicPost == $EditForum['idpost'] ? 'Y' : 'N';

		// **** on récupère les infos sur le sujet ****
		$query = $sql->query("SELECT opentopic,postit FROM ".$_PRE."topics WHERE idtopic='".$EditForum['parent']."'");
		list($OpenTopic,$PostIt) = mysql_fetch_array($query);
		
		$LimiteLength 						= 		$_PERMFORUM[$forumid]['MaxChar'];

        $tpl->box['limitmsgdef'] = $LimiteLength > 0 ? $LimiteLength : $tpl->attlang("unlimited");

		
		// **** Peut-on ouvrir/fermer le sujet? ****
		if ($IsTopic=="Y" && $_MODORIGHTS[4]) {
			if ($OpenTopic=="N") {
				$tpl->box['titleopclo']=$tpl->attlang("toopclotopic");
				$tpl->box['opclotovalid']="Y";
			} else {
				$tpl->box['titleopclo']=$tpl->attlang("tocpclotopic");
				$tpl->box['opclotovalid']="N";
			}
			$tpl->box['affoptions'] .= $tpl->gettemplate("editpost","opclotopic");
		}

		// **** Peut-on épingler le sujet? ****
		if ($IsTopic=="Y" && $_MODORIGHTS[7]) {
			if ($PostIt=="0") {
				$tpl->box['postit']=$tpl->attlang("postitn");
				$tpl->box['addpostit']="1";
			} else {
				$tpl->box['postit']=$tpl->attlang("postito");
				$tpl->box['addpostit']="0";
			}
			$tpl->box['affoptions'].=$tpl->gettemplate("editpost","postittopic");
		}
				
		// **** Peut-on supprimer le message/sujet? ****
		if ($_MODORIGHTS[2]) {
			if($IsTopic=="Y") {
				$tpl->box['titledeltopic']=$tpl->attlang("deltopic");
				$tpl->box['cmdeltopic']=$tpl->attlang("cmdeltopic");
			} else {
				$tpl->box['titledeltopic']=$tpl->attlang("delpost");
				$tpl->box['cmdeltopic']=$tpl->attlang("cmdelpost");
			}
			$tpl->box['affoptions'] .= $tpl->gettemplate("editpost","deltopic");
		}
		
		// **** Peut-on scinder le sujet ? ****
		if ($IsTopic != "Y" && $_MODORIGHTS[8]) {
			$tpl->box['affoptions'].=$tpl->gettemplate("editpost","splittopic");
        }
		
		// **** Peut-on bannir le membre? ****		
		if ($_MODORIGHTS[3] && $EditForum['posterid']>0) {
			$searchban = $sql->query("SELECT userid FROM ".$_PRE."banlist WHERE userid='".$EditForum['posterid']."'");
			$isbanned = mysql_num_rows($searchban);

            $tpl->box['affoptions'] .= $isbanned == 1 ? $tpl->gettemplate("editpost","banned") : $tpl->gettemplate("editpost","banboxok");
		}

		// **** Peut-on déplacer le sujet ****
		if ($IsTopic=="Y" && $_MODORIGHTS[6]) {
			getjumpforum("editpost");
        } else {
			$tpl->box['forumjump'] = NULLSTR;
        }
		
		if ($_GENERAL[19] || $_GENERAL[20]) {
			$tpl->box['editcontent'].=$tpl->gettemplate("editpost","optionsstruct");
        }
		
		// **** Edition du message ****
		$EditForum['pseudo'] = getformatrecup($EditForum['pseudo']);
		$EditForum['sujet'] = getformatrecup($EditForum['sujet']);

		for ($i = 1; $i < 17; $i++) {
			isicon("icon".$i,$i);
        }
			
		if ($_USER['wysiwyg'] == "Y") {
			$tpl->box['quotemsg']=htmlentities(getformatrecup($EditForum['msgpost'], ENT_COMPAT,'ISO-8859-1', true));
        } else {
			$tpl->box['quotemsg']=getformatrecup($EditForum['msgpost']);
        }
			
		if ($EditForum['smiles']=="N") {
			$tpl->box['smilechecked']=" checked";
        }

		if ($EditForum['afbbcode']=="N") {
			$tpl->box['bbcodechecked']=" checked";
        }

        $tpl->box['javascript'] = $_USER['wysiwyg'] == "Y" ? $tpl->gettemplate("writebox_wysiwyg","wysiwygjs") : $tpl->gettemplate("entete","getjscompter");

		$tpl->box['writebox'] = affwritebox();
			
		$tpl->box['editcontent'] .= $tpl->gettemplate("editpost","editmsgbox");
	}
}

$cache .= $tpl->gettemplate("editpost","pagestruct");

$tps = number_format(get_microtime() - $tps_start,4);

$cache .= $tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);

 