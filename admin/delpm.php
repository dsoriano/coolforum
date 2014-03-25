<?php
//*********************************************************************************
//*                                                                               *
//*                  CoolForum v.0.8.5 Beta : Forum de discussion                   *
//*              Copyright �2001-2014 SORIANO Denis alias Cool Coyote             *
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
//*       Forum Cr�� par SORIANO Denis (Cool Coyote)                              *
//*       contact : coyote@coolcoyote.net                                         *
//*       site web et t�l�chargement : http://www.coolforum.net                   *
//*                                                                               *
//*********************************************************************************

require("entete.php"); 
getlangage("adm_delpm");

if($_REQUEST['action'] == "delpm")
{
	$Old		=	intval($_POST['old']);
	$NbPm		=	intval($_POST['nbpm']);
	$LastVisit	=	intval($_POST['lastvisit']);
	
	$TabQuery	=	array();
	$Now		=	time();
	
	if($Old>0)
		$TabQuery[]="date<'".($Now-($Old*86400))."'";
	
	if($LastVisit>0)
	{
		$query = $sql->query("SELECT userid FROM "._PRE_."user WHERE lastvisit<'".($Now-($LastVisit*86400))."'");
		$nb = mysql_num_rows($query);
		
		if($nb>0)
		{
			$tmp=array();
			while($j=mysql_fetch_array($query))
				$tmp[]=$j['userid'];
			$TabQuery[]="iddest IN (".implode(",",$tmp).")";
		}
		else
			$TabQuery[]="iddest='0'";
	}
	
	if($NbPm>0)
	{
		$query = $sql->query("SELECT userid FROM "._PRE_."user WHERE nbpmtot>'".$NbPm."'");
		$nb = mysql_num_rows($query);
		
		if($nb>0)
		{
			$tmp=array();
			while($j=mysql_fetch_array($query))
				$tmp[]=$j['userid'];
			$TabQuery[]="iddest IN (".implode(",",$tmp).")";
		}
		else
			$TabQuery[]="iddest='0'";
	}
	
	if($_POST['statut']=="lu")		$TabQuery[]="vu='1'";
	elseif($_POST['statut']=="nonlu")	$TabQuery[]="vu='0'";
	
	if(count($TabQuery)==0)			$Where="iddest='0'"; // s�curit� pour �viter de s�lectionner tous les messages
	else					$Where=implode(" AND ",$TabQuery);
	
	if($_POST['confirm']=="Y")
	{
		$MbList=array();
		$TotalPm=array();
		$TotalVu=array();
		
		$query = $sql->query("SELECT iddest FROM "._PRE_."privatemsg WHERE ".$Where." GROUP BY iddest");
		while($j=mysql_fetch_array($query))
			$MbList[]=$j['iddest'];
		
		$query = $sql->query("DELETE FROM "._PRE_."privatemsg WHERE ".$Where);
		$total = mysql_affected_rows();
		
		$query = $sql->query("OPTIMIZE TABLE "._PRE_."privatemsg");
		
		$query = $sql->query("SELECT iddest,vu FROM "._PRE_."privatemsg");
		while($j=mysql_fetch_array($query))
		{
			$TotalPm[$j['iddest']]++;
			if($j['vu']=="0")
				$TotalVu[$j['iddest']]++;
		}
		
		for($i=0;$i<count($MbList);$i++)
			$query = $sql->query("UPDATE "._PRE_."user SET nbpmvu='".$TotalVu[$MbList[$i]]."',nbpmtot='".$TotalPm[$MbList[$i]]."' WHERE userid='".$MbList[$i]."'");
		
		$tpl->box['admcontent'] = $tpl->gettemplate("adm_delpm","delok");
	}
	else
	{
		$query = $sql->query("SELECT COUNT(*) AS total FROM "._PRE_."privatemsg WHERE ".$Where);
		list($total) = mysql_fetch_array($query);
		
		$tpl->box['admcontent'] = $tpl->gettemplate("adm_delpm","confirm");
	}
	
}

if(empty($_REQUEST['action']))
{
	$query=$sql->query("SELECT COUNT(*) AS nbpmlu FROM "._PRE_."privatemsg WHERE vu=1");
	list($nbpm)=mysql_fetch_array($query);
	
	$query=$sql->query("SELECT COUNT(*) AS nbpmnonlu FROM "._PRE_."privatemsg WHERE vu=0");
	list($nbpmnonlu)=mysql_fetch_array($query);
	
	$nbtotpm=$nbpm+$nbpmnonlu;
	
	$tpl->box['admcontent'] = $tpl->gettemplate("adm_delpm","accueil");
}

$cache.=$tpl->gettemplate("adm_delpm","content");
require("bas.php");

