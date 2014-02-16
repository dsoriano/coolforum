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
getlangage("adm_supskin");


if($_REQUEST['action']=="sup")
{
	$query=$sql->query("DELETE FROM ".$_PRE."skins WHERE id=".$_GET['id']);
	$query=$sql->query("OPTIMIZE TABLE ".$_PRE."skins");
	$query=$sql->query("UPDATE ".$_PRE."user SET skin='".$_FORUMCFG['defaultskin']."' WHERE skin=".$_GET['id']);
	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	$query=$sql->query("SELECT id,valeur FROM ".$_PRE."skins WHERE propriete='skinname' AND id<>".$_FORUMCFG['defaultskin']." ORDER BY id");
	$nb=mysql_num_rows($query);
	
	$tpl->box['ligneskin']="";
	
	if($nb==0)
		$tpl->box['ligneskin']=$tpl->gettemplate("adm_supskin","noskin");
	else
	{
		while($Skin=mysql_fetch_array($query))
		{
			$Skin['valeur']=getformatrecup($Skin['valeur']);
			$tpl->box['ligneskin'].=$tpl->gettemplate("adm_supskin","ligneskin");
		}
	}
	$tpl->box['admcontent']=$tpl->gettemplate("adm_supskin","tableskin");
}

$cache.=$tpl->gettemplate("adm_supskin","content");
require("bas.php");
