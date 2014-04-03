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
getlangage("adm_groups");

if($_REQUEST['action'] == "confdelgrp")
{
	$id			=	intval($_POST['id']);
	$idgroup		=	intval($_POST['idgroup']);
	
	$query			=	$sql->query("DELETE FROM "._PRE_."groups WHERE id_group=%d", $id)->execute();
	$query			=	$sql->query("DELETE FROM "._PRE_."groups_perm WHERE id_group=%d", $id)->execute();
	$query			=	$sql->query("UPDATE "._PRE_."user SET userstatus=$idgroup WHERE userstatus=%d", $id)->execute();
	
	// penser à virer les couleurs si on les intègre dans les skins
	
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action'] == "delgrp")
{
	$id			=	intval($_GET['id']);
	$query	=	$sql->query("SELECT parent FROM "._PRE_."groups WHERE id_group=%d", $id)->execute();
	
	if($query->num_rows()>0 && $id>4)
	{
		list($parent)		=	$query->fetch_array();
		$tpl->box['lignedelgrp']	=	"";
		
		$query			=	$sql->query("SELECT id_group, Nom_group FROM "._PRE_."groups ORDER BY id_group")->execute();
		
		while($Grps = $query->fetch_array())
		{
			$Grps['Nom_group'] 	= 	getformatrecup($Grps['Nom_group']);
			$selected		=	"";
			
			if($parent == $Grps['id_group'])
				$selected	=	" SELECTED";
				
			$tpl->box['lignedelgrp']	.=	$tpl->gettemplate("adm_groups","lignedelgrp");
		}
			
			
	}
	
	$tpl->box['admcontent'] 	= 	$tpl->gettemplate("adm_groups","delgrp");		
}

if($_REQUEST['action'] == "savedfor")
{
	$id			=	intval($_POST['id']);
	
	$query			=	$sql->query("SELECT forumid FROM "._PRE_."forums ORDER BY forumid")->execute();
	if($query->num_rows() > 0)
	{
		while($For	=	mysql_fetch_array($query))
		{
			$ForumId	=	$For['forumid'];
			
			if(isset($_POST['droits'][$ForumId]))
				$IntDroitFor 	= 	get_intfromright($_POST['droits'][$ForumId]);
			else
				$IntDroitFor 	= 	0;
				
			if(isset($_POST['MaxChar'][$ForumId]))
				$MaxChar		=	intval($_POST['MaxChar'][$ForumId]);
			else
				$MaxChar		=	0;
				
			$query_group		=	$sql->query("REPLACE INTO "._PRE_."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (%d, %d, %d, %d)", array($id, $ForumId, $IntDroitFor, $MaxChar))->execute();
		}		
	}
	
	
	$_REQUEST['action'] = NULLSTR;	
}

if($_REQUEST['action'] == "dfor")
{
	$id 			=	intval($_REQUEST['id']);
	$i				=	0;

	// ######################################################################
	// #### Affichage des catégories et forums pour sélection des droits ####
	
	$query 				= 	$sql->query("SELECT * FROM "._PRE_."categorie ORDER BY catorder")->execute();
	$nb				=	$query->num_rows();
	
	$tpl->box['listedroits']	=	"";
	if ($nb==0)
		$tpl->box['catforum']	.=	$tpl->gettemplate("adm_groups","nocatfound");
	else
	{
		$TabForum		=	array();
		$sqlforums 		= 	$sql->query("SELECT "._PRE_."forums.forumid,
							    "._PRE_."forums.forumcat,
							    "._PRE_."forums.forumtitle,
							    "._PRE_."groups_perm.droits,
							    "._PRE_."groups_perm.MaxChar
						FROM "._PRE_."forums LEFT JOIN "._PRE_."groups_perm ON "._PRE_."groups_perm.id_group=%d AND "._PRE_."groups_perm.id_forum = "._PRE_."forums.forumid
						ORDER BY "._PRE_."forums.forumcat,"._PRE_."forums.forumorder", $id)->execute();
						
		$nbforums		=	$sqlforums->num_rows();
		
		if($nbforums>0)
			while($TabForum[]	=	$sqlforums->fetch_array());

		while($Cats=mysql_fetch_array($query))
		{
			$forumlist	=	"";

			for($cpt=0;$cpt<count($TabForum);$cpt++)
				if($TabForum[$cpt]['forumcat'] == $Cats['catid'])
				{
					$Selected		=	array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
					$MyForum 		= 	$TabForum[$cpt];
					
					$Selected		=	get_rightfromint($Selected, $MyForum['droits']);
					
					if($error)
					{
						if(count($_POST['droits'][$MyForum['forumid']])>0)
							$AffRights	=	array_map("Return_Checked",$_POST['droits'][$MyForum['forumid']]);
						$DefMaxChar	=	$_POST['MaxChar'][$MyForum['forumid']];
					}
					else
					{
						$AffRights	=	array_map("Return_Checked",$Selected);
						$DefMaxChar		=	$MyForum['MaxChar'];
					}
					$MyForum['forumtitle'] 	= 	getformatrecup($MyForum['forumtitle']);
					$i++;
					if($id==1)
						$forumlist 		.= 	$tpl->gettemplate("adm_groups","ligne_droits_guests");
					else
						$forumlist 		.= 	$tpl->gettemplate("adm_groups","ligne_droits");
				}		
			
			if(strlen($forumlist)>0)
			{
				$Cats['cattitle']		=	getformatrecup($Cats['cattitle']);
				$tpl->box['listedroits']	.=	$tpl->gettemplate("adm_groups","lignecat");
				$tpl->box['listedroits']	.=	$forumlist;
			}
		}
	}

	 		
	$tpl->box['admcontent'] 	= 	$tpl->gettemplate("adm_groups","edit_forumdroits");
}

if($_REQUEST['action'] == "savenewgroup")
{
	$error = false;
	$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['Grp_Name']);
	
	$Droits_gen 	= 	array_map("return_int",$_POST['Droits_gen']);
	
	if(strlen($testchain)>0)
	{
		$parentgroup	=	intval($_POST['parentgroup']);
		$Grp_Name 	= 	getformatmsg($_POST['Grp_Name']);
		
		if(isset($_POST['ShowSelected']) && is_array($_POST['ShowSelected']) && count($_POST['ShowSelected']) > 0)
			$IntDroitGen 	= 	get_intfromright($_POST['ShowSelected']);
		else
			$IntDroitGen	=		0;
		
		$query		=	$sql->query("INSERT INTO "._PRE_."groups (parent, Nom_group, Droits_generaux,  Max_Pm, Max_Cit, Max_Sign, Max_Desc) VALUES (%d, '%s', %d, %d, %d, %d, %d)", array($parentgroup, $Grp_Name, $IntDroitGen, $Droits_gen['Max_Pm'], $Droits_gen['Max_Cit'], $Droits_gen['Max_Sign'], $Droits_gen['Max_Desc']))->execute();
		$IdNewGroup	=	$query->insert_id();
		
		if(isset($_POST['droits']) && is_array($_POST['droits']) && count($_POST['droits']) > 0)
			foreach($_POST['droits'] as $key => $value)
			{
				$IntDroitFor = get_intfromright($value);
				$MaxChar	=	intval($_POST['MaxChar'][$key]);
				$query		=	$sql->query("INSERT INTO "._PRE_."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (%d, %d, %d, %d)", array($IdNewGroup, $key, $IntDroitFor, $MaxChar))->execute();
			}
		
		$parentgroupcolor	=	'grp'.$parentgroup;
		$query		=	$sql->query("SELECT id, valeur FROM "._PRE_."skins WHERE propriete = '%s'", $parentgroupcolor)->execute();
		while(list($idskin,$colorgroup) = $query->fetch_array())
		{
			$newgroup	=	'grp'.$IdNewGroup;
			$saveskin	=	$sql->query("INSERT INTO "._PRE_."skins (id,propriete,valeur) VALUES (%d,'%s','%s')", array($idskin, $newgroup, $colorgroup))->execute();
		}
		$_REQUEST['action'] = NULLSTR;
	}
	else
	{
		$error = true;
		$_REQUEST['action'] = "newgroup";
	}
	 
}

if($_REQUEST['action'] == "newgroup")
{
	$tpl->box['errormsg'] = NULLSTR;
	$parentgroup = intval($_POST['parentgroup']);
	
	$Droits = array(	false,	false,	false,	false,
				false,	false,	false,	false,
				false,	false,	false,	false,
				false,	false,	false,	false,
				false,	false,	false,	false, false);
	
	$ShowSelected = array();
	
	if($error)
	{
		$Grp_Name		=	getrecupforform($_POST['Grp_Name']);
		if(count($_POST['ShowSelected'])>0)
			$ShowSelected		=	array_map("Return_Checked",$_POST['ShowSelected']);
		$tpl->box['errormsg']	=	$tpl->gettemplate("adm_groups","errormsg");
	}
	else
	{
		//$id = intval($_REQUEST['id']);
		$query = $sql->query("SELECT * FROM "._PRE_."groups WHERE id_group='$parentgroup'");
		
		$Droits_gen = mysql_fetch_array($query);
		
		if($Droits_gen['Droits_generaux'] > 0)
		{
			$Droits = get_rightfromint($Droits, $Droits_gen['Droits_generaux']);
			$ShowSelected	=	array_map("Return_Checked",$Droits);
		}
	}

	$tpl->box['defdroits_gen_limit']	=	$tpl->gettemplate("adm_groups","defdroits_gen_limit");
	$tpl->box['defdroits_gen_autho']	=	$tpl->gettemplate("adm_groups","defdroits_gen_autho");
	
	$tpl->box['droitsgen'] = $tpl->gettemplate("adm_groups","defdroits_gen");
	
	// ######################################################################
	// #### Affichage des catégories et forums pour sélection des droits ####
	
	$query 			= 	$sql->query("SELECT * FROM "._PRE_."categorie ORDER BY catorder");
	$nb			=	mysql_num_rows($query);
	
	$tpl->box['listedroits']	=	"";
	if ($nb==0)
		$tpl->box['catforum']	.=	$tpl->gettemplate("adm_groups","nocatfound");
	else
	{
		$TabForum	=	array();
		$sqlforums 	= 	$sql->query("SELECT "._PRE_."forums.forumid,
							    "._PRE_."forums.forumcat,
							    "._PRE_."forums.forumtitle,
							    "._PRE_."groups_perm.droits,
							    "._PRE_."groups_perm.MaxChar
						FROM "._PRE_."forums LEFT JOIN "._PRE_."groups_perm ON "._PRE_."groups_perm.id_group='$parentgroup' AND "._PRE_."groups_perm.id_forum = "._PRE_."forums.forumid
						ORDER BY "._PRE_."forums.forumcat,"._PRE_."forums.forumorder");
						
		$nbforums	=	mysql_num_rows($sqlforums);
		
		if($nbforums>0)
			while($TabForum[]	=	mysql_fetch_array($sqlforums));

		while($Cats=mysql_fetch_array($query))
		{
			$forumlist	=	"";

			for($cpt=0;$cpt<count($TabForum);$cpt++)
				if($TabForum[$cpt]['forumcat']==$Cats['catid'])
				{
					$Selected		=	array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
					$MyForum 		= 	$TabForum[$cpt];
					
					$Selected		=	get_rightfromint($Selected, $MyForum['droits']);
					
					if($error)
					{
						if(count($_POST['droits'][$MyForum['forumid']])>0)
							$AffRights	=	array_map("Return_Checked",$_POST['droits'][$MyForum['forumid']]);
						$DefMaxChar	=	$_POST['MaxChar'][$MyForum['forumid']];
					}
					else
					{
						$AffRights	=	array_map("Return_Checked",$Selected);
						$DefMaxChar		=	$MyForum['MaxChar'];
					}
					$MyForum['forumtitle'] 	= 	getformatrecup($MyForum['forumtitle']);
					$i			=	$cpt + 1;
					$forumlist 		.= 	$tpl->gettemplate("adm_groups","ligne_droits");
				}		
			
			if(strlen($forumlist)>0)
			{
				$Cats['cattitle']=getformatrecup($Cats['cattitle']);
								
				$tpl->box['listedroits']		.=	$tpl->gettemplate("adm_groups","lignecat");
				$tpl->box['listedroits']		.=	$forumlist;
			}
		}
	}
		
	$tpl->box['admcontent'] = $tpl->gettemplate("adm_groups","addgroup");
		
	
}

if($_REQUEST['action'] == "savedgen")
{
	$id = intval($_POST['id']);
	$Droits_gen['Max_Cit'] = intval($_POST['Droits_gen']['Max_Cit']);
	$Droits_gen['Max_Sign'] = intval($_POST['Droits_gen']['Max_Sign']);
	$Droits_gen['Max_Pm'] = intval($_POST['Droits_gen']['Max_Pm']);
	$Droits_gen['Max_Desc'] = intval($_POST['Droits_gen']['Max_Desc']);
	
	if(count($_POST['ShowSelected'])>0)
		$Int_Rights = get_intfromright($_POST['ShowSelected']);
	else
		$Int_Rights = 0;
		
	
	$query = $sql->query("UPDATE "._PRE_."groups SET Droits_generaux = $Int_Rights, Max_Pm = ".$Droits_gen['Max_Pm']." , Max_Cit = ".$Droits_gen['Max_Cit'].", Max_Sign = ".$Droits_gen['Max_Sign'].", Max_Desc = ".$Droits_gen['Max_Desc']." WHERE id_group = $id");
	
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action'] == "dgen")
{
	$tpl->box['defdroits_gen_limit']	=	NULLSTR;
	$tpl->box['defdroits_gen_autho']	=	NULLSTR;
	
	$Droits = array(	false,	false,	false,	false,
				false,	false,	false,	false,
				false,	false,	false,	false,
				false,	false,	false,	false,
				false,	false,	false,	false, false);
	
	$ShowSelected = array();

	$id = intval($_REQUEST['id']);
	$query = $sql->query("SELECT * FROM "._PRE_."groups WHERE id_group=$id");
	
	$Droits_gen = mysql_fetch_array($query);
		
	if($Droits_gen['Droits_generaux'] > 0)
		{
			$Droits = get_rightfromint($Droits, $Droits_gen['Droits_generaux']);
			
			foreach($Droits as $key => $value)
			{
				if($value)
					$ShowSelected[$key] = " CHECKED";
				else
					$ShowSelected[$key] = "";
			}
		}
	
	if($id>1)
	{
		$tpl->box['defdroits_gen_limit']	=	$tpl->gettemplate("adm_groups","defdroits_gen_limit");
		$tpl->box['defdroits_gen_autho']	=	$tpl->gettemplate("adm_groups","defdroits_gen_autho");
	}
	
	$tpl->box['defdroits_gen'] = $tpl->gettemplate("adm_groups","defdroits_gen");
	$tpl->box['admcontent'] = $tpl->gettemplate("adm_groups","formdroits_gen");
	
}

if(empty($_REQUEST['action']))
{
	$tpl->box['ligne_group'] = "";
	$tpl->box['grpselect'] = "";
	
	$query = $sql->query("SELECT id_group, Nom_group FROM "._PRE_."groups ORDER BY id_group");
	
	while(list($id_group,$Nom_group) = mysql_fetch_array($query))
	{
		if($id_group > 4)	$tpl->box['linkdelete']	=	$tpl->gettemplate("adm_groups","linkdelete");
		else			$tpl->box['linkdelete'] 	= 	"";
		
		$tpl->box['ligne_group'] .= $tpl->gettemplate("adm_groups","ligne_group");
		if($id_group<5)
			$tpl->box['grpselect'] .= $tpl->gettemplate("adm_groups","grpselect");
	}
	
	$tpl->box['admcontent'] = $tpl->gettemplate("adm_groups","list_groups");	
}

$cache.=$tpl->gettemplate("adm_groups","content");
require("bas.php");

