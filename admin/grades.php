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
getlangage("adm_grades");

$error 							= 	"";

// #### FONCTIONS SPECIFIQUES ####
function insert_grade($Orig_Array, $To_Insert)
{
	$Key_start					=	count($Orig_Array)+1;
	
	foreach($Orig_Array as $key => $value)
	{
		if($value[1] > $To_Insert[1])
		{
			$Key_start 			= 	$key;
			break;
		}
	}
	
	for($i = count($Orig_Array)+1 ; $i > $Key_start; $i--)
		$Orig_Array[$i]			=	$Orig_Array[$i-1];
	
	$Orig_Array[$Key_start]		=	$To_Insert;
	return($Orig_Array);
}

// #### ENREGISTREMENT MODIFICATIONS ####
if($_REQUEST['action'] == "confirmedit")
{
	$id							=	intval($_REQUEST['id']);
	
	$testchain					=	preg_replace("/([\s]{1,})/","",$_POST['sujet']);
		if(strlen($testchain) == 0 || strlen($testchain) > 35)
			$error 				= 	$tpl->attlang("badname");
	
	if(intval($_REQUEST['gd_nbmsg'])<0)
		$error 					= 	$tpl->attlang("badmsg");

	if(intval($_REQUEST['gd_nbpins'])<1)
		$error 					= 	$tpl->attlang("badpins");
	
	if(strlen($error) == 0)
	{		
		$_FORUMCFG 				= 	getconfig();
		$To_Insert				=	array();
		$To_Insert[0]			=	getrecupforform($_REQUEST['gd_nom'], true);
		$To_Insert[1]			=	intval($_REQUEST['gd_nbmsg']);
		$To_Insert[2]			=	intval($_REQUEST['gd_nbpins']);
		
		if($To_Insert[2] > 20)
			$To_Insert[2]		=	20;
		
		$All_Grade 				= 	unserialize($_FORUMCFG['grades']);
		$nb_grades 				= 	count($All_Grade);

		$dest					=	$nb_grades;
		
		if($nb_grades > 0 && is_array($All_Grade))
		{
			foreach($All_Grade AS $key => $value)
			{
				if($To_Insert[1] < $value[1])
				{
					$dest		=	$key;
					break;
				}		
			}
			
			
			if($dest < $id)
				for($i = $id; $i > $dest; $i--)
					$All_Grade[$i]		=		$All_Grade[$i-1];
			
			
			if($dest > $id)
			{
				$dest --;
				for($i = $id; $i < $dest; $i++)
					$All_Grade[$i]		=		$All_Grade[$i+1];

			}
			$All_Grade[$dest]	=	$To_Insert;
		}		
		
		$Tabl_to_save 			= 	serialize($All_Grade);
		$query					=	$sql->query("UPDATE "._PRE_."config SET valeur = '%s' WHERE options = 'grades'", $Tabl_to_save)->execute();
		
		unset($_REQUEST['gd_nom'], $_REQUEST['gd_nbmsg'], $_REQUEST['gd_nbpins']);
		
		$_REQUEST['action'] = NULLSTR;
	}
	else
	{
		$gd_nom					=	getrecupforform($_REQUEST['gd_nom']);
		$gd_nbmsg				=	intval($_REQUEST['gd_nbmsg']);
		$gd_nbpins				=	intval($_REQUEST['gd_nbpins']);
		
		$_REQUEST['action'] 	=	"editgrade";
	}
	
	//			=	"editgrade";		
}

// #### FORMULAIRE D'EDITION ####
if($_REQUEST['action'] == "editgrade")
{
	$tpl->box['afferror']		=		NULLSTR;
	$id							=		intval($_REQUEST['id']);
	
	if(strlen($error) > 0)
	{
		$tpl->box['afferror']	=		$tpl->gettemplate("adm_grades","afferror");
		$tpl->box['admcontent'] = 	$tpl->gettemplate("adm_grades","edit_grade");
	}	
	elseif($id > 0)
	{
		$_FORUMCFG 				= 	getconfig();		
		$All_Grade 				= 	unserialize($_FORUMCFG['grades']);
		
		$gd_nom					=	$All_Grade[$id][0];	
		$gd_nbmsg				=	$All_Grade[$id][1];
		$gd_nbpins				=	$All_Grade[$id][2];

		$tpl->box['admcontent'] 	= 	$tpl->gettemplate("adm_grades","edit_grade");
	}
	else
		$_REQUEST['action'] = NULLSTR;
}

// #### SUPPRESSION D'UN GRADE ####
if($_REQUEST['action'] == "confdelgrade")
{
	$id							=	intval($_REQUEST['id']);
	
	if($id > 0)
	{
		$_FORUMCFG 				= 	getconfig();		
		$All_Grade 				= 	unserialize($_FORUMCFG['grades']);
		
		for($i = $id; $i < count($All_Grade)+1; $i++)
			$All_Grade[$i]		=	$All_Grade[$i+1];
				
		array_pop($All_Grade);
		$Tabl_to_save 			= 	serialize($All_Grade);
		$query					=	$sql->query("UPDATE "._PRE_."config SET valeur = '%s' WHERE options = 'grades'", $Tabl_to_save)->execute();

		$_REQUEST['action'] = NULLSTR;
	}
}

if($_REQUEST['action'] == "delgrade")
{
	$id							=	intval($_REQUEST['id']);
	$tpl->box['admcontent'] 	= 	$tpl->gettemplate("adm_grades","formdelgrade");
}

// #### ENREGISTREMENT UTILISE-T-ON LES GRADES ####
if($_REQUEST['action'] == "usegrade")
{
	if($_REQUEST['use_grades'] == "Y")
		$query 					= $sql->query("UPDATE "._PRE_."config SET valeur='Y' WHERE options = 'use_grades'")->execute();
	else
		$query 					= $sql->query("UPDATE "._PRE_."config SET valeur='N' WHERE options = 'use_grades'")->execute();
		
	$_REQUEST['action'] = NULLSTR;
}

// #### ENREGISTREMENT NOUVEAU GRADE ####
if($_REQUEST['action'] == "save")
{
	
	$testchain				=	preg_replace("/([\s]{1,})/","",$_POST['sujet']);
		if(strlen($testchain)==0 || strlen($testchain)>35)
			$error 			= 	$tpl->attlang("badname");
	
	if(intval($_REQUEST['gd_nbmsg'])<0)
		$error 				= 	$tpl->attlang("badmsg");

	if(intval($_REQUEST['gd_nbpins'])<1)
		$error 				= 	$tpl->attlang("badpins");
	
	if(strlen($error) == 0)
	{		
		$_FORUMCFG 			= 	getconfig();

		$To_Insert			=	array();
		$To_Insert[0]		=	getrecupforform($_REQUEST['gd_nom'], true);
		$To_Insert[1]		=	intval($_REQUEST['gd_nbmsg']);
		$To_Insert[2]		=	intval($_REQUEST['gd_nbpins']);

		if($To_Insert[2] > 20)
			$To_Insert[2]		=	20;
			
		$All_Grade 			= 	unserialize($_FORUMCFG['grades']);
		$nb_grades 			= 	count($All_Grade);
		
		if($nb_grades > 0 && is_array($All_Grade))
			$All_Grade		=	insert_grade($All_Grade, $To_Insert);
		else
			$All_Grade[1]	=	$To_Insert;
		
		$Tabl_to_save 		= 	serialize($All_Grade);
		
		$query				=	$sql->query("UPDATE "._PRE_."config SET valeur = '%s' WHERE options = 'grades'", $Tabl_to_save)->execute();
		
		unset($_REQUEST['gd_nom'], $_REQUEST['gd_nbmsg'], $_REQUEST['gd_nbpins']);
	}
	else
	{
		$gd_nom				=	getrecupforform($_REQUEST['gd_nom']);
		$gd_nbmsg			=	intval($_REQUEST['gd_nbmsg']);
		$gd_nbpins			=	intval($_REQUEST['gd_nbpins']);
	}
	$_REQUEST['action'] = NULLSTR;
}

// #### ACCUEIL GRADES ####
if(empty($_REQUEST['action']))
{
	$tpl->box['ligne_grades'] 			= 	"";
	$All_Grade  						= 	array();
	$Grade								=	array();
	$Check_Use_Grade					=	"";
	$key								=	0;
	
	$_FORUMCFG 							= 	getconfig();
	
	if($_FORUMCFG['use_grades'] == "Y")
		$Check_Use_Grade				=	" CHECKED";
		
	$All_Grade 							= 	unserialize($_FORUMCFG['grades']);
	
	if(strlen($error)>0)
		$tpl->box['afferror']				=	$tpl->gettemplate("adm_grades","afferror");
	else
		$tpl->box['afferror']				= 	"";
	
	if(count($All_Grade)>0 && is_array($All_Grade))
	{
		foreach($All_Grade as $key => $Grade)
		{
			$tpl->box['nbpins']			=	"";
			
			for($i = 0; $i < $Grade[2]; $i++)
				$tpl->box['nbpins'] 		.= 	trim($tpl->gettemplate("adm_grades","img_grades"));
			
			$tpl->box['ligne_grades'] 	.=	$tpl->gettemplate("adm_grades","ligne_grades");
		}
	}
	
	$tpl->box['admcontent'] 				= 	$tpl->gettemplate("adm_grades","list_grades");
}

$cache.=$tpl->gettemplate("adm_grades","content");
require("bas.php");
