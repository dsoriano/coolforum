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


require("secret/connect.php"); 
require("admin/functions.php");


// #### Initialisation des variables #### //////////////////////////////////////
$tpl->box['affforumcontent']		=	NULLSTR;
$tpl->box['boxconnected']			=	NULLSTR;

$TabForum							=	array();
$TabModos							=	array();

$_GET['catid']						=	intval($_GET['catid']);
////////////////////////////////////////////////////////////////////////////////






// #### Définition du lieu #### ///////////////////////////////////////////////
$SessLieu	=	'ACC';
$SessForum	=	0;
$SessTopic	=	0;
////////////////////////////////////////////////////////////////////////////////






require("entete.php");
getlangage("viewcat");






// #### Extraction du cookie #### //////////////////////////////////////////////
if(isset($_COOKIE['listeforum_coolforum']))
      $zecook			=		cookdecode($_COOKIE['listeforum_coolforum']);
////////////////////////////////////////////////////////////////////////////////






// #### Infos sur catégorie #### ///////////////////////////////////////////////
$query 					= 		$sql->query("SELECT * FROM ".$_PRE."categorie WHERE catid=".$_GET['catid']);
$nb						=		mysql_num_rows($query);

if($nb==0)						geterror("novalidlink");
else		$CatInfo	=		mysql_fetch_array($query);
////////////////////////////////////////////////////////////////////////////////






// #### Navigation #### ////////////////////////////////////////////////////////
$CatInfo['cattitle']	=		getformatrecup($CatInfo['cattitle']);
$tpl->treenavs			=		$tpl->gettemplate("treenav","treeviewcat");
$cache				   .=		$tpl->gettemplate("treenav","hierarchy");
////////////////////////////////////////////////////////////////////////////////






// #### Affichage des forums #### //////////////////////////////////////////////
$sqlforums 				= 	$sql->query("SELECT * FROM ".$_PRE."forums WHERE forumcat='".$CatInfo['catid']."' ORDER BY forumorder");
$nbforums				=	mysql_num_rows($sqlforums);
	
if($nbforums>0)
	while($TabForum[]	=	mysql_fetch_array($sqlforums));

$sqlmodo 				= 	$sql->query("SELECT * FROM ".$_PRE."moderateur ORDER BY forumident,modoorder");
$nbmodos				=	mysql_num_rows($sqlmodo);
	
if($nbmodos>0)
	while($TabModos[]	=	mysql_fetch_array($sqlmodo));

$tpl->box['forumlist']	=	affforumlist($CatInfo['catid']);

if(strlen($tpl->box['forumlist'])>0)
{
	if(strlen($CatInfo['catcoment'])>0)
	{
		$CatInfo['catcoment']		=	getformatrecup($CatInfo['catcoment']);
		$tpl->box['catcoment']		=	$tpl->gettemplate("viewcat","catcoment");
	}			
			
	$tpl->box['affforumcontent']   .=	$tpl->gettemplate("viewcat","affcategorie");
	$tpl->box['affforumcontent']   .=	$tpl->box['forumlist'];
}

$cache .= $tpl->gettemplate("viewcat","accueilgeneral");
////////////////////////////////////////////////////////////////////////////////






$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);


