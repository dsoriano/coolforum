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
getlangage("adm_options_gen");

if($_REQUEST['action']=="save")
{
	$_POST['configz']['indexnews'] = preg_replace("/<script.*?>.*?<\/script>/si","",$_POST['configz']['indexnews']);
	$_POST['configz']['catseparate'] = preg_replace("/<script.*?>.*?<\/script>/si","",$_POST['configz']['catseparate']);

	if(!isset($_POST['configz']['conn_accueil']) || (isset($_POST['configz']['conn_accueil']) && $_POST['configz']['conn_accueil'] != "Y"))	
		$_POST['configz']['conn_accueil'] 	= "N";
	if(!isset($_POST['configz']['conn_forum']) || (isset($_POST['configz']['conn_forum']) && $_POST['configz']['conn_forum'] != "Y"))
		$_POST['configz']['conn_forum'] 	= "N";
	if(!isset($_POST['configz']['conn_topic']) || (isset($_POST['configz']['conn_topic']) && $_POST['configz']['conn_topic'] != "Y"))
		$_POST['configz']['conn_topic'] 	= "N";
		
	for($i=0;$i<count($_POST['configz']);$i++)
	{
		$valeur=each($_POST['configz']);
		$valeur['value'] = getformathtml($valeur['value']);

		$query=$sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='%s'", array($valeur['value'], $valeur['key']))->execute();
	}
	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	$configuration=getconfig();
	$IsSelected = array(	NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR,
							NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	$timezn1 = array();
	$timezn2 = array();
	
	
	$configuration['indexnews']=getformatrecup($configuration['indexnews']);
	$configuration['catseparate']=getformatrecup($configuration['catseparate']);
	
	if($configuration['viewmsgedit']=="Y")
		$IsSelected[1]=" SELECTED";
	else
		$IsSelected[2]=" SELECTED";

	if($configuration['bbcodeinsign']=="Y")
		$IsSelected[3]=" SELECTED";
	else
		$IsSelected[4]=" SELECTED";	

	if($configuration['smileinsign']=="Y")
		$IsSelected[5]=" SELECTED";
	else
		$IsSelected[6]=" SELECTED";

	if($configuration['canpostmsgcache']=="Y")
		$IsSelected[7]=" SELECTED";
	else
		$IsSelected[8]=" SELECTED";

	if($configuration['forumjump']=="Y")
		$IsSelected[9]=" SELECTED";
	else
		$IsSelected[10]=" SELECTED";

	if($configuration['repflash']=="Y")
		$IsSelected[13]=" SELECTED";
	else
		$IsSelected[14]=" SELECTED";

	if($configuration['conn_accueil'] == "Y")		$IsChecked[0] = " CHECKED";
	else											$IsChecked[0] = "";

	if($configuration['conn_forum'] == "Y")			$IsChecked[1] = " CHECKED";
	else											$IsChecked[1] = "";
	
	if($configuration['conn_topic'] == "Y")			$IsChecked[2] = " CHECKED";
	else											$IsChecked[2] = "";

	//**** affichage du skin par défaut ****
	$tpl->box['skinlist']	=	"";	
	$query			=	$sql->query("SELECT * FROM "._PRE_."skins WHERE propriete='skinname'")->execute();
	
	while($j=$query->fetch_array())
	{
		$selected	=	"";
		if($configuration['defaultskin']==$j['id'])	$selected=" SELECTED";
			
		$tpl->box['skinlist'].=$tpl->gettemplate("adm_options_gen","skinlist");
	}
	
	$cache.=$tpl->gettemplate("adm_options_gen","optionslist");	
}

require("bas.php");
