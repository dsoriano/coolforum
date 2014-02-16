<?php
//*********************************************************************************
//*                                                                               *
//*                  CoolForum v.0.8.5 Beta : Forum de discussion                   *
//*              Copyright �2001-2014 SORIANO Denis alias Cool Coyote             *
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
//*       Forum Cr�� par SORIANO Denis (Cool Coyote)                              *
//*       contact : coyote@coolcoyote.net                                         *
//*       site web et t�l�chargement : http://www.coolforum.net                   *
//*                                                                               *
//*********************************************************************************

require("entete.php"); 
getlangage("adm_createforum");

$tpl->box['titlesection'] = $tpl->attlang("titlemodifforum");

// ######################### fonctions #########################
function getplace($cat,$forum,$position)
{
	global $sql,$tpl,$pos,$_PRE;
	$chaine = NULLSTR;
	
	$query = $sql->query("SELECT * FROM ".$_PRE."forums WHERE forumcat='$cat' ORDER BY forumorder");
	
	while($pos=mysql_fetch_array($query))
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
	global $sql,$tpl,$pos,$_PRE;
	$chaine = NULLSTR;
	
	$query=$sql->query("SELECT * FROM ".$_PRE."moderateur WHERE forumident='$forum' ORDER BY modoorder");
	
	while($pos=mysql_fetch_array($query))
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
		$query=$sql->query("UPDATE ".$_PRE."forums SET forumorder=forumorder+1 WHERE forumcat=".$_GET['cat']." AND forumorder>=".$_GET['changeto']." AND forumorder<=".$_GET['place']);
		$query=$sql->query("UPDATE ".$_PRE."forums SET forumorder=".$_GET['changeto']." WHERE forumid=".$_GET['forumid']);
	}
	elseif ($_GET['changeto']>$_GET['place'])
	{
		$query=$sql->query("UPDATE ".$_PRE."forums SET forumorder=forumorder-1 WHERE forumcat=".$_GET['cat']." AND forumorder<=".$_GET['changeto']." AND forumorder>=".$_GET['place']);
		$query=$sql->query("UPDATE ".$_PRE."forums SET forumorder=".$_GET['changeto']." WHERE forumid=".$_GET['forumid']);	
	}
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="changeposmodo")
{
	if($_GET['changeto']<$_GET['place'])
	{
		$query=$sql->query("UPDATE ".$_PRE."moderateur SET modoorder=modoorder+1 WHERE forumident='".$_GET['forumid']."' AND modoorder<".$_GET['place']." AND modoorder>=".$_GET['changeto']);
		$query=$sql->query("UPDATE ".$_PRE."moderateur SET modoorder=".$_GET['changeto']." WHERE forumident='".$_GET['forumid']."' AND idusermodo=".$_GET['modo']);
	}
	elseif ($_GET['changeto']>$_GET['place'])
	{
		$query=$sql->query("UPDATE ".$_PRE."moderateur SET modoorder=modoorder-1 WHERE forumident=".$_GET['forumid']." AND modoorder<=".$_GET['changeto']." AND modoorder>".$_GET['place']);
		$query=$sql->query("UPDATE ".$_PRE."moderateur SET modoorder=".$_GET['changeto']." WHERE forumident=".$_GET['forumid']." AND idusermodo=".$_GET['modo']);	
	}
	$_REQUEST['id']=$_GET['forumid'];
	unset($_GET['forumid']);
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="addmodo")
{
	$identuser			=	intval($_GET['identuser']);
	$id					=	intval($_GET['id']);
	$query				=	$sql->query("SELECT login FROM ".$_PRE."user WHERE userid = $identuser");
	
	list($username)	=	mysql_fetch_array($query);
	$username			=	getformatdbtodb($username);
	
	$query				=	$sql->query("SELECT * FROM ".$_PRE."moderateur WHERE forumident = $id");
	$modoorder			=	mysql_num_rows($query)+1;
	
	$query				=$sql->query("INSERT INTO ".$_PRE."moderateur (forumident,idusermodo,modologin,modoorder,modorights) VALUES ($id,$identuser,'$username','$modoorder',215)");
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="updatemodo")
{
	$Modorights = get_intfromright($_POST['modorights']);
	
	$query=$sql->query("UPDATE ".$_PRE."moderateur SET modorights = $Modorights WHERE forumident='".$_REQUEST['id']."' AND idusermodo=".$_REQUEST['identuser']);
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="editmodo")
{
	$Modorights = array(false, false, false, false, false, false, false, false, false);
	$Check = array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	
	$query=$sql->query("SELECT * FROM ".$_PRE."moderateur WHERE forumident='".$_REQUEST['id']."' AND idusermodo=".$_REQUEST['identuser']);
	$Modo=mysql_fetch_array($query);
	
	$Modo['modologin']=getformatrecup($Modo['modologin']);
	
	$Modorights = get_rightfromint($Modorights,$Modo['modorights']);
	$Check = array_map("Return_Checked",$Modorights);

	$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","modoeditpage");
	
}

if($_REQUEST['action']=="delmodo")
{
	$query=$sql->query("SELECT modoorder from ".$_PRE."moderateur WHERE forumident='".$_POST['id']."' AND idusermodo=".$_POST['identuser']);
	list($modoorder)=mysql_fetch_array($query);
	
	$query=$sql->query("DELETE FROM ".$_PRE."moderateur WHERE forumident='".$_POST['id']."' AND idusermodo='".$_POST['identuser']."'");
	$query=$sql->query("UPDATE ".$_PRE."moderateur SET modoorder=modoorder-1 WHERE forumident='".$_POST['id']."' AND modoorder>='$modoorder'");
	
	$_REQUEST['action']="modify";
}

if($_REQUEST['action']=="save")
{
	$id			=	intval($_POST['id']);	
	
	// ##### Gestion si l'on change de cat�gorie #####
	$query	=	$sql->query("SELECT forumcat,forumorder FROM ".$_PRE."forums WHERE forumid=$id");
	$j	=	mysql_fetch_array($query);
	
	if($j['forumcat']!=$_POST['cat'])
	{
		$query		=	$sql->query("SELECT forumorder FROM ".$_PRE."forums WHERE forumcat=".$_POST['cat']);
		$nb		=	mysql_num_rows($query);
		$order		=	$nb+1;
		$query		=	$sql->query("UPDATE ".$_PRE."forums SET forumorder=forumorder-1 WHERE forumcat='".$j['forumcat']."' AND forumorder>'".$j['forumorder']."'");
	}
	else
		$order		=	$j['forumorder'];

	// ##### Gestion des variables #####	
	$forumname				=	getformatmsg($_POST['forumname']);
	$forumcoment			=	getformatmsg($_POST['forumcoment']);
	
	$query					=	$sql->query("SELECT id_group FROM ".$_PRE."groups ORDER BY id_group");
	while($Group			=	mysql_fetch_array($query))
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
			
		$query_group		=	$sql->query("REPLACE INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES ('$Id_Group', '$id', '$IntDroitFor', '$MaxChar')");
	}
	
	$query=$sql->query("UPDATE ".$_PRE."forums SET forumcat='".$_POST['cat']."',forumtitle='$forumname',forumcomment='$forumcoment',forumorder='$order', openforum='".$_POST['openorclose']."' WHERE forumid=".$_POST['id']);

	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="modify")
{
	$tpl->box['IsSaved'] = NULLSTR;
	$id		=	intval($_REQUEST['id']);
	
	$query		=	$sql->query("SELECT * FROM ".$_PRE."forums WHERE forumid=$id");
	$InfosForum	=	mysql_fetch_array($query);
	
	$InfosForum['forumtitle'] = getformatrecup($InfosForum['forumtitle']);
	$InfosForum['forumcomment'] = getformatrecup($InfosForum['forumcomment']);
	
	// #### Le forum est-il ouvert? ####
	$OpenForum	= 	array(0 => "",1 => "");
	
	if($InfosForum['openforum']=='Y')	$OpenForum[0] = " SELECTED";
	else					$OpenForum[1] = " SELECTED";
				
	// #### S�lection cat�gories ####
	$query		=	$sql->query("SELECT catid,cattitle FROM ".$_PRE."categorie ORDER BY catid");
	$nbcat		=	mysql_num_rows($query);
	
	if($nbcat==0)
		$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","nocat");
	else
	{
		// #### Cat�gorie parente ####
		
		$tpl->box['catlist']	=	"";
		while($Cats=mysql_fetch_array($query))
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
		$query				=	$sql->query("SELECT * FROM ".$_PRE."groups_perm WHERE id_forum=$id ORDER BY id_group");
		while($j = mysql_fetch_array($query))
		{
			$FPerm[$j['id_group']]['droits'] 	= $j['droits'];
			$FPerm[$j['id_group']]['MaxChar'] = $j['MaxChar'];		
		}
		
		$tpl->box['listedroits'] 		= 	"";
		$query 				= 	$sql->query("SELECT * FROM ".$_PRE."groups ORDER BY id_group");
		$NbGroups			= 	mysql_num_rows($query);
		$i				=	1;
		
		while($Grp = mysql_fetch_array($query))
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
		
		
		// #### partie mod�rateurs ####
		
		$tpl->box['mododispolist'] = "";
		
		$query		=	$sql->query("SELECT id_group FROM ".$_PRE."groups WHERE (Droits_generaux BETWEEN 524288 AND 1048575) OR (Droits_generaux BETWEEN 1572863 AND 2097151)");
		
		if(mysql_num_rows($query)>0)
		{
			$TabLstGrp	=	array();
			while($j = mysql_fetch_array($query))
				$TabLstGrp[] = $j['id_group'];
			
			$LstGrp 	= 	"'".implode("','",$TabLstGrp)."'";
			
			$query		=	$sql->query("SELECT ".$_PRE."user.userid,".$_PRE."user.login,".$_PRE."moderateur.* FROM ".$_PRE."user LEFT JOIN ".$_PRE."moderateur ON ".$_PRE."user.userid=".$_PRE."moderateur.idusermodo AND ".$_PRE."moderateur.forumident='".$_REQUEST['id']."' WHERE ".$_PRE."moderateur.idusermodo IS NULL AND ".$_PRE."user.userstatus IN ($LstGrp)");
			$nb		=	mysql_num_rows($query);
			
			if($nb==0)
				$tpl->box['mododispolist'] = $tpl->attlang("ernomododispo");
			else
			{
				while($ModoDispo = mysql_fetch_array($query))
				{
					$ModoDispo['login'] 		= 	getformatrecup($ModoDispo['login'],false);
					$tpl->box['mododispolist'] 	.= 	$tpl->gettemplate("adm_createforum","modotochoose");
				}			
			}
			
			$query		=	$sql->query("SELECT * FROM ".$_PRE."moderateur WHERE forumident='".$_REQUEST['id']."' ORDER BY modoorder");
			$nb		=	mysql_num_rows($query);
			
			if($nb==0)
				$tpl->box['modoselectlist'] 	= 	$tpl->attlang("ernomodoselect");
			else
			{
				$tpl->box['lgnemodo'] 		= 	"";
				while($ModoSelect = mysql_fetch_array($query))
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
	$query = $sql->query("SELECT * FROM ".$_PRE."categorie ORDER BY catorder");
	$nb=mysql_num_rows($query);
	
	$tpl->box['catforum']="";
	if ($nb==0)
		$tpl->box['catforum'].=$tpl->gettemplate("adm_createforum","nocatfound");
	else
	{
		$TabForum=array();
		
		$sqlforums = $sql->query("SELECT * FROM ".$_PRE."forums ORDER BY forumcat,forumorder");
		$nbforums=mysql_num_rows($sqlforums);
		
		if($nbforums>0)
			while($TabForum[]=mysql_fetch_array($sqlforums));
	
		while($Cats=mysql_fetch_array($query))
		{
			$forumlist="";

			for($cpt=0;$cpt<count($TabForum);$cpt++)
				if($TabForum[$cpt]['forumcat']==$Cats['catid'])
				{
					//r�cup�ration des infos
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