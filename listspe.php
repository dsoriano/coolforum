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
	
$query=$sql->query("SELECT * FROM ".$_PRE."forums");
$nb=mysql_num_rows($query);

if($nb>0)
	while($j=mysql_fetch_array($query))
		if(empty($_PERMFORUM[$j['forumid']][1]) || $_PERMFORUM[$j['forumid']][1]==false)
			$ForumMask[]=$j['forumid'];

if(count($ForumMask)>0)	$Mask = "AND idforum NOT IN ('".implode("','",$ForumMask)."')";
else			$Mask = NULLSTR;


// ###### Récupération et affichage de la liste des nouveaux topics ######

$query = $sql->query("SELECT idtopic FROM ".$_PRE."topics WHERE datederrep>'".$_USER['lastvisit']."' ".$Mask);
$nb = mysql_num_rows($query);

if($nb > 0)
{
	while(list($t)=mysql_fetch_array($query))
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
	
	
	$query = $sql->query("SELECT ".$_PRE."topics.idtopic,
		".$_PRE."topics.idforum AS forumid,
		".$_PRE."topics.sujet,
		".$_PRE."topics.nbrep,
		".$_PRE."topics.nbvues,
		".$_PRE."topics.datederrep,
		".$_PRE."topics.derposter,
		".$_PRE."topics.idderpost,
		".$_PRE."topics.icone,
		".$_PRE."topics.idmembre,
		".$_PRE."topics.pseudo,
		".$_PRE."user.login,
		".$_PRE."user.userstatus,
		".$_PRE."user.userid
		FROM ".$_PRE."topics 
	LEFT JOIN ".$_PRE."user ON ".$_PRE."topics.idmembre=".$_PRE."user.userid 
	WHERE idtopic IN ('".implode("','",$Topics)."') LIMIT ".$debut.",".$fin);
	
	while($Topics=mysql_fetch_array($query))
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

