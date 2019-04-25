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

if(!isset($_REQUEST['action']))		$_REQUEST['action'] = NULLSTR;

if($_REQUEST['action']=="preview")
	$nocache=true;

getlangage("detail");
getlangage("repondre");
getlangage("writebox");

///////////////////
// Fonctions

function isicon($icone,$i)
{
global $_POST,$Icon_Select;
	$chaine="";
	if($_POST['icon']==$icone)
		$Icon_Select[$i]=" CHECKED";
	else
		$Icon_Select[$i]="";

}

$Parent		=	intval($_REQUEST['parent']);
$ForumID	=	intval($_REQUEST['forumid']);

if(empty($_REQUEST['action']))
	$_REQUEST['action']="form";
unset($error);

// #### définition du lieu ###
$SessLieu	=	'TOP';
$SessForum	=	$ForumID;
$SessTopic	=	$Parent;
//////////////////////////////

require("entete.php");

$error	=	NULLSTR;

///////////////////
// Test time limit

$date=time();
if(isset($_COOKIE['LimitTimePost'])&& (($_COOKIE['LimitTimePost']+$_FORUMCFG['limittimepost'])>$date))
	geterror("toofast");

///////////////////
// Test des droits

if(($Parent==0 && !$_PERMFORUM[$ForumID][4]) || ($Parent>0 && !$_PERMFORUM[$ForumID][3]))
	geterror("call_loginbox");



if($_REQUEST['action']=="preview")
{

	$table_smileys=getloadsmileys();

	if($_USER['wysiwyg']=="Y")
	{
		$msg		=	convert_html_to_bbcode($_POST['msg']);
		$msg		=	getformatmsghtml($msg);
		$msg		=	addslashes($msg);
	}
	else
		$msg 		= getformatpreview($_POST['msg']);

	if(!isset($_POST['smilecode']) || (isset($_POST['smilecode']) && $_POST['smilecode']!="non"))
		$msg		=		getreturnsmilies($msg);

	if(!isset($_POST['smilecode']) || (isset($_POST['bbcode']) && $_POST['bbcode']!="non"))
	{
		InitBBcode();
		$msg		=		getreturnbbcode($msg);
	}

	$tpl->box['affmessage']	=		$msg;

	$cache.=$tpl->gettemplate("writebox","msgpreview");

	$cache.=$tpl->gettemplate("baspage","endhtml");
	$tpl->output($cache);
}

if($_REQUEST['action']=="savemsg")
{
	$error="";

	if (!is_int($Parent) || !is_int($ForumID))
		geterror("novalidlink");

	// **** Le sujet est-il open? ****
	if($Parent>0)
	{
		$query	=	$sql->query("SELECT idtopic,opentopic,idforum FROM "._PRE_."topics WHERE idtopic=%d", $Parent)->execute();
		$nb	=	$query->num_rows();

		if ($nb	== 0)	geterror("novalidlink");
		else
		{
			$j	=	$query->fetch_array();
			if($j['opentopic']=="N")		geterror("closedtopic");
		}
	}

	// **** Peut-on poster dans ce forum ****
	$ForumInfo = getforumname($ForumID);

	// **** test du sujet ****
	if($Parent==0)
	{
		$testchain=preg_replace("/([\s]{1,})/","",$_POST['sujet']);
		if(strlen($testchain)==0)
			$error=$tpl->attlang("badsujet");
	}


	// **** test du pseudo si visiteur ****
	$idmembre	=	$_USER['userid'];

	if($idmembre == 0)
	{
		$testchain=preg_replace("/([\s]{1,})/","",$_POST['pseudo']);
		if(strlen($testchain)==0)
			$error=$tpl->attlang("badpseudo1");

		$query		=		$sql->query("SELECT login FROM "._PRE_."user WHERE login='%s'", getformatmsg($_POST['pseudo'],false))->execute();
		$nb				=		$query->num_rows();

		if($nb > 0)
			$error=$tpl->attlang("badpseudo2");
	}

	// **** test du message ****
	if($_USER['wysiwyg']=="Y" && (!isset($_REQUEST['repflash']) || (isset($_REQUEST['repflash']) && $_REQUEST['repflash'] != "Y")))
	{
		$testchain		=	preg_replace("/<img .*?>/si","[img]",$_POST['msg']);	// Les images ne doivent pas être supprimées par la ligne en dessous
		$testchain		=	strip_tags($testchain);									// Supprime les balises HTML
		$testchain		=	preg_replace("/(\r\n|\n)/si","",$testchain);			// Supprime les retour à la ligne

		$trans			=	get_html_translation_table(HTML_ENTITIES);				// |
		$trans 			= 	array_flip($trans);										// > Remplace les entitées HTML par leur caractère équivalent
		$testchain		=	strtr($testchain,$trans);								// |
	}
	else
		$testchain=preg_replace("/([\s]{1,})/","",$_POST['msg']);

	if(strlen($testchain)==0)
	{
		$error=$tpl->attlang("badmsg");
		$_POST['msg'] = $testchain;
	}

	// **** test du sondage ****
	if(isset($_POST['newpoll']) && $_POST['newpoll']=="true" && $Parent==0 && isset($_PERMFORUM[$ForumInfo['forumid']][5]) && $_PERMFORUM[$ForumInfo['forumid']][5])
	{
		$choice		=	array();
		$nbrep		=	array();

		$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['pollquest']);

		if(strlen($testchain)==0)	$error = $tpl->attlang("badquestpoll");

		for($i=1;$i<$_FORUMCFG['limitpoll']+1;$i++)
		{
			$testchain=preg_replace("/([\s]{1,})/","",$_POST['choixvote'][$i]);

			if(strlen($testchain)>0)
			{
				$choice[]=getformatmsg($_POST['choixvote'][$i]);
				$nbrep[]=0;
			}
		}
		if(count($choice)<2)
			$error=$tpl->attlang("badreppoll");
	}
	elseif((isset($_POST['newpoll']) && $_POST['newpoll']=="true" && $Parent>0) || (isset($_POST['newpoll']) && isset($_PERMFORUM[$ForumInfo['forumid']][5]) && $_POST['newpoll']=="true" && !$_PERMFORUM[$ForumInfo['forumid']][5]))
		geterror("novalidlink");

	// **** traitement de l'icône ****
	if(preg_match("|^icon([0-9]{1,2})$|",$_POST['icon']) == 0)
  		$_POST['icon']="icon1";

	// **** si tout est ok on formatte et on enregistre tout ****
	if(strlen($error)==0)
	{
		$sujet		=	getformatmsg($_POST['sujet'],false);	// formattage du sujet


		$pseudo = $idmembre==0 ? getformatmsg($_POST['pseudo'],false) : getformatdbtodb($_USER['username']);

		if($_USER['wysiwyg']=="Y" && (!isset($_REQUEST['repflash']) || (isset($_REQUEST['repflash']) && $_REQUEST['repflash'] != "Y")))
		{
			$msg		=	convert_html_to_bbcode($_POST['msg']);
			$msg		=	getformatmsghtml($msg);
		}
		else
			$msg		=	getformatmsg($_POST['msg']);	// formattage du message

		$msg		=	test_max_length($msg,$_PERMFORUM[$ForumInfo['forumid']]['MaxChar']);
		$sujet		=	test_max_length($sujet,$_FORUMCFG['limittopiclength']);
		$pseudo		=	test_max_length($pseudo,$_FORUMCFG['limitloginlength']);


		if(isset($_POST['newpoll']) && $_POST['newpoll']=="true")					// formattage et enregistrement du sondage
		{
			$chainechoix 	= 	implode(" >> ",$choice);
			$chainerep 	= 	implode(" >> ",$nbrep);
			$pollquest	=	getformatmsg($_POST['pollquest'],false);

			$query		=	$sql->query("INSERT INTO "._PRE_."poll (date,question,choix,rep,votants) VALUES ('$date','$pollquest','$chainechoix','$chainerep','-')");
			$idpoll		=	$sql->insertId();
		}
		else	$idpoll		=	0;

		if(isset($_POST['bbcode']) && $_POST['bbcode'] == "non")	$nobb		=	"N";		// test si bbcode actif ou non
			else			$nobb		=	"Y";

		if(isset($_POST['smilecode']) && $_POST['smilecode'] == "non")	$smiles		=	"N";		// active ou non smileys
			else			$smiles		=	"Y";

		if(isset($_POST['notifyme']) && $_POST['notifyme'] == "oui")	$notifyme	=	"Y";		// active ou non la notification
			else			$notifyme	=	"N";

		if ($Parent==0)						// insertion d'un sujet
		{
			$query 		= 	$sql->query("INSERT INTO "._PRE_."topics (idforum,sujet,date,nbrep,nbvues,datederrep,derposter,icone,idmembre,pseudo,opentopic,poll) VALUES (%d,'%s','%s',0,0,'%s','%s','%s', %d,'%s','Y',%d)", array($ForumID, $sujet, $date, $date, $pseudo, $_POST['icon'], $idmembre, $pseudo, $idpoll))->execute();
			$topicid	=	$sql->insertId();
			$query		=	$sql->query("INSERT INTO "._PRE_."posts (idforum,sujet,date,parent,msg,icone,idmembre,pseudo,postip,smiles,bbcode,notifyme) VALUES (%d,'%s','%s',%d,'%s','%s',%d,'%s','%s','%s','%s','%s')", array($ForumID, $sujet, $date, $topicid, $msg, $_POST['icon'], $idmembre, $pseudo, $_SERVER['REMOTE_ADDR'], $smiles, $nobb, $notifyme))->execute();
			$idderpost	=	$sql->insertId();
			$query		=	$sql->query("UPDATE "._PRE_."forums SET lastforumposter='%s',lastdatepost='%s',lastidpost=%d,forumtopic=forumtopic+1 WHERE forumid=%d", array($pseudo, $date, $idderpost, $ForumID))->execute();
			$query		=	$sql->query("UPDATE "._PRE_."topics SET idderpost=%d WHERE idtopic=%d", array($idderpost, $topicid))->execute();

			updatenbtopics();
		}
		else
		{
			$query 		= 	$sql->query("INSERT INTO "._PRE_."posts (idforum,sujet,date,parent,msg,icone,idmembre,pseudo,postip,smiles,bbcode,notifyme) VALUES (%d,'%s','%s',%d,'%s','%s',%d,'%s','%s','%s','%s','%s')", array($ForumID, $sujet, $date, $Parent, $msg, $_POST['icon'], $idmembre, $pseudo, $_SERVER['REMOTE_ADDR'], $smiles, $nobb, $notifyme))->execute();
			$idderpost	=	$sql->insertId();
			$query 		= 	$sql->query("UPDATE "._PRE_."topics SET datederrep='%s', nbrep=nbrep+1, derposter='%s', idderpost=%d WHERE idtopic=%d", array($date, $pseudo, $idderpost, $Parent))->execute();
			updatenbposts();

			//if(!$annonce)
				$query	=	$sql->query("UPDATE "._PRE_."forums SET lastforumposter='%s',lastdatepost='%s',lastidpost=%d,forumposts=forumposts+1 WHERE forumid=%d", array($pseudo, $date, $idderpost, $ForumID))->execute();

			//////////////////////////
			// ENVOI DES NOTIFICATIONS
			if($_FORUMCFG['mailnotify'] == "Y")
			{
				$quest		=	$sql->query("SELECT "._PRE_."posts.idmembre AS idmembre, "._PRE_."user.usermail AS mail FROM "._PRE_."posts LEFT JOIN "._PRE_."user ON "._PRE_."user.userid="._PRE_."posts.idmembre WHERE "._PRE_."posts.parent='$Parent' AND "._PRE_."posts.notifyme='Y' AND "._PRE_."posts.idmembre <>%d GROUP BY "._PRE_."posts.idmembre", $idmembre)->execute();
				$nbnotify	=	$quest->num_rows();

				if($nbnotify > 0)
				{

					$url		=	$_FORUMCFG['urlforum']."gotopost.php?id=$idderpost";

					$sqlsujet	=	$sql->query("SELECT sujet FROM "._PRE_."topics WHERE idtopic=%d", $Parent)->execute();
					list($mailsujet)	=	$sqlsujet->fetch_array();
					$mailsujet = formatstrformail(recupDBforMail($mailsujet));

					eval("\$subject = ".$tpl->attlang("mailsujet").";");
					eval("\$mesg = ".$tpl->attlang("mailmsg").";");

					while($jmail=$quest->fetch_array())
						@sendmail($jmail['mail'],$subject,$mesg);
				}
			}

		}

		if($idmembre>0)
			$result = $sql->query("UPDATE "._PRE_."user SET usermsg=usermsg+1, lastpost='%s' WHERE userid=%d", array($date, $idmembre))->execute();

		//------------- envoie des cookies et redirection ---------------------------

		if(isset($_COOKIE['listeforum_coolforum']))
		      $zecook=cookdecode($_COOKIE['listeforum_coolforum']);


	        $zecook[$ForumID."m"] = $zecook[$ForumID."m"]+1;
	        sendcookie("listeforum_coolforum",cookencode($zecook),-1);

		if($Parent==0)
			$envoiecookie=$idderpost;
		else
			$envoiecookie=$Parent;

		$cookiedetails="CoolForumDetails";
		if(isset($_COOKIE[$cookiedetails]))
			$cookiespost=cookdecode($_COOKIE[$cookiedetails]);


		if(!isset($cookiespost[$envoiecookie]))		$cookiespost[$envoiecookie]		=	0;
		$cookiespost[$envoiecookie] = $cookiespost[$envoiecookie]+1;
		if(count($cookiespost)>250)
			$limit=count($cookiespost)-250;
		else
		      	$limit=0;
		reset($cookiespost);
		$aa = 0;
		foreach ($cookiespost as $key => $value) {
		    if ($aa >= $limit) {
                $cookposttransfert[$key]=$value;
            }
		    $aa++;
        }

		sendcookie($cookiedetails,cookencode($cookposttransfert),-1);

		SetCookie("LimitTimePost",time(),time()+$_FORUMCFG['limittimepost']);

		if(!isset($_POST['redirect']))	$_POST['redirect'] = NULLSTR;
		switch($_POST['redirect'])
		{
			case "acc":
				header("location: index.php");
				break;
			case "cat":
				header("location: viewcat.php?catid=".$ForumInfo['forumcat']);
				break;
			case "for":
				header("location: list.php?forumid=".$ForumInfo['forumid']);
				break;
			case "msg":
				header("location: gotopost.php?id=".$idderpost);
				break;
			default:
				header("location: gotopost.php?id=".$idderpost);
				break;
		}
	}
	else
		$_REQUEST['action']="form";

}

if($_REQUEST['action']=="form")
{
	$tpl->box['forumcontent']=NULLSTR;
	$tpl->box['facultatif']=NULLSTR;

	if (!is_int($Parent) || !is_int($ForumID))	geterror("novalidlink");

	$_GET['id'] = $Parent;
	$_GET['p']=1;

	$tpl->box['smilechecked'] = NULLSTR;
	$tpl->box['bbcodechecked'] = NULLSTR;
	$tpl->box['notifychecked'] = NULLSTR;
	$tpl->box['mailnotify'] = NULLSTR;
	$tpl->box['sondage'] = NULLSTR;
	$tpl->box['boxconnected'] = NULLSTR;
	$Icon_Select = array();
	array_rempl($Icon_Select,1,16,NULLSTR);

	////////////////////////////////////////////////////
	// on vérifie que le sujet existe et qu'il est open
	if($Parent!=0)
	{
		$query	=	$sql->query("SELECT idtopic,opentopic,idforum FROM "._PRE_."topics WHERE idtopic=%d", $Parent)->execute();
		$nb	=	$query->num_rows();

		if ($nb	== 0)	geterror("novalidlink");
		else
		{
			$j	=	$query->fetch_array();
			if($j['opentopic']=="N")		geterror("closedtopic");
		}
	}

	$ForumInfo = getforumname($ForumID);
	$table_smileys=getloadsmileys();

	// ###### Navigation ######

	$ForumInfo['cattitle']=getformatrecup($ForumInfo['cattitle']);
	$ForumInfo['forumtitle']=getformatrecup($ForumInfo['forumtitle']);
	$tpl->treenavs=$tpl->gettemplate("treenav","treereppage");
	$cache.=$tpl->gettemplate("treenav","hierarchy");

	//////////////////////////////////////////////
	// mise en places des variables des templates

	$ForumInfo['parent']=$Parent;

	$LimiteLength 						= 		$_PERMFORUM[$ForumID]['MaxChar'];

	if($LimiteLength > 0)
		$tpl->box['limitmsgdef']		=		$LimiteLength;
	else
		$tpl->box['limitmsgdef']		=		$tpl->attlang("unlimited");

	if(strlen($error)>0)
	{
		for($i=1;$i<17;$i++) //on cherche l'icône à éditer
			isicon("icon".$i,$i);

		$tpl->box['errorbox']=$tpl->gettemplate("repondre","errorbox");

		$Subject		=	getformatrecup(getrecupforform($_POST['sujet']));
		$tpl->box['quotemsg']	=	getformatrecup(getrecupforform($_POST['msg']));

		if(isset($_POST['questpoll']))			$questpoll		=	htmlentities($_POST['questpoll'], ENT_COMPAT,'ISO-8859-1', true);
		else									$questpoll		=	NULLSTR;

		if(isset($_POST['bbcode']) && $_POST['bbcode'] == "non")	$tpl->box['bbcodechecked']	=	" CHECKED";
		if(isset($_POST['smilecode']) && $_POST['smilecode'] == "non")	$tpl->box['smilechecked']		=	" CHECKED";
		if(isset($_POST['notifyme']) && $_POST['notifyme'] == "oui")	$tpl->box['notifychecked']	=	" CHECKED";
	}
	else
	{
		$Icon_Select[1]		=	" CHECKED ";
		$tpl->box['errorbox']=NULLSTR;
	}

	////////////////////////////////
	// Mise en place de la citation

	if (isset($_GET['quote']))
	{
		$quote 					= 	intval($_GET['quote']);
		$tpl->box['quotemsg']	=	getquote($quote);
	}


	if($_USER['userid'] == 0)
		$tpl->box['pseudobox']	=	$tpl->gettemplate("repondre","boxguest");
	else
	{
		$posteurpseudo 			= 	getformatrecup($_USER['username']);
		$tpl->box['pseudobox']	=	$tpl->gettemplate("repondre","boxmembre");
	}

	if($_FORUMCFG['mailnotify']=="Y" && $_USER['userstatus'] > 1)
		$tpl->box['mailnotify']	=	$tpl->gettemplate("writebox","mailnotify");

	if($_USER['wysiwyg'] == "Y")
		$tpl->box['javascript']	=	$tpl->gettemplate("writebox_wysiwyg","wysiwygjs");
	else
		$tpl->box['javascript']	=	$tpl->gettemplate("entete","getjscompter");

	$_FORUMCFG['limitmsg']		=	$_PERMFORUM[$ForumID]['MaxChar'];
	$tpl->box['boxwritepage']		=	affwritebox();

	//////////////////////////////
	// Si Sondage

	$tpl->box['affpoll']	=	NULLSTR;

	if(isset($_REQUEST['newpoll']) && $_REQUEST['newpoll']=="true" && $Parent==0 && $_PERMFORUM[$ForumID][5])
	{
		$tpl->box['pollchoix']	=	"";

		for($i=1;$i<$_FORUMCFG['limitpoll']+1;$i++)
		{
			$pollvalue		 =	htmlentities($_POST['choixvote'][$i], ENT_COMPAT,'ISO-8859-1', true);
			$tpl->box['pollchoix']	.=	$tpl->gettemplate("repondre","pagepollchoice");
		}
		$tpl->box['affpoll']	=	$tpl->gettemplate("repondre","pagepoll");
	}
	elseif(isset($_REQUEST['newpoll']) && $_REQUEST['newpoll']=="true"  && !$_PERMFORUM[$ForumID][5])
		geterror("call_loginbox");

	if ($Parent==0)
		$tpl->box['valuesend']	=	$tpl->attlang("newtopic");
	else
	{
		$tpl->box['valuesend']	=	$tpl->attlang("newrep");
		$tpl->box['facultatif']	=	$tpl->attlang("facultatif");
	}


	//////////////////////////////////////////////
	// affichage des précédents message si besoin

	if($Parent>0)
	{
		InitBBcode();

		if($_FORUMCFG['canpostmsgcache']=="Y")
		{
			$query=$sql->query("SELECT idpost FROM "._PRE_."posts WHERE parent=%d ORDER BY date LIMIT 0,1", [$Parent])->execute();
			list($IdTopic) = $query->fetch_array();
		}


		$query = $sql->query("SELECT "._PRE_."posts.idpost AS idpost,"._PRE_."posts.sujet AS sujetpost, "._PRE_."posts.date AS datepost,
		"._PRE_."posts.msg AS msgpost, "._PRE_."posts.icone AS iconpost, "._PRE_."posts.idmembre AS posterid,"._PRE_."posts.smiles AS smiles,"._PRE_."posts.parent AS parent,"._PRE_."posts.bbcode AS afbbcode, "._PRE_."posts.poll AS poll, "._PRE_."posts.pseudo, "._PRE_."user.*
		FROM "._PRE_."posts
		LEFT JOIN "._PRE_."user ON "._PRE_."posts.idmembre="._PRE_."user.userid
		WHERE "._PRE_."posts.parent=%d
		ORDER BY "._PRE_."posts.date DESC LIMIT 0,10", $Parent)->execute();

		while ($DetailMsg=$query->fetch_array())
		{
			$tpl->box['affsujetpost']=NULLSTR;
			$tpl->box['forumcontent'].=affdetailtopic(0,false);
		}
	}

	$cache.=$tpl->gettemplate("repondre","repaccueil");

	$tps = number_format(get_microtime() - $tps_start,4);

	$cache.=$tpl->gettemplate("baspage","endhtml");
	$tpl->output($cache);
}

