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
getlangage("adm_index");


if($_FORUMCFG['openforum']=="Y")
	$tpl->box['openforum']=$tpl->attlang("open");
else
	$tpl->box['openforum']=$tpl->attlang("close");
	
// #######################################

if($_FORUMCFG['confirmparmail']==3)
	$tpl->box['confirm']=$tpl->attlang("conf_bymail");
elseif($_FORUMCFG['confirmparmail']==1 || $_FORUMCFG['confirmparmail']==2)
	$tpl->box['confirm']=$tpl->attlang("conf_byadmin");
else
	$tpl->box['confirm']=$tpl->attlang("conf_auto");

// #######################################

$tpl->box['lastcookieini']=strftime("%d/%m/%Y %H:%M",$_FORUMCFG['initialise']);

// #######################################

if($_FORUMCFG['forumjump']=="Y")
	$tpl->box['forumjump']=$tpl->attlang("fjenabled");
else
	$tpl->box['forumjump']=$tpl->attlang("fjdisabled");

//--------------------------------------------------------------------------

$nbtotmsg=$_FORUMCFG['statnbtopics']+$_FORUMCFG['statnbposts'];

// #######################################

$query=$sql->query("SELECT COUNT(*) AS nbpmlu FROM "._PRE_."privatemsg WHERE vu=1")->execute();
list($nbpm)=$query->fetch_array();

$query=$sql->query("SELECT COUNT(*) AS nbpmnonlu FROM "._PRE_."privatemsg WHERE vu=0")->execute();
list($nbpmnonlu)=$query->fetch_array();

$nbtotpm=$nbpm+$nbpmnonlu;

//--------------------------------------------------------------------------

$tpl->box['groups_stats']="";
$tpl->box['nbbannis'] = 0;
$tpl->box['nbattente'] = 0;
$nbtotmembers = 0;
		
$query = $sql->query("SELECT COUNT(*) AS nbuser, "._PRE_."user.userstatus, "._PRE_."groups.Nom_group FROM "._PRE_."user LEFT JOIN "._PRE_."groups ON "._PRE_."user.userstatus="._PRE_."groups.id_group GROUP BY userstatus ORDER BY userstatus")->execute();
while($j=$query->fetch_array())
{
	if($j['userstatus'] < 0)
		$tpl->box['nbbannis'] += $j['nbuser'];
	elseif($j['userstatus']==0)
		$tpl->box['nbattente'] = $j['nbuser'];
	else
	{
		$tpl->box['group_name'] = $j['Nom_group'];
		$tpl->box['nb_users']   = $j['nbuser'];
		$tpl->box['groups_stats'] .= $tpl->gettemplate("adm_index","groups_stats");				
	}
	
	$nbtotmembers += $j['nbuser'];
}

$cache.=$tpl->gettemplate("adm_index","accueil");
require("bas.php");
