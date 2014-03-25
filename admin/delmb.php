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
getlangage("adm_delmb");

if($_REQUEST['action'] == "delmb")
{
	$UserMsg	=	intval($_POST['usermsg']);
	$RegisterDate	=	intval($_POST['registerdate']);
	$LastVisit	=	intval($_POST['lastvisit']);
	$LastPost	=	intval($_POST['lastpost']);
	
	$TabQuery	=	array();
	$Now		=	time();
	
	if($UserMsg>0)
		$TabQuery[]="usermsg <= '".$UserMsg."'";
	
	if($RegisterDate>0)
		$TabQuery[]="registerdate < '".($Now-($RegisterDate*86400))."'";
	
	if($LastVisit>0)
		$TabQuery[]="lastvisit < '".($Now-($LastVisit*86400))."'";
	
	if($LastPost>0)
		$TabQuery[]="lastpost < '".($Now-($LastPost*86400))."'";
	
	if(count($TabQuery)==0)			$Where="userid='0'"; // sécurité pour éviter de sélectionner tous les membres
	else					$Where=implode(" AND ",$TabQuery);
	
	if($_POST['confirm']=="Y")
	{
		$query = $sql->query("SELECT userid FROM "._PRE_."user WHERE ".$Where);

		$UserIDList=array();
			
		while($j=mysql_fetch_array($query))
			$UserIDList[]=$j['userid'];
			
		$UserIDList = implode(",",$UserIDList);
			
		$query = $sql->query("UPDATE "._PRE_."topics SET idmembre='0' WHERE idmembre IN (".$UserIDList.")");
		$query = $sql->query("UPDATE "._PRE_."posts SET idmembre='0' WHERE idmembre IN (".$UserIDList.")");
		
		$query = $sql->query("DELETE FROM "._PRE_."userplus WHERE idplus IN (".$UserIDList.")");
		
		$query = $sql->query("DELETE FROM "._PRE_."user WHERE ".$Where);
		$total = mysql_affected_rows();
		
		$query = $sql->query("OPTIMIZE TABLE "._PRE_."user");
		$query = $sql->query("OPTIMIZE TABLE "._PRE_."userplus");
		
		updatemembers();
		
		$tpl->box['admcontent'] = $tpl->gettemplate("adm_delmb","delok");
	}
	else
	{
		$query = $sql->query("SELECT login AS username FROM "._PRE_."user WHERE ".$Where);
		$total = mysql_num_rows($query);
		
		if($total>0)
		{
			while($Mb=mysql_fetch_array($query))
			{
				$Mb['username'] = getformatrecup($Mb['username']);
				$tpl->box['listmb'].=$tpl->gettemplate("adm_delmb","lignemb");
			}
			
			$tpl->box['submit']=$tpl->gettemplate("adm_delmb","submit");
		}
		else
			$tpl->box['listmb']=$tpl->gettemplate("adm_delmb","nomb");
		
		$tpl->box['admcontent'] = $tpl->gettemplate("adm_delmb","confirm");
	}
	
}

if(empty($_REQUEST['action']))
{
	$query=$sql->query("SELECT COUNT(*) AS nbtotmb FROM "._PRE_."user");
	list($nbtotmb)=mysql_fetch_array($query);
	
	$tpl->box['admcontent'] = $tpl->gettemplate("adm_delmb","accueil");
}

$cache.=$tpl->gettemplate("adm_delmb","content");
require("bas.php");

