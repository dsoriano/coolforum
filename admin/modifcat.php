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
getlangage("adm_modifcat");

function getplace($cat,$position)
{
	global $Pos,$tpl,$sql,$_PRE;
	
	$query=$sql->query("SELECT * FROM ".$_PRE."categorie ORDER BY catorder");
	
	while($Pos=mysql_fetch_array($query))
	{
		if($cat==$Pos['catid'])
			$tpl->box['orderlist'].=$tpl->gettemplate("adm_modifcat","actualorder");
		else
			$tpl->box['orderlist'].=$tpl->gettemplate("adm_modifcat","setorder");
	}


}

if($_REQUEST['action']=="changepos")
{
	if($_GET['changeto']<$_GET['place'])
	{
		$query=$sql->query("UPDATE ".$_PRE."categorie SET catorder=catorder+1 WHERE catorder>='".$_GET['changeto']."' AND catorder<='".$_GET['place']."'");
		$query=$sql->query("UPDATE ".$_PRE."categorie SET catorder='".$_GET['changeto']."' WHERE catid='".$_GET['cat']."'");
	}
	elseif ($_GET['changeto']>$_GET['place'])
	{
		$query=$sql->query("UPDATE ".$_PRE."categorie SET catorder=catorder-1 WHERE catorder<='".$_GET['changeto']."' AND catorder>='".$_GET['place']."'");
		$query=$sql->query("UPDATE ".$_PRE."categorie SET catorder='".$_GET['changeto']."' WHERE catid='".$_GET['cat']."'");	
	}
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="modify")
{
	$sql=mysql_query("SELECT * FROM ".$_PRE."categorie WHERE catid=".$_GET['id']);	
	$Lescat=mysql_fetch_array($sql);
	
	$Lescat['cattitle']=getformatrecup($Lescat['cattitle']);
	$Lescat['catcoment']=getformatrecup($Lescat['catcoment']);
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_modifcat","modifform");
}

if($_REQUEST['action']=="save")
{
	$nom=getformatmsg($_POST['nom']);
	$coment=getformatmsg($_POST['coment']);
	
	$query=$sql->query("UPDATE ".$_PRE."categorie SET cattitle='$nom',catcoment='$coment' WHERE catid=".$_POST['id']);
	$_REQUEST['action'] = NULLSTR;
}


if(empty($_REQUEST['action']))
{
	$tpl->box['catlist'] = NULLSTR;
	
	$query=$sql->query("SELECT * FROM ".$_PRE."categorie ORDER BY catorder");
	$nb=mysql_num_rows($query);
	
	if ($nb==0)
		$tpl->box['catlist']=$tpl->gettemplate("adm_modifcat","nocatfound");
	else
	{
		while($lescat=mysql_fetch_array($query))
		{
			$tpl->box['orderlist']="";
			getplace($lescat['catid'],$lescat['catorder']);
			$lescat['cattitle']=getformatrecup($lescat['cattitle']);
			$tpl->box['catlist'].=$tpl->gettemplate("adm_modifcat","lignecat");
		}
		echo("</table>");
	}

	$tpl->box['admcontent']=$tpl->gettemplate("adm_modifcat","choosecat");
}

$cache.=$tpl->gettemplate("adm_modifcat","content");
require("bas.php");
