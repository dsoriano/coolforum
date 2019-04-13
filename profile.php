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

require("admin/functions.php");

// #### définition du lieu ###
$SessLieu	=	'PRO';
$SessForum	=	0;
$SessTopic	=	0;
//////////////////////////////

require("entete.php");

getlangage("profile");

$tpl->box['boxconnected'] = NULLSTR;
$tpl->box['notifylink']= NULLSTR;

if($_USER['userstatus'] > 1)
{

	if(!isset($_REQUEST['p']))
		$_REQUEST['p'] = NULLSTR;
		
	switch ($_REQUEST['p'])
	{
		case "profile":
			include("profile_options.php");
			break;
		case "infoperso":
			include("profile_perso.php");
			break;
		case "pm":
			include("profile_pm.php");
			break;
		case "mdp":
			include("profile_mdp.php");
			break;
		case "notify":
			include("profile_notify.php");
			break;
		default:
			include("profile_accueil.php");
			break;
	}
	
}
else
	$tpl->box['profilcontent']=$tpl->gettemplate("profil","idrequired");

if($_FORUMCFG['usemails']=="Y" && $_FORUMCFG['mailnotify']=="Y")
	$tpl->box['notifylink']=$tpl->gettemplate("profil","notifylink");

$cache.=$tpl->gettemplate("profil","profilaccueil");

$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
