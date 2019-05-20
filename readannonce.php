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

getlangage("detail");

if (empty($_GET['id']) || empty($_GET['forumid']))
{
	require("entete.php");
	geterror("novalidlink");
}

$_GET['id'] 	= 	intval($_GET['id']);
$_GET['forumid'] 	= 	intval($_GET['forumid']);

if(isset($_GET['p']))
	$_GET['p'] = intval($_GET['p']);

// #### définition du lieu ###
$_SESSION['SessLieu']	=	_LOCATION_FORUM_;
$_SESSION['SessForum']	=	$_GET['forumid'];
$_SESSION['SessTopic']	=	0;
//////////////////////////////

require("entete.php");

$table_smileys=getloadsmileys();

$errorlink=true;


$query = $sql->query("SELECT inforums FROM "._PRE_."annonces WHERE idpost=%d",$_GET['id'])->execute();
if($query->num_rows()==1)
{
	$validforums=$query->fetch_array();
	//if(substr($validforums['inforums'],$_GET['forumid']-1,1)=="0")
	if(preg_match("|/".$_GET['forumid']."/|",$validforums['inforums']) == 0)
		$errorlink=false;
}
else
	$errorlink=false;

if(!$errorlink)
	geterror("novalidlink");

$TopicInfo=gettopictitle($_GET['id'],true);
if(!$TopicInfo)
	geterror("novalidlink");


$ForumInfo=getforumname($_GET['forumid']);

if(!$_PERMFORUM[$_GET['forumid']][2])
	geterror("call_loginbox");

$ForumInfo['cattitle']	=	getformatrecup($ForumInfo['cattitle']);
$ForumInfo['forumtitle']	=	getformatrecup($ForumInfo['forumtitle']);
$TopicInfo['sujet']	=	getformatrecup($TopicInfo['sujet']);
$tpl->treenavs		=	$tpl->gettemplate("treenav","treedetail");
$cache		       .=	$tpl->gettemplate("treenav","hierarchy");

$PrintRedirect	=	"idann=".$_GET['id'];

$query = $sql->query("UPDATE "._PRE_."annonces SET nbvues=nbvues+1 WHERE idpost=%d",$_GET['id'])->execute();

$query = $sql->query("SELECT "._PRE_."annonces.idpost AS idpost,"._PRE_."annonces.sujet AS sujetpost, "._PRE_."annonces.date AS datepost,
"._PRE_."annonces.msg AS msgpost, "._PRE_."annonces.icone AS iconpost, "._PRE_."annonces.idmembre AS posterid,"._PRE_."annonces.smiles AS smiles, "._PRE_."annonces.bbcode AS afbbcode, "._PRE_."annonces.poll , "._PRE_."user.* 
FROM "._PRE_."annonces
LEFT JOIN "._PRE_."user ON "._PRE_."annonces.idmembre="._PRE_."user.userid
WHERE idpost=%d",$_GET['id'])->execute();

InitBBcode();
$tpl->box['forumcontent']="";

$nb = $query->num_rows();

if($nb>0)
{
	$DetailMsg=$query->fetch_array();
	$tpl->box['forumcontent'].=affdetailtopic(1);

	if($DetailMsg['poll']>0)
	{
		$pollreq	=	$sql->query("SELECT * FROM "._PRE_."poll WHERE id=%d", $DetailMsg['poll'])->execute();
		$sd		=	$pollreq->fetch_array();

		$tpl->box['questpoll'] = getformatrecup($sd['question']);

		if(preg_match("|-".$_USER['userid']."-|",$sd['votants']) == 0 && $_USER['userstatus']>1)
		{
			$tpl->box['buttonvote']	=	$tpl->gettemplate("detail","votebutton");
			$canvote		=	true;
		}
		else
		{
			$tpl->box['buttonvote']	=	"";
			$canvote		=	false;
		}

		$nbrep		=	explode(" >> ",$sd['rep']);
		$choix		=	explode(" >> ",$sd['choix']);
		$nbtotalrep	=	0;

		for($i=0; $i<count($choix);$i++)
			$nbtotalrep += $nbrep[$i];

		$swapbgcolor=true;

		$tpl->box['pollchoice']="";
		for($i=0;$i<count($choix);$i++)
		{
			if($swapbgcolor)
				$pollbgcolor=$_SKIN['bgtable1'];
			else
				$pollbgcolor=$_SKIN['bgtable2'];

			if($nbtotalrep>0)
				$percent=round(($nbrep[$i]*100)/$nbtotalrep);
			else
				$percent=0;
			$tpl->box['altpoll']=getformatrecup($choix[$i]);
			if($canvote)
				$tpl->box['radiopoll']=$tpl->gettemplate("detail","votechoice");
			$tpl->box['pollchoice'].=$tpl->gettemplate("detail","lignesondage");

			$swapbgcolor=!$swapbgcolor;
		}
		$tpl->box['affpoll']=$tpl->gettemplate("detail","boxsondage");
	}
}

$cache.=$tpl->gettemplate("detail","boxdetail");

session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
