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
getlangage("adm_delforum");

if($_REQUEST['action']=="del")
{
	// ##### Récupération cat and order du forum #####
	$query = $sql->query("SELECT forumcat,forumorder FROM "._PRE_."forums WHERE forumid=%d", $_POST['forumid'])->execute();
	list($forumcat,$forumorder) = $query->fetch_array();
	
	
	// ##### table CF_forums #####
	$query=$sql->query("DELETE FROM "._PRE_."forums WHERE forumid=%d", $_POST['forumid'])->execute();
	if($query)
		$tpl->box['table1']=$tpl->attlang("tblforumok");
	else
		$tpl->box['table1']=$tpl->attlang("tblforumnok");
	$query=$sql->query("OPTIMIZE TABLE "._PRE_."forums");

	// ##### table CF_topics #####
	$query=$sql->query("DELETE FROM "._PRE_."topics WHERE idforum=%d", $_POST['forumid'])->execute();
	if($query)
		$tpl->box['table2']=$tpl->attlang("tbltopicsok");
	else
		$tpl->box['table2']=$tpl->attlang("tbltopicsnok");
	$query=$sql->query("OPTIMIZE TABLE "._PRE_."topics")->execute();

	// ##### table CF_posts #####	
	$query=$sql->query("DELETE FROM "._PRE_."posts WHERE idforum=%d", $_POST['forumid'])->execute();
	if($query)
		$tpl->box['table3']=$tpl->attlang("tblpostsok");
	else
		$tpl->box['table3']=$tpl->attlang("tblpostsnok");
	$query=$sql->query("OPTIMIZE TABLE "._PRE_."posts")->execute();

	// ##### table CF_groups_perm #####	
	$query=$sql->query("DELETE FROM "._PRE_."groups_perm WHERE id_forum=%d", $_POST['forumid'])->execute();
	if($query)
		$tpl->box['table4']=$tpl->attlang("groups_permok");
	else
		$tpl->box['table4']=$tpl->attlang("groups_permnok");
	$query=$sql->query("OPTIMIZE TABLE "._PRE_."groups_perm")->execute();

	// ##### table CF_moderateur #####	
	$query=$sql->query("DELETE FROM "._PRE_."moderateur WHERE forumident=%d", $_POST['forumid'])->execute();
	if($query)
		$tpl->box['table5']=$tpl->attlang("tblmodook");
	else
		$tpl->box['table5']=$tpl->attlang("tblmodonok");
	$query=$sql->query("OPTIMIZE TABLE "._PRE_."moderateur")->execute();

	// ##### Mise à jour de l'ordre des forums #####
	$query = $sql->query("UPDATE "._PRE_."forums SET forumorder = forumorder-1 WHERE forumcat=%d AND forumorder>%d", array($forumcat, $forumorder))->execute();
	

	// ##### Mise à jour des stats #####
	updatenbtopics();
	updatenbposts();
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_delforum","actiondel");
}

if($_REQUEST['action']=="avert")
{
	$sqlforums = $sql->query("SELECT * FROM "._PRE_."forums WHERE forumid=%d", $_GET['forumid'])->execute();
	$MyForum=$sqlforums->fetch_array();
	$tpl->box['admcontent']=$tpl->gettemplate("adm_delforum","avertdel");
}

if(empty($_REQUEST['action']))
{
	$query = $sql->query("SELECT * FROM "._PRE_."categorie ORDER BY catorder")->execute();
	$nb=$query->num_rows();
	
	$tpl->box['catforum']="";
	if ($nb==0)
		$tpl->box['catforum'].=$tpl->gettemplate("adm_delforum","nocatfound");
	else
	{
		$TabForum=array();
		
		$sqlforums = $sql->query("SELECT * FROM "._PRE_."forums ORDER BY forumcat,forumorder")->execute();
		$nbforums=$sqlforums->num_rows();
		
		if($nbforums>0)
		{
			while($TabForum[]=$sqlforums->fetch_array());
	
			while($Cats=$query->fetch_array())
			{
				$forumlist="";
	
				for($cpt=0;$cpt<count($TabForum);$cpt++)
					if($TabForum[$cpt]['forumcat']==$Cats['catid'])
					{
						//récupération des infos
						$MyForum = $TabForum[$cpt];
						
						$forumlist .= $tpl->gettemplate("adm_delforum","ligneforum");
					}		
				
				if(strlen($forumlist)>0)
				{
					$Cats['cattitle']=getformatrecup($Cats['cattitle']);
									
					$tpl->box['catforum'].=$tpl->gettemplate("adm_delforum","lignecat");
					$tpl->box['catforum'].=$forumlist;
				}
			}
		}
		else
			$tpl->box['catforum'].=$tpl->gettemplate("adm_delforum","nocatfound");
	}	
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_delforum","forumlist");
}

$cache.=$tpl->gettemplate("adm_delforum","content");
require("bas.php");
