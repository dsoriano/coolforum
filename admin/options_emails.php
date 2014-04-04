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
getlangage("adm_options_emails");

if($_REQUEST['action']=="save")
{
	if($_POST['configz']['usemails']=="N")
	{
		if($_POST['configz']['confirmparmail']==3)
		$_POST['configz']['confirmparmail'] = 0;
		$_POST['configz']['mailnotify'] = "N";
	}
	
	for($i=0;$i<count($_POST['configz']);$i++)
	{
		$valeur=each($_POST['configz']);
		$query=$sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='%s'", array($valeur['value'], $valeur['key']))->execute();
	}
	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	$configuration=getconfig();
	$IsSelected = array( 	NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, 
							NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	
	if($configuration['usemails']=="Y")
		$IsSelected[1]=" SELECTED";
	else
		$IsSelected[2]=" SELECTED";

	if($configuration['confirmparmail']==3)
		$IsSelected[3]=" SELECTED";
	elseif($configuration['confirmparmail']==2)
		$IsSelected[4]=" SELECTED";
	elseif($configuration['confirmparmail']==1)
		$IsSelected[5]=" SELECTED";
	else
		$IsSelected[6]=" SELECTED";

	if($configuration['mailnotify']=="Y")
		$IsSelected[7]=" SELECTED";
	else
		$IsSelected[8]=" SELECTED";
	
	if($configuration['sendpmbymail']=="Y")
		$IsSelected[9]=" SELECTED";
	else
		$IsSelected[10]=" SELECTED";

	if($configuration['mailfunction']=="normal")
		$IsSelected[11]=" SELECTED";
	elseif($configuration['mailfunction']=="online")
		$IsSelected[12]=" SELECTED";		
	elseif($configuration['mailfunction']=="nexen")
		$IsSelected[13]=" SELECTED";
	
	$cache.=$tpl->gettemplate("adm_options_emails","optionslist");	
}

require("bas.php");
