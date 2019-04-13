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
getlangage("adm_db_optimize");

if($_REQUEST['action']=="optimize")
{
	$tablename=array();
	$DB_Name="";
		
	$query = $sql->query("SHOW TABLES")->execute();
	if($query)
	while($j=$query->fetch_row())
	{
		if(preg_match("|^"._PRE_ . "|i",$j[0] > 0))
			$tablename[]=$j[0];
	}
	
	for($i=0;$i<count($tablename);$i++)
	{
		$query = $sql->query("OPTIMIZE TABLE %s", $tablename[$i])->execute();
		$DB_Name=$tablename[$i];
		if($query)
			$tpl->box['optimizedtble'].=$tpl->gettemplate("adm_db_optimize","opt_ok");
		else
			$tpl->box['optimizedtble'].=$tpl->gettemplate("adm_db_optimize","opt_nok");
	}
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_db_optimize","actionopt");	
	
}

if(empty($_REQUEST['action']))
{
	$tpl->box['admcontent']=$tpl->gettemplate("adm_db_optimize","accueilopt");	
}

$cache.=$tpl->gettemplate("adm_db_optimize","content");
require("bas.php");
