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

if($_GENERAL[1])
{
	if($_REQUEST['action']=="find")
	{
		// **************************************
		// **** Initialisation des variables ****
		$errorsearchpseudo	=	false;
		$searchpseudo		=	NULLSTR;
		$querystring		=	NULLSTR;
		$Peudo				=	NULLSTR;
		$tabl_query 		= 	array();
		
		// ************************************
		// **** Constitution de la requête ****
		
		// start requete
		$querystring		=	"SELECT parent FROM ".$_PRE."posts WHERE ";

		// requete - depuis quand
		if($_POST['fromtime']=="1")
			$tabl_query[]	=	"date>".(time()-2592000);
		if($_POST['fromtime']=="2")
			$tabl_query[]	=	"date>".(time()-8640000);	
		
		// requete - mot clé
		if(strlen($_POST['keyword'])>0)
		{
			$skeyword					=	getformatmsg($_POST['keyword'],false);		
			if($_POST['in'] == "1")
				$tabl_query[]			=	"(sujet LIKE \"%$skeyword%\")";
			elseif ($_POST['in'] == "2")
				$tabl_query[]			=	"((sujet LIKE \"%$skeyword%\") OR (msg  LIKE \"%$skeyword%\"))";
		}
				
		// recherche par pseudo
		if(strlen($_POST['pseudosearch'])>0)
		{
			$Pseudo			=	getformatmsg($_POST['pseudosearch'],false);
			
			if($_POST['parampseudo']==2) // Si une partie du pseudo
			{
				$querysearch			=	mysql_query("SELECT userid FROM ".$_PRE."user WHERE login LIKE \"%$Pseudo%\"");
				$nbquerysearch			=	mysql_num_rows($querysearch);
				
				if($nbquerysearch>0)
				{
					while($trans		=	mysql_fetch_array($querysearch))
						$trans2[]		=	$trans['userid'];
	
					$searchpseudo		=	implode($trans2,"','");
					unset($trans2);
					
					$tabl_query[]="idmembre IN ('$searchpseudo')";
				}
				else
					$errorsearchpseudo	=	true;
			}
			else // Si pseudo exact
			{
				$querysearch			=	mysql_query("SELECT userid FROM ".$_PRE."user WHERE login ='$Pseudo'");
				$nbquerysearch			=	mysql_num_rows($querysearch);
				
				if($nbquerysearch>0)
				{
					$trans				=	mysql_fetch_array($querysearch);
					$searchpseudo		=	$trans['userid'];
					$tabl_query[]		=	"idmembre = $searchpseudo";
				}
				else
					$errorsearchpseudo	=	true;
			}			
		}
	
		// sélection des forums autorisés
		$maskarray						=	array();
			
		$query							=	$sql->query("SELECT * FROM ".$_PRE."forums");
		$nb								=	mysql_num_rows($query);
		if($nb>0)
		{
			while($j = mysql_fetch_array($query))
			{
				if(isset($_PERMFORUM[$j['forumid']][1]) && $_PERMFORUM[$j['forumid']][1])
					$maskarray[]=$j['forumid'];	
			}	
		}
		$forummask 						=	"'".implode("','",$maskarray)."'";
		
		// topics auxquels on a participé
		if(isset($_GET['idmembre']) && intval($_GET['idmembre'])>0 && intval($_GET['forumsearch']) > 0 && in_array(intval($_GET['forumsearch']),$maskarray))
		{
			$searchpseudo 				= 	intval($_GET['idmembre']);
			
			$tabl_query[]				=	"idmembre = $searchpseudo";
			$tabl_query[]				=	"idforum='$forumsearch'";
		}
		elseif(intval($_POST['forumsearch']) > 0) // recherche par forum
			$tabl_query[]				=	"idforum='$forumsearch'";
		else // recherche tous forums
			$tabl_query[]				=	"idforum IN ($forummask)";				

		if($errorsearchpseudo==false && (strlen($searchpseudo)>0 || (strlen($searchpseudo)==0 && strlen($_POST['keyword'])>0) ))
		{
			$querystring				.=	implode($tabl_query," AND ")." ORDER BY date DESC";
			
			$resultat					=	mysql_query($querystring);
			$nb 						= 	mysql_num_rows($resultat);
		}
		else
			$nb							=	0;
		
		// ********************************
		// **** sauvegarde du résultat ****
		if($nb>0)
		{
			$cpt=0;
			while($i=mysql_fetch_array($resultat))
			{
				$topic=$i['parent'];
				if(!isset($topics[$topic]))
				{
					$topics[$topic]=true;
					$topfinal[$cpt]=$topic;
					$cpt++;
				}
				
			}
			$sessionsearch=init_session();
			$sessionstring = implode(',',$topfinal);
			$date=time();
			if(isset($Pseudo) && strlen($Pseudo)>0 && strlen($_POST['keyword'])==0)
				$keyword=$Pseudo;
			else
				$keyword=getformatmsg($_POST['keyword'],false);
			
			$sql=mysql_query("INSERT INTO ".$_PRE."search VALUES('$sessionsearch','$keyword','$date','$sessionstring')");
			
			$tpl->box['searchcontent']=$tpl->gettemplate("search","pleasewait");
			$tpl->box['searchcontent'].=getjsredirect("find.php?ssearch=$sessionsearch",2000);
		
		}
		else
			$tpl->box['searchcontent'].=$tpl->gettemplate("search","nofound");
	}
	
	
	if(empty($_REQUEST['action']))
	{
		$tpl->box['pseudosearch'] = NULLSTR;
		
		$perim=time()-3600;
		
		$query = $sql->query("DELETE FROM ".$_PRE."search WHERE time<$perim");
		
		if(isset($_GET['posterid']))
		{
			$posterid	= 	intval($_GET['posterid']);
			$query 		= 	$sql->query("SELECT login FROM ".$_PRE."user WHERE userid='$posterid'");
			$result		=	mysql_num_rows($query);
			if($result>0)
			{
				$j			=	mysql_fetch_array($query);
				$tpl->box['pseudosearch']	=	getformatdbtodb($j['login']);
			}
		}
		
		$tpl->box['forumlist']="";
		$isforum=false;
		
		$query = $sql->query("SELECT * FROM ".$_PRE."categorie ORDER BY catorder");
		$nb = mysql_num_rows($query);
		
		if($nb>0)
		{
			$TabForum=array();
			
			$sqlforums = $sql->query("SELECT * FROM ".$_PRE."forums ORDER BY forumcat,forumorder");
			$nbforums=mysql_num_rows($sqlforums);
			
			if($nbforums>0)
				while($TabForum[]=mysql_fetch_array($sqlforums));
				
			while($Cats=mysql_fetch_array($query))
			{
				$addforum="";
				for($cpt=0;$cpt<count($TabForum);$cpt++)
						if($TabForum[$cpt]['forumcat']==$Cats['catid'])
						{
							$Forums=$TabForum[$cpt];
							if(isset($_PERMFORUM[$Forums['forumid']][1]) && $_PERMFORUM[$Forums['forumid']][1])
								$addforum.=$tpl->gettemplate("search","addforumform");
						}
				if(strlen($addforum)>0)
				{
					$isforum=true;
					$tpl->box['forumlist'].=$tpl->gettemplate("search","addcatform");
					$tpl->box['forumlist'].=$addforum;
				}
			}		
		}
		
		if($isforum)
			$tpl->box['searchcontent']=$tpl->gettemplate("search","searchform");
		else
			$tpl->box['searchcontent']=$tpl->gettemplate("search","cantsearch");
	}
	
	$cache.=$tpl->gettemplate("search","accueilsearch");
	
	$tps = number_format(get_microtime() - $tps_start,4);
	
	$cache.=$tpl->gettemplate("baspage","endhtml");
	$tpl->output($cache);
}
else
	geterror("call_loginbox");
