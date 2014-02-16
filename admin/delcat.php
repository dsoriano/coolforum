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
getlangage("adm_delcat");

if($_REQUEST['action']=="del")
{
	// ##### récupération de la position de la catégorie #####
	$query=$sql->query("SELECT catorder FROM ".$_PRE."categorie WHERE catid=".$_POST['catid']);
	list($CatOrder)=mysql_fetch_array($query);
	
	// ##### suppression de tous les forums et messages #####
	$fortodel=$sql->query("SELECT forumid,forumtitle FROM ".$_PRE."forums WHERE forumcat=".$_POST['catid']);
	$nb=mysql_num_rows($fortodel);
	
	$tpl->box['delforumresult']="";
	
	if($nb==0)
		$tpl->box['delforumresult']=$tpl->attlang("noforumtodel");
	else
	{
		while(list($ForumID,$ForumTitle)=mysql_fetch_array($fortodel))
		{
			// ##### table CF_forums #####
			$query=$sql->query("DELETE FROM ".$_PRE."forums WHERE forumid='$ForumID'");
			if($query)
				$tpl->box['table1']=$tpl->attlang("tblforumok");
			else
				$tpl->box['table1']=$tpl->attlang("tblforumnok");
		
			// ##### table CF_topics #####
			$query=$sql->query("DELETE FROM ".$_PRE."topics WHERE idforum='$ForumID'");
			if($query)
				$tpl->box['table2']=$tpl->attlang("tbltopicsok");
			else
				$tpl->box['table2']=$tpl->attlang("tbltopicsnok");
		
			// ##### table CF_posts #####	
			$query=$sql->query("DELETE FROM ".$_PRE."posts WHERE idforum='$ForumID'");
			if($query)
				$tpl->box['table3']=$tpl->attlang("tblpostsok");
			else
				$tpl->box['table3']=$tpl->attlang("tblpostsnok");
		
			// ##### table CF_groups_perm #####	
			$query=$sql->query("DELETE FROM ".$_PRE."groups_perm WHERE id_forum='$ForumID'");
			if($query)
				$tpl->box['table4']=$tpl->attlang("groups_permok");
			else
				$tpl->box['table4']=$tpl->attlang("groups_permnok");
			$query=$sql->query("OPTIMIZE TABLE ".$_PRE."groups_perm");

				
			// ##### table CF_moderateur #####	
			$query=$sql->query("DELETE FROM ".$_PRE."moderateur WHERE forumident='$ForumID'");
			if($query)
				$tpl->box['table5']=$tpl->attlang("tblmodook");
			else
				$tpl->box['table5']=$tpl->attlang("tblmodonok");
				
			$tpl->box['delforumresult'].=$tpl->gettemplate("adm_delcat","delforum");
		}
	
		$query=$sql->query("OPTIMIZE TABLE ".$_PRE."forums");	
		$query=$sql->query("OPTIMIZE TABLE ".$_PRE."topics");
		$query=$sql->query("OPTIMIZE TABLE ".$_PRE."posts");
		$query=$sql->query("OPTIMIZE TABLE ".$_PRE."forumperm");
		$query=$sql->query("OPTIMIZE TABLE ".$_PRE."moderateur");
		
	}
	
	// ##### suppression de la catégorie #####
	$query=$sql->query("DELETE FROM ".$_PRE."categorie WHERE catid=".$_POST['catid']);
	if($query)
		$tpl->box['delcatresult'] = $tpl->attlang("cattodel");
	else
		$tpl->box['delcatresult'] = $tpl->attlang("nocatfound");
	
	// ##### réorganisation de l'ordre des catégories #####
	$query=$sql->query("UPDATE ".$_PRE."categorie SET catorder=catorder-1 WHERE catorder>'$CatOrder'");

	updatenbtopics();
	updatenbposts();
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_delcat","actiondel");
}

if($_REQUEST['action']=="avert")
{
	$sqlforums = $sql->query("SELECT * FROM ".$_PRE."categorie WHERE catid=".$_GET['catid']);
	$MyForum=mysql_fetch_array($sqlforums);
	$tpl->box['admcontent']=$tpl->gettemplate("adm_delcat","avertdel");
}

if(empty($_REQUEST['action']))
{
	$query = $sql->query("SELECT * FROM ".$_PRE."categorie ORDER BY catorder");
	$nb=mysql_num_rows($query);
	
	$tpl->box['catforum']="";
	if ($nb==0)
		$tpl->box['catforum'].=$tpl->gettemplate("adm_delcat","nocatfound");
	else
	{
		while($Cats=mysql_fetch_array($query))
		{
			$Cats['cattitle']=getformatrecup($Cats['cattitle']);
							
			$tpl->box['catforum'].=$tpl->gettemplate("adm_delcat","lignecat");
			//$tpl->box['catforum'].=$forumlist;
		}
	}

	$tpl->box['admcontent']=$tpl->gettemplate("adm_delcat","forumlist");
}

$cache.=$tpl->gettemplate("adm_delcat","content");
require("bas.php");
