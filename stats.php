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
$SessLieu	=	'STA';
$SessForum	=	0;
$SessTopic	=	0;
//////////////////////////////

require("entete.php"); 

getlangage("stats");

//if($_FORUMCFG[statsconfig][0]=="1" && (($_USER[userstatus]>1 && $_FORUMCFG[statsconfig][$_USER[userstatus]]=="1") || ($_USER[userstatus]<2 && $_FORUMCFG[statsconfig][1]=="1")))
if($_GENERAL[0])
{
	if(empty($_REQUEST['action']))
	{
		$query=$sql->query("SELECT COUNT(*) AS nbsmiles FROM ".$_PRE."smileys");
		list($tpl->box['nbsmiles'])=mysql_fetch_array($query);
		
		$tpl->box['nbtopics']=$_FORUMCFG['statnbtopics'];
		$tpl->box['nbrep']=$_FORUMCFG['statnbposts'];
		$tpl->box['nbmsg']= $_FORUMCFG['statnbtopics']+$_FORUMCFG['statnbposts'];
		
		//// les dates
		$date=explode(" - ",strftime("%d - %m - %Y",time()));
		$thismonth= mktime(0,0,0,$date[1],1,$date[2]);
		$thisday= mktime(0,0,0,$date[1],$date[0],$date[2]);
		
		$query=$sql->query("SELECT COUNT(*) AS nbdays FROM ".$_PRE."posts WHERE date > $thismonth");
		list($tpl->box['postthismonth'])=mysql_fetch_array($query);
	
		$query=$sql->query("SELECT COUNT(*) AS nbdays FROM ".$_PRE."posts WHERE date > $thisday");
		list($tpl->box['postthisday'])=mysql_fetch_array($query);
		
		//// les membres
		$tpl->box['groups_stats']="";
		$tpl->box['nbbannis']=0;
		$tpl->box['nbattente']=0;
		
		
		$query = $sql->query("SELECT COUNT(*) AS nbuser, ".$_PRE."user.userstatus, ".$_PRE."groups.Nom_group FROM ".$_PRE."user LEFT JOIN ".$_PRE."groups ON ".$_PRE."user.userstatus=".$_PRE."groups.id_group GROUP BY userstatus ORDER BY userstatus");
		while($j=mysql_fetch_array($query))
		{
			if($j['userstatus']<0)
				$tpl->box['nbbannis'] += $j['nbuser'];
			elseif($j['userstatus']==0)
				$tpl->box['nbattente'] = $j['nbuser'];
			else
			{
				$tpl->box['group_name'] = $j['Nom_group'];
				$tpl->box['nb_users']   = $j['nbuser'];
				$tpl->box['groups_stats'] .= $tpl->gettemplate("stats","groups_stats");				
			}
		}
		
		$tpl->box['maxusers']=$_FORUMCFG['topmembers'];
		$tpl->box['timetopmembers']=getlocaltime($_FORUMCFG['timetopmembers']);																			
		
		$query=$sql->query("SELECT forumtopic+forumposts AS maxmsg, forumtitle FROM ".$_PRE."forums ORDER BY maxmsg DESC");
	
		list($tpl->box['maxforummsg'],$tpl->box['maxforumname'])=mysql_fetch_array($query);
		
		$query=$sql->query("SELECT SUM(".$_PRE."topics.nbvues) AS Fnbvues,".$_PRE."forums.forumtitle FROM ".$_PRE."topics LEFT JOIN ".$_PRE."forums ON ".$_PRE."forums.forumid=".$_PRE."topics.idforum GROUP BY ".$_PRE."topics.idforum ORDER BY Fnbvues DESC");
		if(!$query)
			echo(mysql_error());
		list($tpl->box['maxvues'],$tpl->box['forummaxvues'])=mysql_fetch_array($query);
		
		$tpl->box['statscontent']=$tpl->gettemplate("stats","statsgeneral");
	}
	
	if($_REQUEST['action']=="tenbesttopics")
	{
		// sélection des forums autorisés
		
		$maskarray=array();
		
		$query=$sql->query("SELECT * FROM ".$_PRE."forums");
		$nb=mysql_num_rows($query);
		if($nb>0)
		{
			while($j=mysql_fetch_array($query))
			{
				if(isset($_PERMFORUM[$j['forumid']][1]) && $_PERMFORUM[$j['forumid']][1])
					$maskarray[]=$j['forumid'];
			}	
		}
		$forummask ="'".implode("','",$maskarray)."'";
	
		$query=$sql->query("SELECT ".$_PRE."topics.idtopic,
					".$_PRE."topics.idforum,
					".$_PRE."topics.sujet,
					".$_PRE."topics.nbrep,
					".$_PRE."topics.nbvues,
					".$_PRE."topics.datederrep,
					".$_PRE."topics.derposter,
					".$_PRE."topics.idderpost,
					".$_PRE."topics.icone,
					".$_PRE."topics.idmembre,
					".$_PRE."topics.pseudo,
					".$_PRE."topics.poll,
					".$_PRE."topics.postit, 
					".$_PRE."user.login 
				FROM ".$_PRE."topics 
				LEFT JOIN ".$_PRE."user ON ".$_PRE."topics.idmembre=".$_PRE."user.userid 
				WHERE ".$_PRE."topics.idforum IN ($forummask) ORDER BY ".$_PRE."topics.nbvues DESC LIMIT 0,10");
		
		//$TitleStat=$tpl->gettemplate("stats",4);
		$CptStats=1;
		
		$tpl->box['topicscontent']="";
		while($Topics=mysql_fetch_array($query))
		{
			$tpl->box['pretopic'] = NULLSTR;
			if($Topics['idmembre']>0)
				$Topics['loginposter']=$Topics['login'];
			else
				$Topics['loginposter']=$Topics['pseudo'];
			$Topics['datederrep']=getlocaltime($Topics['datederrep']);
			$Topics['sujet']=getformatrecup($Topics['sujet']);
			
			if(!empty($Topics['postit']) && $Topics['postit']=="1")
				$tpl->box['pretopic'].=$tpl->gettemplate("stats","ifpostittopic");
					
			if(!empty($Topics['poll']) && $Topics['poll']>0)
				$tpl->box['pretopic'].=$tpl->gettemplate("stats","iftopicpoll");
			
			$forumid = $Topics['idforum'];
			$tpl->box['topic']=$tpl->gettemplate("stats","topiclinktomsg");
			$tpl->box['affichepages']= getpagestopic($Topics['nbrep']+1, $Topics['idtopic'],1);
			
			$tpl->box['gotobutton']=$tpl->gettemplate("stats","linklastmsg");
			
			$tpl->box['topicscontent'].=$tpl->gettemplate("stats","lignetopic");
			$CptStats++;	
		}
		$tpl->box['statscontent']=$tpl->gettemplate("stats","structure1");
	}
	
	if($_REQUEST['action']=="tenbestrep")
	{
		// sélection des forums autorisés
		
		$maskarray=array();
		
		$query=$sql->query("SELECT * FROM ".$_PRE."forums");
		$nb=mysql_num_rows($query);
		if($nb>0)
		{
			while($j=mysql_fetch_array($query))
			{
				if(isset($_PERMFORUM[$j['forumid']][1]) && $_PERMFORUM[$j['forumid']][1])
					$maskarray[]=$j['forumid'];
			}	
		}
		$forummask ="'".implode("','",$maskarray)."'";
		//echo($forummask);
		
		//$query=$sql->query("SELECT idpost,idforum,sujet,date,nbrep,nbvues,datederrep,derposter,icone,idmembre FROM CF_posts WHERE parent=0 ORDER BY nbvues DESC");
	
		$query=$sql->query("SELECT ".$_PRE."topics.idtopic,
					".$_PRE."topics.idforum,
					".$_PRE."topics.sujet,
					".$_PRE."topics.nbrep,
					".$_PRE."topics.nbvues,
					".$_PRE."topics.datederrep,
					".$_PRE."topics.derposter,
					".$_PRE."topics.idderpost,
					".$_PRE."topics.icone,
					".$_PRE."topics.idmembre,
					".$_PRE."topics.pseudo,
					".$_PRE."topics.poll,
					".$_PRE."topics.postit, 
					".$_PRE."user.login 
				FROM ".$_PRE."topics 
				LEFT JOIN ".$_PRE."user ON ".$_PRE."topics.idmembre=".$_PRE."user.userid 
				WHERE ".$_PRE."topics.idforum IN ($forummask) ORDER BY ".$_PRE."topics.nbrep DESC LIMIT 0,10");
		
		if(!$query)
			echo(mysql_error());
		//$TitleStat=$tpl->gettemplate("stats",4);
		$CptStats=1;
		
		$tpl->box['topicscontent']="";
		while($Topics=mysql_fetch_array($query))
		{
			$tpl->box['pretopic'] = NULLSTR;
			
			if($Topics['idmembre']>0)
				$Topics['loginposter']=$Topics['login'];
			else
				$Topics['loginposter']=$Topics['pseudo'];
				
			$Topics['datederrep']=getlocaltime($Topics['datederrep']);
			$Topics['sujet']=getformatrecup($Topics['sujet']);

			if(!empty($Topics['postit']) && $Topics['postit']=="1")
				$tpl->box['pretopic'].=$tpl->gettemplate("stats","ifpostittopic");
					
			if(!empty($Topics['poll']) && $Topics['poll']>0)
				$tpl->box['pretopic'].=$tpl->gettemplate("stats","iftopicpoll");
			
			$forumid = $Topics['idforum'];
			$tpl->box['topic']=$tpl->gettemplate("stats","topiclinktomsg");
			$tpl->box['affichepages']= getpagestopic($Topics['nbrep']+1, $Topics['idtopic'],1);
			
			$tpl->box['topic']=$tpl->gettemplate("stats","topiclinktomsg");
			
			$tpl->box['gotobutton']=$tpl->gettemplate("stats","linklastmsg");
					
			$tpl->box['topicscontent'].=$tpl->gettemplate("stats","lignetopic");
			$CptStats++;	
		}
		$tpl->box['statscontent']=$tpl->gettemplate("stats","structure1");
	}
	
	if($_REQUEST['action']=="tenbestuser")
	{
		$tpl->box['filter']	=	NULLSTR;
		$tpl->box['next']	=	NULLSTR;
		$tpl->box['before'] = 	NULLSTR;

		$query=$sql->query("SELECT userid,login,userstatus,registerdate,usermsg,usermail,usersite,showmail,showusersite FROM ".$_PRE."user WHERE userstatus<>0 ORDER BY usermsg DESC LIMIT 0,10");
		
		//$TitleStat=$tpl->gettemplate("stats",15);
		$tpl->box['topicscontent']="";
		$CptStats=1;
		while($Topics=mysql_fetch_array($query))
		{
			$Topics['registerdate']=getlocaltime($Topics['registerdate'],1);
			$Topics['loginposter']=getformatpseudo($Topics['login'],$Topics['userstatus'],$Topics['userid']);
			
			if($Topics['showmail']=="Y")
			{
				$Topics['usermail']=getemail($Topics['usermail']);
				$tpl->box['email']=$tpl->gettemplate("stats","emaillink");
			}
			else
				$tpl->box['email']="&nbsp;";
			
			if($Topics['showusersite']=="Y" && strlen($Topics['usersite'])>0)
				$tpl->box['siteweb']=$tpl->gettemplate("stats","siteweblink");
			else
				$tpl->box['siteweb']="&nbsp;";
			
						
			$tpl->box['topicscontent'].=$tpl->gettemplate("stats","lignemb");
			$CptStats++;	
		}
		$tpl->box['statscontent']=$tpl->gettemplate("stats","structure2");
	}
	
	if($_REQUEST['action']=="listmember")
	{
		$tpl->box['topicscontent'] = NULLSTR;
		//$TitleStat=$tpl->gettemplate("stats",6);
		
		if(!isset($_GET['debut']))	$debut=0;
		else						$debut= intval($_GET['debut']);
		
		if(isset($_GET['letter']) && $_GET['letter']=="0")
		{
			$Where = "AND login NOT REGEXP('^[a-zA-Z]')";
			$letter = htmlentities($_GET['letter'], ENT_COMPAT,'ISO-8859-1', true);
		}
		elseif(isset($_GET['letter']) && preg_match("|^[a-zA-Z]{1}$|",$_GET['letter']) > 0)
		{
			$Where = "AND login LIKE '".$_GET['letter']."%'";
			$letter = htmlentities($_GET['letter'], ENT_COMPAT,'ISO-8859-1', true);
		}
		else
		{
			$Where = NULLSTR;
			$letter = NULLSTR;
		}
		
		$tpl->box['filter']=$tpl->gettemplate("stats","mbfilter");
		
		$query=$sql->query("SELECT COUNT(*) AS tot FROM ".$_PRE."user WHERE userstatus>0 ".$Where);
		if($query)
			$total=mysql_fetch_array($query);
		
		if($total['tot']>0)
		{
			$query=$sql->query("SELECT userid,login,userstatus,registerdate,usermsg,usermail,usersite,showmail,showusersite FROM ".$_PRE."user WHERE userstatus<>0 ".$Where." ORDER BY login LIMIT ".$debut.",20");
			
			$tpl->box['affstats']="";
			
			$CptStats=$debut+1;
			
			while($Topics=mysql_fetch_array($query))
			{
				$Topics['registerdate']=getlocaltime($Topics['registerdate'],1);
				$Topics['loginposter']=getformatpseudo($Topics['login'],$Topics['userstatus'],$Topics['userid']);
				
				if($Topics['showmail']=="Y")
				{
					$Topics['usermail']=getemail($Topics['usermail']);
					$tpl->box['email']=$tpl->gettemplate("stats","emaillink");
				}
				else
					$tpl->box['email']="&nbsp;";
				
				if($Topics['showusersite']=="Y" && strlen($Topics['usersite'])>0)
					$tpl->box['siteweb']=$tpl->gettemplate("stats","siteweblink");
				else
					$tpl->box['siteweb']="&nbsp;";
				
							
				$tpl->box['topicscontent'].=$tpl->gettemplate("stats","lignemb");
				$CptStats++;	
			}
		}
		else
			$tpl->box['topicscontent']=$tpl->gettemplate("stats","nomb");
			
		if($debut>0)
		{
			$tpl->box['debut']=$debut-20;
			$tpl->box['before']=$tpl->gettemplate("stats","beforelink");
		}
		else
			$tpl->box['before']=$tpl->gettemplate("stats","beforenolink");
		
		if(($debut+20)<$total['tot'])
		{
			$tpl->box['debut']=$debut+20;
			$tpl->box['next']=$tpl->gettemplate("stats","nextlink");
		}
		else
			$tpl->box['next']=$tpl->gettemplate("stats","nextnolink");
		
		//$tpl->box[statscontent]=$tpl->gettemplate("stats",14);
	
		$tpl->box['statscontent']=$tpl->gettemplate("stats","structure2");
	}
	
	if($_REQUEST['action']=="connected")
	{
		$MbConnected=0;
		
		foreach($NombreConnectes as $Connected)
			if($Connected['userid']>0)
				$MbConnected++;
		
		if($MbConnected > 0)
		{
			$Members	= array();
			$ForumsMask	= array();
			$TopicsMask	= array();
			$ForumInfo	= array();
			$TopicInfo	= array();
			
			if(empty($_GET['debut']))	$debut=0;
			else				$debut=intval($_GET['debut']);
			if($debut < 0)			$debut=0;
			
			$query = $sql->query("SELECT username,userid,userstatus,typelieu,forumid,topicid FROM ".$_PRE."session WHERE userid<>'0' ORDER BY username LIMIT ".$debut.",20");
			
			while($j=mysql_fetch_array($query))
				$Members[]=$j;
			
			foreach($Members as $MemberInfo)
			{
				if($MemberInfo['forumid']>0 && !empty($_PERMFORUM[$MemberInfo['forumid']][0]) && $_PERMFORUM[$MemberInfo['forumid']][0]==true)
				{
					$ForumsMask[]=$MemberInfo['forumid'];
					
					if($MemberInfo['topicid']>0 && !empty($_PERMFORUM[$MemberInfo['forumid']][1]) && $_PERMFORUM[$MemberInfo['forumid']][1]==true)
						$TopicsMask[]=$MemberInfo['topicid'];
				}
			}
			
			if(count($ForumsMask)>0)
			{
				$query = $sql->query("SELECT forumid,forumtitle FROM ".$_PRE."forums WHERE forumid IN ('".implode("','",$ForumsMask)."')");
				
				while(list($ForumId,$ForumTitle)=mysql_fetch_array($query))
					$ForumInfo[$ForumId]=getformatrecup($ForumTitle);
				
				if(count($TopicsMask)>0)
				{
					$query = $sql->query("SELECT idtopic,sujet,idderpost FROM ".$_PRE."topics WHERE idtopic IN ('".implode("','",$TopicsMask)."')");
					
					while($j=mysql_fetch_array($query))
					{
						$TopicInfo[$j['idtopic']]=$j;
						$TopicInfo[$j['idtopic']]['sujet']=getformatrecup($TopicInfo[$j['idtopic']]['sujet']);
					}
				}
			}
			
			reset($Members);
			$tpl->box['listconnected']=NULLSTR;
			
			foreach($Members as $MemberInfo)
			{
				$MemberInfo['pseudo']=getformatpseudo($MemberInfo['username'],$MemberInfo['userstatus'],$MemberInfo['userid']);
				
				if($MemberInfo['forumid']>0 && !empty($_PERMFORUM[$MemberInfo['forumid']][0]) && $_PERMFORUM[$MemberInfo['forumid']][0]==true)
				{
					$ForumTitle = $ForumInfo[$MemberInfo['forumid']];
					$tpl->box['typelieu']=$tpl->gettemplate("stats","typelieu_for");
					
					if($MemberInfo['topicid']>0 && !empty($_PERMFORUM[$MemberInfo['forumid']][1]) && $_PERMFORUM[$MemberInfo['forumid']][1]==true)
					{
						$Topic = $TopicInfo[$MemberInfo['topicid']];
						$tpl->box['typelieu'].=$tpl->gettemplate("stats","typelieu_top");
					}
				}
				else
				{
					$TypeLieuUrl = NULLSTR;
					
					switch($MemberInfo['typelieu'])
					{
						case "ACC":
							$TypeLieu	= $tpl->attlang("acc");
							$TypeLieuUrl	= "index.php"; 
							break;
						case "SEA":
							$TypeLieu	= $tpl->attlang("sea");
							if($_GENERAL[1])
								$TypeLieuUrl	= "search.php"; 
							break;
						case "ADM":
							$TypeLieu	= $tpl->attlang("adm");
							break;
						case "STA":
							$TypeLieu	= $tpl->attlang("sta");
							$TypeLieuUrl	= "stats.php";
							break;
						case "HLP":
							$TypeLieu	= $tpl->attlang("hlp");
							$TypeLieuUrl	= "aide.php";
							break;
						case "PRO":
							$TypeLieu	= $tpl->attlang("pro");
							if($_USER['userstatus']>0)
								$TypeLieuUrl	= "profile.php";
							break;
						default:
							$TypeLieu	= $tpl->attlang("acc");
							$TypeLieuUrl	= "index.php"; 
							break;
					}
					
					if(strlen($TypeLieuUrl)>0)	$tpl->box['typelieu']=$tpl->gettemplate("stats","typelieuurl");
					else				$tpl->box['typelieu']=$tpl->gettemplate("stats","typelieu");
				}
				
				$tpl->box['listconnected'].=$tpl->gettemplate("stats","ligneconnected");
			}
			
			if($debut > 0)
			{
				$tpl->box['debut']=$debut-20;
				$tpl->box['before']=$tpl->gettemplate("stats","connectedbeforelink");
			}
			else
				$tpl->box['before']=$tpl->gettemplate("stats","connectedbeforenolink");
			
			if(($debut+20) < $MbConnected)
			{
				$tpl->box['debut']=$debut+20;
				$tpl->box['next']=$tpl->gettemplate("stats","connectednextlink");
			}
			else
				$tpl->box['next']=$tpl->gettemplate("stats","connectednextnolink");
					
		}
		else
			$tpl->box['listconnected']=$tpl->gettemplate("stats","nombconnected");
		
		$tpl->box['statscontent']=$tpl->gettemplate("stats","structure3");
	}
	
	$cache.=$tpl->gettemplate("stats","pageconfig");
	$tps = number_format(get_microtime() - $tps_start,4);
	
	$cache.=$tpl->gettemplate("baspage","endhtml");
	$tpl->output($cache);
}
else
	geterror("call_loginbox");
