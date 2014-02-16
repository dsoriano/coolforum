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
getlangage("adm_options_config");

if($_REQUEST['action']=="save")
{
	for($i=0;$i<count($_POST['configz']);$i++)
	{
		$valeur=each($_POST['configz']);
		
		if($valeur['key'] != "forumname" && $valeur['key'] != "sitename")
			$valeur['value'] = getformathtml($valeur['value']);
		else
			$valeur['value'] = getformatmsg($valeur['value']);

		$query=$sql->query("UPDATE ".$_PRE."config SET valeur='".$valeur['value']."' WHERE options='".$valeur['key']."'");
	}
	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	$configuration = getconfig();
	$IsSelected = array();
	$timezn1 = array();
	$timezn2 = array();
	
	
	$configuration['forumname']=getformatrecup($configuration['forumname']);
	$configuration['sitename']=getformatrecup($configuration['sitename']);
	$configuration['closeregmsg']=getformatrecup($configuration['closeregmsg']);
	$configuration['closeforummsg']=getformatrecup($configuration['closeforummsg']);
	
	
	if($configuration['openforum']=="Y")
	{
		$IsSelected[1]=" SELECTED";
		$IsSelected[2]=NULLSTR;
	}
	else
	{
		$IsSelected[1]=NULLSTR;
		$IsSelected[2]=" SELECTED";
	}

	if($configuration['openinscriptions']=="Y")
	{
		$IsSelected[3]=" SELECTED";
		$IsSelected[4]=NULLSTR;
	}
	else
	{
		$IsSelected[3]=NULLSTR;
		$IsSelected[4]=" SELECTED";
	}	

	if($configuration['mustbeidentify']=="Y")
	{
		$IsSelected[5]=" SELECTED";
		$IsSelected[6]=NULLSTR;
	}
	else
	{
		$IsSelected[5]=NULLSTR;
		$IsSelected[6]=" SELECTED";
	}
	
	for($i = 0; $i < 25; $i++)
		if($i == $configuration['defaulttimezone']+12)
			$timezn2[$i] = " SELECTED";
		else
			$timezn2[$i] = NULLSTR;
	//$timezn2[$configuration['defaulttimezone']+12]=" SELECTED";
	
	$cache.=$tpl->gettemplate("adm_options_config","optionslist");	
}

require("bas.php");
