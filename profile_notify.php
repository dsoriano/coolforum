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
			$query = $sql->query("UPDATE "._PRE_."posts SET notifyme='N' WHERE parent=%d AND idmembre=%d", array($transfert[1], $_USER['userid']))->execute();
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
	
	$query=$sql->query("SELECT * FROM "._PRE_."forums")->execute();
	$nb=$query->num_rows();
	if($nb>0)
	{
		while($j=$query->fetch_array())
		{
			if(isset($_PERMFORUM[$j['forumid']][1]) && $_PERMFORUM[$j['forumid']][1])
				$maskarray[]=$j['forumid'];	
		}	
	}
	$forummask ="'".implode("','",$maskarray)."'";
	$Forbidden = " AND idforum IN ($forummask) ";
	
	// Gestion des pages et récupération de la liste des topics où le membre posséde un abonnement
	
	$query = $sql->query("SELECT "._PRE_."posts.parent FROM "._PRE_."posts WHERE notifyme='Y' AND "._PRE_."posts.idmembre=%d " . $Forbidden . " GROUP BY parent", array($_USER['userid']))->execute();
	
	$nbtopics_filtered = $query->num_rows();

	if(!isset($_GET['page']))		$page	=	1;
	else							$page	=	intval($_GET['page']);
	
	$tpl->box['navpages']=getnumberpages($nbtopics_filtered,"profil_notify",$_FORUMCFG['topicparpage'],$page);
	if($nbpages>1)
		$tpl->box['numberpages']=$tpl->gettemplate("profil_notify","boxpages");
	else
		$tpl->box['numberpages']=NULLSTR;
	
	$debut = ($page*$_FORUMCFG['topicparpage'])-$_FORUMCFG['topicparpage'];
	
	$ListIDTopics = array();
	while(list($IDTopic)=$query->fetch_array())
		$ListIDTopics[]=$IDTopic;
	
	
	// Récupération de la liste des topics à afficher
	
	$total = 0;
	$tpl->box['notifycontent'] = NULLSTR;
	
	if(count($ListIDTopics)>0)
	{
		$query = $sql->query("SELECT "._PRE_."topics.idtopic,
				"._PRE_."topics.idforum,
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
				WHERE idtopic IN (%s)
				ORDER BY "._PRE_."topics.postit DESC,"._PRE_."topics.datederrep DESC LIMIT %d,%d", array(implode(",",$ListIDTopics), $debut, $_FORUMCFG['topicparpage']))->execute();
		
		$total = $query->num_rows();
		
		if(isset($_COOKIE['CoolForumDetails']))
			$cookiespost=cookdecode($_COOKIE['CoolForumDetails']);
		
		while($Topics=$query->fetch_array())
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
