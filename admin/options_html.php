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
getlangage("adm_options_html");

if($_REQUEST['action']=="save")
{

	$_POST['configz']['ajouthtml'] 	= 	getformathtml($_POST['configz']['ajouthtml']);
	$_POST['configz']['htmlbas'] 	= 	getformathtml($_POST['configz']['htmlbas']);
	
	for($i=0;$i<count($_POST['configz']);$i++)
	{
		$valeur=each($_POST['configz']);
		$query=$sql->query("UPDATE ".$_PRE."config SET valeur='".$valeur['value']."' WHERE options='".$valeur['key']."'");
	}
	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	$IsSelected = array(	NULLSTR, NULLSTR, NULLSTR);
	$configuration=getconfig();
	
	if($configuration['usepub']=="Y")
		$IsSelected[1]=" SELECTED";
	else
		$IsSelected[2]=" SELECTED";
			
	$cache.=$tpl->gettemplate("adm_options_html","optionslist");	
}

require("bas.php");
