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

require("entete.php"); 
getlangage("adm_createforum");

$tpl->box['titlesection'] = $tpl->attlang("titlemodifforum");

// ######################### fonctions #########################
function getplace($cat,$forum,$position)
{
	global $sql,$tpl,$pos;
	$chaine = NULLSTR;
	
	$query = $sql->query("SELECT * FROM "._PRE_."forums WHERE forumcat=%d ORDER BY forumorder", $cat)->execute();
	
	while($pos=$query->fetch_array())
	{
		if($forum==$pos['forumid'])
			$chaine .= $tpl->gettemplate("adm_createforum","posactual");
		else
			$chaine .= $tpl->gettemplate("adm_createforum","posdest");
	}
	return($chaine);
}


function getplacemodo($forum,$position)
{
	global $sql,$tpl,$pos;
	$chaine = NULLSTR;
	
	$query=$sql->query("SELECT * FROM "._PRE_."moderateur WHERE forumident=%d ORDER BY modoorder", $forum)->execute();
	
	while($pos=$query->fetch_array())
	{
		if($position==$pos['modoorder'])
			$chaine .= $tpl->gettemplate("adm_createforum","posmodoactual");
		else
			$chaine .= $tpl->gettemplate("adm_createforum","posmododest");
	}
	return($chaine);

}
// #############################################################

if($_REQUEST['action']=="changepos")
{
	if($_GET['changeto']<$_GET['place'])
	{
		$query=$sql->query("UPDATE "._PRE_."forums SET forumorder=forumorder+1 WHERE forumcat=%d AND forumorder>=%d AND forumorder<=%d", array($_GET['cat'], $_GET['changeto'], $_GET['place']))->execute();
		$query=$sql->query("UPDATE "._PRE_."forums SET forumorder=%d WHERE forumid=%d", array($_GET['changeto'], $_GET['forumid']))->execute();
	}
	elseif ($_GET['changeto']>$_GET['place'])
	{
		$query=$sql->query("UPDATE "._PRE_."forums SET forumorder=forumorder-1 WHERE forumcat=%d AND forumorder<=%d AND forumorder>=%d", array($_GET['cat'], $_GET['changeto'], $_GET['place']))->execute();
		$query=$sql->query("UPDATE "._PRE_."forums SET forumorder=%d WHERE forumid=%d", array($_GET['changeto'], $_GET['forumid']))->execute();
	}
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="changeposmodo")
{
	if($_GET['changeto']<$_GET['place'])
	{
		$query=$sql->query("UPDATE "._PRE_."moderateur SET modoorder=modoorder+1 WHERE forumident=%d AND modoorder<%d AND modoorder>=%d", array($_GET['forumid'], $_GET['place'], $_GET['changeto']))->execute();
		$query=$sql->query("UPDATE "._PRE_."moderateur SET modoorder=%d WHERE forumident=%d AND idusermodo=%d", array($_GET['changeto'], $_GET['forumid'], $_GET['modo']))->execute();
	}
	elseif ($_GET['changeto']>$_GET['place'])
	{
		$query=$sql->query("UPDATE "._PRE_."moderateur SET modoorder=modoorder-1 WHERE forumident=%d AND modoorder<=%d AND modoorder>%d", array($_GET['forumid'], $_GET['changeto'], $_GET['place']))->execute();
		$query=$sql->query("UPDATE "._PRE_."moderateur SET modoorder=%d WHERE forumident=%d AND idusermodo=%d", array($_GET['changeto'], $_GET['forumid'], $_GET['modo']))->execute();
	}
	$_REQUEST['id']=$_GET['forumid'];
	unset($_GET['forumid']);
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="addmodo")
{
	$identuser			=	intval($_GET['identuser']);
	$id					=	intval($_GET['id']);
	$query				=	$sql->query("SELECT login FROM "._PRE_."user WHERE userid = %d", $identuser)->execute();
	
	list($username)	=	$query->fetch_array();
	$username			=	getformatdbtodb($username);
	
	$query				=	$sql->query("SELECT * FROM "._PRE_."moderateur WHERE forumident = %d", $id)->execute();
	$modoorder			=	$query->num_rows()+1;
	
	$query				=$sql->query("INSERT INTO "._PRE_."moderateur (forumident,idusermodo,modologin,modoorder,modorights) VALUES (%d,%d,'%s',%d,215)", array($id, $identuser, $username, $modoorder))->execute();
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="updatemodo")
{
	$Modorights = get_intfromright($_POST['modorights']);
	
	$query=$sql->query("UPDATE "._PRE_."moderateur SET modorights = %d WHERE forumident=%dAND idusermodo=%d", array($Modorights, $_REQUEST['id'], $_REQUEST['identuser']))->execute();
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="editmodo")
{
	$Modorights = array(false, false, false, false, false, false, false, false, false);
	$Check = array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	
	$query=$sql->query("SELECT * FROM "._PRE_."moderateur WHERE forumident=%d AND idusermodo=%d", array($_REQUEST['id'], $_REQUEST['identuser']))->execute();
	$Modo=$query->fetch_array();
	
	$Modo['modologin']=getformatrecup($Modo['modologin']);
	
	$Modorights = get_rightfromint($Modorights,$Modo['modorights']);
	$Check = array_map("Return_Checked",$Modorights);

	$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","modoeditpage");
	
}

if($_REQUEST['action']=="delmodo")
{
	$query=$sql->query("SELECT modoorder from "._PRE_."moderateur WHERE forumident=%d AND idusermodo=%d", array($_POST['id'], $_POST['identuser']))->execute();
	list($modoorder)=$query->fetch_array();
	
	$query=$sql->query("DELETE FROM "._PRE_."moderateur WHERE forumident=%d AND idusermodo=%d", array($_POST['id'], $_POST['identuser']))->execute();
	$query=$sql->query("UPDATE "._PRE_."moderateur SET modoorder=modoorder-1 WHERE forumident=%d AND modoorder>=%d", array($_POST['id'], $modoorder))->execute();
	
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="save")
{
	$id			=	intval($_POST['id']);	
	
	// ##### Gestion si l'on change de catégorie #####
	$query	=	$sql->query("SELECT forumcat,forumorder FROM "._PRE_."forums WHERE forumid=%d", $id)->execute();
	$j	=	$query->fetch_array();
	
	if($j['forumcat']!=$_POST['cat'])
	{
		$query		=	$sql->query("SELECT forumorder FROM "._PRE_."forums WHERE forumcat=%d", $_POST['cat'])->execute();
		$nb		=	$query->num_rows();
		$order		=	$nb+1;
		$query		=	$sql->query("UPDATE "._PRE_."forums SET forumorder=forumorder-1 WHERE forumcat=%d AND forumorder>%d", array($j['forumcat'], $j['forumorder']))->execute();
	}
	else
		$order		=	$j['forumorder'];

	// ##### Gestion des variables #####	
	$forumname				=	getformatmsg($_POST['forumname']);
	$forumcoment			=	getformatmsg($_POST['forumcoment']);
	
	$query					=	$sql->query("SELECT id_group FROM "._PRE_."groups ORDER BY id_group")->execute();
	while($Group			=	$query->fetch_array())
	{
		$Id_Group			=	$Group['id_group'];
		if(isset($_POST['droits'][$Id_Group]))
			$IntDroitFor 	= 	get_intfromright($_POST['droits'][$Id_Group]);
		else
			$IntDroitFor	=	0;
			
		if(isset($_POST['MaxChar'][$Id_Group]))
			$MaxChar		=	intval($_POST['MaxChar'][$Id_Group]);
		else
			$MaxChar		=	0;
			
		$query_group		=	$sql->query("REPLACE INTO "._PRE_."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (%d, %d, %d, %d)", array($Id_Group, $id, $IntDroitFor, $MaxChar))->execute();
	}
	
	$query=$sql->query("UPDATE "._PRE_."forums SET forumcat=%d,forumtitle='%s',forumcomment='%s',forumorder=%d, openforum='%s' WHERE forumid=%d", array($_POST['cat'], $forumname, $forumcoment, $order, $_POST['openorclose'], $_POST['id']))->execute();

	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="modify")
{
	$tpl->box['IsSaved'] = NULLSTR;
	$id		=	intval($_REQUEST['id']);
	
	$query		=	$sql->query("SELECT * FROM "._PRE_."forums WHERE forumid=%d", $id)->execute();
	$InfosForum	=	$query->fetch_array();
	
	$InfosForum['forumtitle'] = getformatrecup($InfosForum['forumtitle']);
	$InfosForum['forumcomment'] = getformatrecup($InfosForum['forumcomment']);
	
	// #### Le forum est-il ouvert? ####
	$OpenForum	= 	array(0 => "",1 => "");
	
	if($InfosForum['openforum']=='Y')	$OpenForum[0] = " SELECTED";
	else					$OpenForum[1] = " SELECTED";
				
	// #### Sélection catégories ####
	$query		=	$sql->query("SELECT catid,cattitle FROM "._PRE_."categorie ORDER BY catid")->execute();
	$nbcat		=	$query->num_rows();
	
	if($nbcat==0)
		$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","nocat");
	else
	{
		// #### Catégorie parente ####
		
		$tpl->box['catlist']	=	"";
		while($Cats=$query->fetch_array())
		{
			$Selected	=	"";
			if($Cats['catid']==$InfosForum['forumcat'])
				$Selected	=	" SELECTED";
				
			$Cats['cattitle']	=	getformatrecup($Cats['cattitle']);
			$tpl->box['catlist']	.=	$tpl->gettemplate("adm_createforum","selectcat");
		}
		
		
		$tpl->box['pagedest']		=	"modifforum.php";
		
		//////////////////////////////////
		
		$FPerm				=	array();
		$query				=	$sql->query("SELECT * FROM "._PRE_."groups_perm WHERE id_forum=%d ORDER BY id_group", $id)->execute();
		while($j = $query->fetch_array())
		{
			$FPerm[$j['id_group']]['droits'] 	= $j['droits'];
			$FPerm[$j['id_group']]['MaxChar'] = $j['MaxChar'];		
		}
		
		$tpl->box['listedroits'] 		= 	"";
		$query 				= 	$sql->query("SELECT * FROM "._PRE_."groups ORDER BY id_group")->execute();
		$NbGroups			= 	$query->num_rows();
		$i				=	1;
		
		while($Grp = $query->fetch_array())
		{
			$Selected		=	array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
			if(!isset($FPerm[$Grp['id_group']]['droits']))		$FPerm[$Grp['id_group']]['droits'] 		= 0;
			if(!isset($FPerm[$Grp['id_group']]['MaxChar']))		$FPerm[$Grp['id_group']]['MaxChar'] 	= $FPerm[$Grp['parent']]['MaxChar'];
			
			$Selected 		= 	get_rightfromint($Selected, $FPerm[$Grp['id_group']]['droits']);
			
			$AffRights		=	array_map("Return_Checked",$Selected);
			$DefMaxChar		=	$FPerm[$Grp['id_group']]['MaxChar'];			
			
			if($Grp['id_group']==1)
				$tpl->box['listedroits'] .= 	$tpl->gettemplate("adm_createforum","ligne_droits_guests");
			else
				$tpl->box['listedroits'] .= 	$tpl->gettemplate("adm_createforum","ligne_droits");
			$i++;
		}		
		
		//////////////////////////////////
		
		$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","formulaire");
		
		
		// #### partie modérateurs ####
		
		$tpl->box['mododispolist'] = "";
		
		$query		=	$sql->query("SELECT id_group FROM "._PRE_."groups WHERE (Droits_generaux BETWEEN 524288 AND 1048575) OR (Droits_generaux BETWEEN 1572863 AND 2097151)")->execute();
		
		if($query->num_rows()>0)
		{
			$TabLstGrp	=	array();
			while($j = $query->fetch_array())
				$TabLstGrp[] = $j['id_group'];
			
			$LstGrp 	= 	"'".implode("','",$TabLstGrp)."'";
			
			$query		=	$sql->query("SELECT "._PRE_."user.userid,"._PRE_."user.login,"._PRE_."moderateur.* FROM "._PRE_."user LEFT JOIN "._PRE_."moderateur ON "._PRE_."user.userid="._PRE_."moderateur.idusermodo AND "._PRE_."moderateur.forumident=%d WHERE "._PRE_."moderateur.idusermodo IS NULL AND "._PRE_."user.userstatus IN ($LstGrp)", $_REQUEST['id'])->execute();
			$nb		=	$query->num_rows();
			
			if($nb==0)
				$tpl->box['mododispolist'] = $tpl->attlang("ernomododispo");
			else
			{
				while($ModoDispo = $query->fetch_array())
				{
					$ModoDispo['login'] 		= 	getformatrecup($ModoDispo['login'],false);
					$tpl->box['mododispolist'] 	.= 	$tpl->gettemplate("adm_createforum","modotochoose");
				}			
			}
			
			$query		=	$sql->query("SELECT * FROM "._PRE_."moderateur WHERE forumident=%d ORDER BY modoorder", $_REQUEST['id'])->execute();
			$nb		=	$query->num_rows();
			
			if($nb==0)
				$tpl->box['modoselectlist'] 	= 	$tpl->attlang("ernomodoselect");
			else
			{
				$tpl->box['lgnemodo'] 		= 	"";
				while($ModoSelect = $query->fetch_array())
				{
					$tpl->box['modoorder'] 		= 	"";
					
					$ModoSelect['modologin'] 	= 	getformatrecup($ModoSelect['modologin'],false);
					$tpl->box['modoorder'] 		= 	getplacemodo($_REQUEST['id'],$ModoSelect['modoorder']);
					$tpl->box['lgnemodo'] 		.= 	$tpl->gettemplate("adm_createforum","lgnemodo");
				}
				$tpl->box['modoselectlist'] 		= 	$tpl->gettemplate("adm_createforum","tblemodolist");
			}
		}
		
		$tpl->box['admcontent'].=$tpl->gettemplate("adm_createforum","modolist");
	}
}


if(empty($_REQUEST['action']))
{
	$query = $sql->query("SELECT * FROM "._PRE_."categorie ORDER BY catorder")->execute();
	$nb=$query->num_rows();
	
	$tpl->box['catforum']="";
	if ($nb==0)
		$tpl->box['catforum'].=$tpl->gettemplate("adm_createforum","nocatfound");
	else
	{
		$TabForum=array();
		
		$sqlforums = $sql->query("SELECT * FROM "._PRE_."forums ORDER BY forumcat,forumorder")->execute();
		$nbforums=$sqlforums->num_rows();
		
		if($nbforums>0)
			while($TabForum[]=$sqlforums->fetch_array());
	
		while($Cats=$query->fetch_array())
		{
			$forumlist="";

			for($cpt=0;$cpt<count($TabForum);$cpt++)
				if($TabForum[$cpt]['forumcat']==$Cats['catid'])
				{
					//récupération des infos
					$MyForum = $TabForum[$cpt];
					$MyForum['forumtitle'] = getformatrecup($MyForum['forumtitle']);
					$MyForum['position'] = getplace($Cats['catid'],$MyForum['forumid'],$MyForum['forumorder']);
					
					$forumlist .= $tpl->gettemplate("adm_createforum","ligneforum");
				}		
			
			if(strlen($forumlist)>0)
			{
				$Cats['cattitle']=getformatrecup($Cats['cattitle']);
								
				$tpl->box['catforum'].=$tpl->gettemplate("adm_createforum","lignecat");
				$tpl->box['catforum'].=$forumlist;
			}
		}
	}	
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","forumlist");
}

$cache.=$tpl->gettemplate("adm_createforum","content");
require("bas.php");
