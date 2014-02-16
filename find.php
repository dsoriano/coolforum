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

getlangage("search");

$ssearch 	= 	getformatmsg($_GET['ssearch']);
$query		=	$sql->query("SELECT * FROM ".$_PRE."search WHERE idsearch='$ssearch'");
$nb		=	mysql_num_rows($query);

if ($nb>0) {
	$j=mysql_fetch_array($query);
} else {
	$j['keyword']=$tpl->attlang("noresult");
}

$tpl->box['boxconnected']	=	NULLSTR;

// ###### Navigation ######
$KeyWords=getformatrecup($j['keyword']);
$tpl->treenavs=$tpl->gettemplate("treenav","treesearch");
$cache.=$tpl->gettemplate("treenav","hierarchy");

//------------- INITIALISATION DU FORUM --------------------

$tpl->box['topiclist']="";
	
if($nb>0)
{
	$UrlKeyWord = urlencode($KeyWords);
	$cookiedetails="CoolForumDetails";    
	if(isset($_COOKIE[$cookiedetails]))
		$cookiespost=cookdecode($_COOKIE[$cookiedetails]);
	
	$tpl->temp['topfinal']=explode(',',$j['search']);
	$tpl->temp['counttopfinal']=count($tpl->temp['topfinal']);

	if(!isset($_GET['page']) || empty($_GET['page']))		$page	=	1;
	else													$page	= intval($_GET['page']);
	
	$tpl->box['navpages']=getnumberpages($tpl->temp['counttopfinal'],"search",$_FORUMCFG['topicparpage'],$page);
	if($nbpages>1)
		$tpl->box['numberpages']	=	$tpl->gettemplate("search","boxpages");

	
	$debut=($page*$_FORUMCFG['topicparpage'])-$_FORUMCFG['topicparpage'];
	
	if($tpl->temp['counttopfinal']>($debut+$_FORUMCFG['topicparpage']))
		$fin=$debut+$_FORUMCFG['topicparpage'];
	else
		$fin=$tpl->temp['counttopfinal'];
	
	$query=$sql->query("SELECT ".$_PRE."topics.idtopic,
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
		WHERE ".$_PRE."topics.idtopic IN (".$j['search'].") ORDER BY ".$_PRE."topics.datederrep DESC LIMIT $debut,$fin");  
	
	
	while($Topics=mysql_fetch_array($query))
	{
		$forumid = $Topics['forumid'];
		$tpl->box['topiclist'].=afftopiclist(0,"search");
		unset($forumid);
	}	
}
else
	$tpl->box['searchresult']=$tpl->gettemplate("search","errorsearch");

$cache.=$tpl->gettemplate("search","boxlist");

$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);


