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
getlangage("adm_addskin");

$tpl->box['titlesection']=$tpl->attlang("titlemodifskin");

if(isset($_REQUEST['act']))
	$_REQUEST['action'] = $_REQUEST['act'];

if($_REQUEST['action']=="saveskin")
{
	$error="";
	
	$_POST['skins']['skinname']=getformatmsg($_POST['skins']['skinname']);
	
	$query=$sql->query("SELECT * FROM "._PRE_."skins WHERE propriete='skinname' AND valeur='%s' AND id<>%d", array($_POST['skins']['skinname'], $_POST['Id']))->execute();
	$nb=$query->num_rows();
	
	if($nb>0)
		$error=$tpl->attlang("errorname");

	if(strlen($_POST['skins']['skinname'])==0)
		$error=$tpl->attlang("errornoname");
				
	if(strlen($_POST['skins']['repimg'])==0)
		$error=$tpl->attlang("errornorepimg");
	
	if(strlen($_POST['skins']['reptpl'])==0)
		$error=$tpl->attlang("errornoreptpl");
		
	if(strlen($error)==0)
	{
		for($i=0;$i<count($_POST['skins']);$i++)
		{
			$valeur=each($_POST['skins']);
			$query=$sql->query("UPDATE "._PRE_."skins SET valeur='%s' WHERE id=%d AND propriete='%s'", array($valeur['value'], $_POST['Id'], $valeur['key']))->execute();
		}
		$tpl->box['admcontent']=$tpl->gettemplate("adm_addskin","saveok");		
	}
	else
	{
		$skins = $_POST['skins'];
		$Id=$_POST['Id'];
		$tpl->box['errorbox']=$tpl->gettemplate("adm_addskin","errorbox");
		
		$_REQUEST['action']="modify";
	}
}

if($_REQUEST['action']=="modify")
{
	$tpl->box['errorbox'] = NULLSTR;
	if(strlen($error)==0)
	{
		$Id = intval($_GET['id']);
		$skins = array();
		
		$query=$sql->query("SELECT * FROM "._PRE_."skins WHERE id=%d", $Id)->execute();
		
		while($j=$query->fetch_array())
			addToArray($skins,$j['propriete'],$j['valeur']);
	}

	$skins['skinname']=getformatrecup($skins['skinname']);
	$smallfont=array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	$middlefont=array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	$bigfont=array(NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	$fonts=array("arial"=>NULLSTR, "courier"=>NULLSTR, "garamond"=>NULLSTR, "helvetica"=>NULLSTR, "tahoma"=>NULLSTR, "times"=>NULLSTR, "verdana"=>NULLSTR);
	$degrad=array(NULLSTR, NULLSTR, NULLSTR);
		
	$smallfont[$skins['smallfont']]=" SELECTED";
	$middlefont[$skins['middlefont']]=" SELECTED";
	$bigfont[$skins['bigfont']]=" SELECTED";
	$fonts[$skins['font']]=" SELECTED";
	
	if($skins['affdegrad']=="Y")	$degrad[1] = " SELECTED";
	else				$degrad[2] = " SELECTED";
	
	$tpl->box['targetform']="modifskin.php";

	$tpl->box['groupscols'] = "";
	$query = $sql->query("SELECT id_group,Nom_group FROM "._PRE_."groups ORDER BY id_group")->execute();
	while(list($id_group,$Nom_group)=$query->fetch_array())
	{
		if(!empty($skins['grp'.$id_group]))
			$valuecolor		=	$skins['grp'.$id_group];
		else
			$valuecolor		=	NULLSTR;
		$Nom_group		=	getformatrecup($Nom_group);
		$tpl->box['groupscols']	.=	$tpl->gettemplate("adm_addskin","groupscols");		
	}			
	$tpl->box['admcontent']=$tpl->gettemplate("adm_addskin","formulaire");
}

if(empty($_REQUEST['action']))
{
	$query=$sql->query("SELECT id,valeur FROM "._PRE_."skins WHERE propriete='skinname' ORDER BY id")->execute();
	$tpl->box['ligneskin']="";
	
	while($Skin=$query->fetch_array())
	{
		$Skin['valeur']=getformatrecup($Skin['valeur']);
		$tpl->box['ligneskin'].=$tpl->gettemplate("adm_addskin","ligneskin");
	}
	$tpl->box['admcontent']=$tpl->gettemplate("adm_addskin","tableskin");	
}

$cache.=$tpl->gettemplate("adm_addskin","content");
require("bas.php");
