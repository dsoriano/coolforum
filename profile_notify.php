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

if(preg_match('|profile.php|',$_SERVER['PHP_SELF']) == 0)
{
	header('location: profile.php');
	exit;
}

if($_FORUMCFG['usemails']=="N" || $_FORUMCFG['mailnotify']=="N")
	header("Location: profile.php");

getlangage("profile_notify");

$tpl->treenavs=$tpl->gettemplate("treenav","treeprofil");
$cache.=$tpl->gettemplate("treenav","hierarchy");

if($_REQUEST['action']=="stopnotify")
{
	$total=count($_POST['stop']);
	if($total==0)
		$tpl->box['infomsg']=$tpl->attlang("nothingchecked");
	else
	{
		$ok=true;
		for($i=0;$i<$total;$i++)
		{
			$transfert = each($_POST['stop']);
			$query = $sql->query("UPDATE ".$_PRE."posts SET notifyme='N' WHERE parent='".$transfert[1]."' AND idmembre='".$_USER['userid']."'");
			if(!$query)
				$ok=false;
		}
	
		if($ok)		$tpl->box['infomsg']=$tpl->attlang("stopnotifyok");
		else		$tpl->box['infomsg']=$tpl->attlang("stopnotifynok");
	}
	
	$tpl->box['profilcontent']=$tpl->gettemplate("profil_notify","infobox");	
	$tpl->box['profilcontent'].=getjsredirect("profile.php?p=notify&page=".$_POST['page'],3000);
}


if(empty($_REQUEST['action']))
{
	// Sécurité pour éviter l'affichage des topics appartenant à des forums dont le membre ne posséde pas les droits
	
	/*$Forbidden = array();
	
	while(list($ForumID,$ForumRights)=each($_PERMFORUM))
		if($ForumRights[1]==false)
			$Forbidden[]=$ForumID;
	
	if(count($Forbidden)>0)	$Forbidden = " AND idforum NOT IN (".implode(",",$Forbidden).") ";
	else			$Forbidden = "";*/
	
	$query=$sql->query("SELECT * FROM ".$_PRE."forums");
	$nb=mysql_num_rows($query);
	if($nb>0)
	{
		while($j=mysql_fetch_array($query))
		{
			if(isset($_PERMFORUM[$j['forumid']][1]) && $_PERMFORUM[$j['forumid']][1])
				$maskarray[]=$j['forumid'];	
		}	
	}
	$forummask ="'".implode("','",$maskarray)."'";
	$Forbidden = " AND idforum IN ($forummask) ";
	
	// Gestion des pages et récupération de la liste des topics où le membre posséde un abonnement
	
	$query = $sql->query("SELECT ".$_PRE."posts.parent FROM ".$_PRE."posts WHERE notifyme='Y' AND ".$_PRE."posts.idmembre='".$_USER['userid']."' ".$Forbidden." GROUP BY parent");
	
	$nbtopics_filtered = mysql_num_rows($query);

	if(!isset($_GET['page']))		$page	=	1;
	else							$page	=	intval($_GET['page']);
	
	$tpl->box['navpages']=getnumberpages($nbtopics_filtered,"profil_notify",$_FORUMCFG['topicparpage'],$page);
	if($nbpages>1)
		$tpl->box['numberpages']=$tpl->gettemplate("profil_notify","boxpages");
	else
		$tpl->box['numberpages']=NULLSTR;
	
	$debut = ($page*$_FORUMCFG['topicparpage'])-$_FORUMCFG['topicparpage'];
	
	$ListIDTopics = array();
	while(list($IDTopic)=mysql_fetch_array($query))
		$ListIDTopics[]=$IDTopic;
	
	
	// Récupération de la liste des topics à afficher
	
	$total = 0;
	$tpl->box['notifycontent'] = NULLSTR;
	
	if(count($ListIDTopics)>0)
	{
		$query = $sql->query("SELECT ".$_PRE."topics.idtopic,
				".$_PRE."topics.idforum,
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
				WHERE idtopic IN (".implode(",",$ListIDTopics).")
				ORDER BY ".$_PRE."topics.postit DESC,".$_PRE."topics.datederrep DESC LIMIT ".$debut.",".$_FORUMCFG['topicparpage']);
		
		$total = mysql_num_rows($query);
		
		if(isset($_COOKIE['CoolForumDetails']))
			$cookiespost=cookdecode($_COOKIE['CoolForumDetails']);
		
		while($Topics=mysql_fetch_array($query))
		{
			$forumid	=	$Topics['idforum'];
			$tpl->box['notifycontent'] .= afftopiclist(0,"profil_notify");
		}
	}
	else
		$tpl->box['notifycontent'] = $tpl->gettemplate("profil_notify","nonotify");
	
	eval("\$tpl->box['accueilnotifycmt']=\"".$tpl->attlang("accueilnotifycmt")."\";");
	
	$tpl->box['profilcontent'] = $tpl->gettemplate("profil_notify","interfaceaccueil");
}
