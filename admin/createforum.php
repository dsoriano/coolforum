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

$tpl->box['titlesection'] = $tpl->attlang("titleaddforum");

if($_REQUEST['action']=="save")
{
	$error		= 	false;
	$CatParent	=	intval($_POST['cat']);

	if($_POST['openorclose'] == "Y")
		$openorclose    = "Y";
	else
		$openorclose	= "N";
			
	$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['forumname']);

	if(strlen($testchain)>0)
	{
		$query			=	$sql->query("SELECT forumorder FROM "._PRE_."forums WHERE forumcat=%d", $_POST['cat'])->execute();
		$nb			=	$query->num_rows();
		$order			=	$nb+1;
		
		$forumname		=	getformatmsg($_POST['forumname']);
		$forumcoment		=	getformatmsg($_POST['forumcoment']);
		
		$query			=	$sql->query("INSERT INTO "._PRE_."forums (forumcat, forumtitle, forumcomment, forumorder, openforum) VALUES (%d, '%s', '%s', %d, '%s')", array($CatParent, $forumname, $forumcoment, $order, $openorclose))->execute();
		$IdForum 		= 	$query->insert_id();
		
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
				
			$query_group		=	$sql->query("REPLACE INTO "._PRE_."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (%d, %d, '%s', %d)", array($Id_Group, $IdForum, $IntDroitFor, $MaxChar))->execute();
		}	

		$tpl->box['savemsg'] 	= 	$tpl->attlang("forumsaved");
	}
	else
	{
		$error 			= 	true;
		$tpl->box['savemsg'] 	= 	$tpl->attlang("forumnotsaved");
	}

	$tpl->box['IsSaved']		=	$tpl->gettemplate("adm_createforum","savemsg");
	$_REQUEST['action'] = NULLSTR;
		
}

if(empty($_REQUEST['action']))
{
	$tpl->box['IsSaved']	=	NULLSTR;
	$_REQUEST['id'] = 0;
			
	$AffRights 	= 	array();		    
	
	if(!$error)
	{
		$CatParent = 0;
		$RightsDefine = array(	1 =>	array(0 => " CHECKED",
						      1 => " CHECKED",
						      2 => " CHECKED",
						      3 => "",
						      4 => "",
						      5 => "",
						      6 => ""),
					
					2 =>	array(0 => " CHECKED",
						      1 => " CHECKED",
						      2 => " CHECKED",
						      3 => " CHECKED",
						      4 => " CHECKED",
						      5 => "",
						      6 => ""),
						      
					3 =>	array(0 => " CHECKED",
						      1 => " CHECKED",
						      2 => " CHECKED",
						      3 => " CHECKED",
						      4 => " CHECKED",
						      5 => " CHECKED",
						      6 => " CHECKED"),
					
					4 =>	array(0 => " CHECKED",
						      1 => " CHECKED",
						      2 => " CHECKED",
						      3 => " CHECKED",
						      4 => " CHECKED",
						      5 => " CHECKED",
						      6 => " CHECKED"));
	}
	else
	{
		$InfosForum 			= array();
		$OpenForum			= array(0 => "",1 => "");
		
		if($openorclose == "Y")
			$OpenForum[0] 		= " SELECTED";
		else
			$OpenForum[1] 		= " SELECTED";
		
		$droits 			= $_POST['droits'];
		$MaxChar			= $_POST['MaxChar'];
		
		$InfosForum['forumtitle'] 	= getrecupforform($_POST['forumname']);
		$InfosForum['forumcomment'] 	= getrecupforform($_POST['forumcoment']);	
	}	

	$query=$sql->query("SELECT catid,cattitle FROM "._PRE_."categorie ORDER BY catid")->execute();
	$nbcat=$query->num_rows();

	if($nbcat==0)
		$tpl->box['admcontent']=$tpl->gettemplate("adm_createforum","nocat");

	else
	{
		$tpl->box['catlist']="";
		while($Cats=$query->fetch_array())
		{
			$Selected 		= 	"";
			if($Cats['catid'] == $CatParent)
				$Selected 	= 	" SELECTED";
				
			$Cats['cattitle']		=	getformatrecup($Cats['cattitle']);
			$tpl->box['catlist']     .=	$tpl->gettemplate("adm_createforum","selectcat");
		}
		
		$tpl->box['pagedest']		=	"createforum.php";
		$tpl->box['listedroits'] 		= 	"";
		$query 				= 	$sql->query("SELECT * FROM "._PRE_."groups ORDER BY id_group")->execute();
		$NbGroups			= 	$query->num_rows();
		$i				=	1;
		
		while($Grp = $query->fetch_array())
		{
			
			if($error)
			{
				$RightsDefine[$i]	=	array_map("Return_Checked",$droits[$i]);
				$DefMaxChar 		= 	$MaxChar[$i];							
			}
			if($Grp['id_group'] > 4)
				$AffRights 		= 	$RightsDefine[$Grp['parent']];
			else
				$AffRights 		= 	$RightsDefine[$i];

			if($Grp['id_group']==1)
				$tpl->box['listedroits'] .= 	$tpl->gettemplate("adm_createforum","ligne_droits_guests");
			else
				$tpl->box['listedroits'] .= 	$tpl->gettemplate("adm_createforum","ligne_droits");
			$i++;
		}
		
		$tpl->box['admcontent']		=	$tpl->gettemplate("adm_createforum","formulaire");
	}
}

$cache.=$tpl->gettemplate("adm_createforum","content");
require("bas.php");
