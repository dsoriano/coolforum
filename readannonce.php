<?php
//*********************************************************************************
//*                                                                               *
//*                  CoolForum v.0.8.5 Beta : Forum de discussion                   *
//*              Copyright �2001-2014 SORIANO Denis alias Cool Coyote             *
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
//*       Forum Cr�� par SORIANO Denis (Cool Coyote)                              *
//*       contact : coyote@coolcoyote.net                                         *
//*       site web et t�l�chargement : http://www.coolforum.net                   *
//*                                                                               *
//*********************************************************************************

require("secret/connect.php"); 
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

// #### d�finition du lieu ###
$SessLieu	=	'FOR';
$SessForum	=	$_GET['forumid'];
$SessTopic	=	0;
//////////////////////////////

require("entete.php");

$table_smileys=getloadsmileys();

$errorlink=true;


$query = $sql->query("SELECT inforums FROM ".$_PRE."annonces WHERE idpost=".$_GET['id']);
if(mysql_num_rows($query)==1)
{
	$validforums=mysql_fetch_array($query);
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

$query = $sql->query("UPDATE ".$_PRE."annonces SET nbvues=nbvues+1 WHERE idpost=".$_GET['id']); 

$query = $sql->query("SELECT ".$_PRE."annonces.idpost AS idpost,".$_PRE."annonces.sujet AS sujetpost, ".$_PRE."annonces.date AS datepost,
".$_PRE."annonces.msg AS msgpost, ".$_PRE."annonces.icone AS iconpost, ".$_PRE."annonces.idmembre AS posterid,".$_PRE."annonces.smiles AS smiles, ".$_PRE."annonces.bbcode AS afbbcode, ".$_PRE."annonces.poll , ".$_PRE."user.* 
FROM ".$_PRE."annonces
LEFT JOIN ".$_PRE."user ON ".$_PRE."annonces.idmembre=".$_PRE."user.userid
WHERE idpost=".$_GET['id']);

InitBBcode();
$tpl->box['forumcontent']="";

$nb = mysql_num_rows($query);

if($nb>0)
{
	$DetailMsg=mysql_fetch_array($query);
	$tpl->box['forumcontent'].=affdetailtopic(1);
	
	if($DetailMsg['poll']>0)
	{
		$pollreq	=	$sql->query("SELECT * FROM ".$_PRE."poll WHERE id='".$DetailMsg['poll']."'");
		$sd		=	mysql_fetch_array($pollreq);
		
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

$tps = number_format(get_microtime() - $tps_start,4);

$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
