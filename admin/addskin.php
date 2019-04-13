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

$tpl->box['titlesection']=$tpl->attlang("titleaddskin");

if(isset($_REQUEST['act']))
	$_REQUEST['action'] = $_REQUEST['act'];
	
if($_REQUEST['action']=="saveskin")
{
	$error="";
	
	$_POST['skins']['skinname']=getformatmsg($_POST['skins']['skinname']);
	
	$query=$sql->query("SELECT * FROM "._PRE_."skins WHERE propriete='skinname' AND valeur='%s'", $_POST['skins']['skinname'])->execute();
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
		$query=$sql->query("SELECT id FROM "._PRE_."skins GROUP BY id ORDER BY id DESC")->execute();
		list($id)=$query->fetch_array();
		
		$id++;
		
		for($i=0;$i<count($_POST['skins']);$i++)
		{
			$valeur=each($_POST['skins']);
			$query=$sql->query("INSERT INTO "._PRE_."skins (id,propriete,valeur) VALUES (%d,'%s','%s')", array($id,$valeur['key'], $valeur['value']))->execute();
		}
		$tpl->box['admcontent']=$tpl->gettemplate("adm_addskin","saveok");		
	}
	else
	{
		$skins = $_POST['skins'];
		$skins['skinname']=getformatrecup($skins['skinname']);
		$tpl->box['errorbox']=$tpl->gettemplate("adm_addskin","errorbox");
		
		$smallfont=array();
		$middlefont=array();
		$bigfont=array();
		$fonts=array();
		$degrad=array();
		
		$smallfont[$skins['smallfont']]=" SELECTED";
		$middlefont[$skins['middlefont']]=" SELECTED";
		$bigfont[$skins['bigfont']]=" SELECTED";
		$fonts[$skins['font']]=" SELECTED";

		if($skins['affdegrad']=="Y")	$degrad[1] = " SELECTED";
		else				$degrad[2] = " SELECTED";
		
		$_REQUEST['actions'] = NULLSTR;
	}
}

if(empty($_REQUEST['action']))
{
	$tpl->box['targetform']="addskin.php";
	
	$tpl->box['groupscols'] = "";
	$tpl->box['errorbox']=NULLSTR;
	
	$query = $sql->query("SELECT id_group,Nom_group FROM "._PRE_."groups ORDER BY id_group")->execute();
	while(list($id_group,$Nom_group)=$query->fetch_array())
	{
		if(strlen($error)>0)
			$valuecolor	=	$skins['grp'.$id_group];
		else
			$valuecolor	=	"";
		$Nom_group		=	getformatrecup($Nom_group);
		$tpl->box['groupscols']	.=	$tpl->gettemplate("adm_addskin","groupscols");		
	}
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_addskin","formulaire");
}

$cache.=$tpl->gettemplate("adm_addskin","content");
require("bas.php");

