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

$_GET['post']		=	intval($_GET['post']);
$_GET['forumid']	=	intval($_GET['forumid']);

$query				=	$sql->query("SELECT parent FROM ".$_PRE."posts WHERE idpost=".$_GET['post']);
list($parent)		=	mysql_fetch_array($query);

// #### définition du lieu ###
$SessLieu				=	'TOP';
$SessForum				=	$_GET['forumid'];
$SessTopic				=	$parent;
//////////////////////////////

require("entete.php");

getlangage("viewip");

$ismodo			=	getismodo($_GET['forumid'],$_USER['userid']);

if(($ismodo && $_MODORIGHTS[0])|| $_GENERAL[20])
{
	$query			=	$sql->query("SELECT * FROM ".$_PRE."posts WHERE idpost=".$_GET['post']);
	$tmp			=	mysql_fetch_array($query);

	$tpl->box['affip'] 	= 	$tmp['postip'];
	$cache 	               .= 	$tpl->gettemplate("viewip","affip");
	$cache 		       .=	getjsredirect($_SERVER['HTTP_REFERER'],4000);
}
else
	geterror("call_loginbox");

$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
