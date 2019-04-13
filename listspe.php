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
$SessLieu	=	'SEA';
$SessForum	=	0;
$SessTopic	=	0;
//////////////////////////////

require("entete.php");

if($_USER['userid']==0)
	header("Location: index.php");

getlangage("listspe");

// ###### Navigation ######
$tpl->box['boxconnected']=NULLSTR;
$KeyWords=$tpl->attlang("newmsgtitle");
$tpl->treenavs=$tpl->gettemplate("treenav","treenewmsg");
$cache.=$tpl->gettemplate("treenav","hierarchy");


// ###### Initialisation ######

$ForumMask=array();
$Topics=array();
$tpl->box['topiclist']=$tpl->box['numberpages']=NULLSTR;
	
$query=$sql->query("SELECT * FROM "._PRE_."forums")->execute();
$nb=$query->num_rows();

if($nb>0)
	while($j=$query->fetch_array())
		if(empty($_PERMFORUM[$j['forumid']][1]) || $_PERMFORUM[$j['forumid']][1]==false)
			$ForumMask[]=$j['forumid'];

if(count($ForumMask)>0)	$Mask = "AND idforum NOT IN ('".implode("','",$ForumMask)."')";
else			$Mask = NULLSTR;


// ###### Récupération et affichage de la liste des nouveaux topics ######

$query = $sql->query("SELECT idtopic FROM "._PRE_."topics WHERE datederrep > '%s' ".$Mask,array($_USER['lastvisit']))->execute();
$nb = $query->num_rows();

if($nb > 0)
{
	while(list($t)=$query->fetch_array())
		$Topics[]=$t;
	
	$UrlKeyWord = urlencode($KeyWords);
	$cookiedetails="CoolForumDetails";    
	if(isset($_COOKIE[$cookiedetails]))
		$cookiespost=cookdecode($_COOKIE[$cookiedetails]);
	
	if(empty($_REQUEST['page']))	$page=1;
	else				$page=intval($_REQUEST['page']);
	if($page < 1)			$page=1;

	if(!isset($_GET['page']) || empty($_GET['page']))		$page	=	1;
	else													$page	= intval($_GET['page']);
	
	$tpl->box['numberpages']=getnumberpages($nb,"listspe",$_FORUMCFG['topicparpage'],$page);
	
	$debut=($page*$_FORUMCFG['topicparpage'])-$_FORUMCFG['topicparpage'];
	
	if($nb > ($debut+$_FORUMCFG['topicparpage']))	$fin=$debut+$_FORUMCFG['topicparpage'];
	else						$fin=$nb;
	
	
	$query = $sql->query("SELECT "._PRE_."topics.idtopic,
		"._PRE_."topics.idforum AS forumid,
		"._PRE_."topics.sujet,
		"._PRE_."topics.nbrep,
		"._PRE_."topics.nbvues,
		"._PRE_."topics.datederrep,
		"._PRE_."topics.derposter,
		"._PRE_."topics.idderpost,
		"._PRE_."topics.icone,
		"._PRE_."topics.idmembre,
		"._PRE_."topics.pseudo,
		"._PRE_."user.login,
		"._PRE_."user.userstatus,
		"._PRE_."user.userid
		FROM "._PRE_."topics 
	LEFT JOIN "._PRE_."user ON "._PRE_."topics.idmembre="._PRE_."user.userid 
	WHERE idtopic IN ('" . implode("','",$Topics) . "') LIMIT %d,%d", array($debut, $fin))->execute();
	
	while($Topics=$query->fetch_array())
	{
		$forumid = $Topics['forumid'];
		$tpl->box['topiclist'].=afftopiclist(0,"listspe");
		unset($forumid);
	}
	
}
else
	$tpl->box['topiclist']=$tpl->gettemplate("listspe","nonewmsg");


$cache.=$tpl->gettemplate("listspe","boxlist");

$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);

