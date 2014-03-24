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

$nocache=true;

require("secret/connect.php"); 
require("admin/functions.php");
require("entete.php");

getlangage("popup");

if(empty($_REQUEST['action']))
{
	$table_smileys=getloadsmileys();
	
	$tpl->box['listsmileys']="";
	
	for($i=0;$i<count($table_smileys);$i++)
	{
		$tpl->box['code_retour'] 	=	"";
		$tpl->box['smileimg'] 		= 	$table_smileys[$i]['img'];
		$tpl->box['smilecode'] 		= 	$table_smileys[$i]['code'];
		
		if($_USER['wysiwyg'] == "N")
			$tpl->box['code_retour'] 	= " ".$table_smileys[$i]['code']." ";
		else
			$tpl->box['code_retour'] 	= $table_smileys[$i]['img'];
			
		$tpl->box['listsmileys'] .= $tpl->gettemplate("popup","lignesmileys");
	}
		
	$cache.=$tpl->gettemplate("popup","affsmileys");

}
if($_REQUEST['action']=="avatar")
{
	if(!isset($_GET['page']))	$page = 1;
	else						$page = intval($_GET['page']);
	
	$query=$sql->query("SELECT COUNT(*) as nblg FROM "._PRE_."avatars")->execute();
	list($nblogos)=$query->fetch_array();
	
	$tpl->box['navigpage']= getnumberpages($nblogos,"popup",15,$page);
		
	$debut=($page*15)-15;
	
	if(($debut+15)>$nblogos)
		$fin=$nblogos;
	else
		$fin=$debut+15;
	
	$avatar=array();
	
	for($i=1;$i<16;$i++)
		$avatar[$i]="&nbsp;";
	
	$query=$sql->query("SELECT * FROM "._PRE_."avatars ORDER BY idlogo LIMIT %d,%d", array($debut, $fin))->execute();
	
	$i=1;
	while(list($idavatar,$extavatar)=$query->fetch_array())
	{
		$avatar[$i]=$tpl->gettemplate("popup","logosource");
		$i++;
	}
	
	$cache.=$tpl->gettemplate("popup","avatarliste");
}

if($_REQUEST['action']=="print")
{
	getlangage("detail");
	$table_smileys=getloadsmileys();
	InitBBcode();
	
	$tpl->box['printmess']		=	NULLSTR;
	$annonce					=	false;
	
	if(isset($_GET['forumid']))		$_GET['forumid']	=	intval($_GET['forumid']);
	else							$_GET['forumid']	=	0;

	if(isset($_GET['idtopic']))		$_GET['idtopic']	=	intval($_GET['idtopic']);
	else							$_GET['idtopic']	=	0;

	if(isset($_GET['idpost']))		$_GET['idpost']		=	intval($_GET['idpost']);
	else							$_GET['idpost']		=	0;

	if(isset($_GET['idann']))
	{
									$_GET['idann']		=	intval($_GET['idann']);
									$annonce			=	true;
	}
	else							$_GET['idann']		=	0;
	
	$ForumInfo		=	getforumname($_GET['forumid']);
	
	if(!$_PERMFORUM[$_GET['forumid']][2])
		geterror("call_loginbox");
	
	if($_GET['idtopic']>0)
		$query = $sql->query("SELECT sujet,date,pseudo,msg,parent,icone,smiles,bbcode FROM "._PRE_."posts WHERE parent=%d AND idforum=%d ORDER BY idpost ASC", array($_GET['idtopic'], $_GET['forumid']))->execute();
	elseif($_GET['idpost']>0)
		$query = $sql->query("SELECT sujet,date,pseudo,msg,parent,icone,smiles,bbcode FROM "._PRE_."posts WHERE idpost=%d AND idforum=%d", array($_GET['idpost'], $_GET['forumid']))->execute();
	else
		$query = $sql->query("SELECT sujet,date,derposter AS pseudo,msg,icone,smiles,bbcode FROM "._PRE_."annonces WHERE idpost=%d AND inforums REGEXP\"/%d/\"", array($_GET['idann'], $_GET['forumid']))->execute();
	
	while($DetailMsg=mysql_fetch_array($query))
	{
		if($DetailMsg['smiles']=="Y")	$DetailMsg['msg']	=	getreturnsmilies($DetailMsg['msg']);
		if($DetailMsg['bbcode']=="Y")	$DetailMsg['msg']	=	getreturnbbcode($DetailMsg['msg'],true);
		
		$DetailMsg['date']		=	getlocaltime($DetailMsg['date']);
		$DetailMsg['sujet']		=	getformatrecup($DetailMsg['sujet']);
		$DetailMsg['pseudo']	=	getformatrecup($DetailMsg['pseudo']);
		$DetailMsg['msg']		=	censuredwords($DetailMsg['msg']);
		$DetailMsg['msg']		=	getformatrecup($DetailMsg['msg']);
		
		if(strlen($DetailMsg['sujet'])==0)	$tpl->box['sujet']	=	"";
		else					$tpl->box['sujet']=$tpl->gettemplate("popup","printsujet");
		
		if(!$annonce)
			$IdTopic		=	$DetailMsg['parent'];
		else
			$IdTopic		=	$_GET['idann'];
			
		$tpl->box['printmess']	.=	$tpl->gettemplate("popup","printmess");
	}
	
	$TopicInfo 	= 	gettopictitle($IdTopic,$annonce);
	$cache		=	$tpl->gettemplate("popup","printstruc");
	$tpl->output($cache);
	die();
}

if($_REQUEST['action']=="profile")
{
	$user=intval($_REQUEST['id']);
	$query=$sql->query("SELECT "._PRE_."user.*,"._PRE_."userplus.*, "._PRE_."groups.Nom_group
							FROM "._PRE_."user 
							LEFT JOIN "._PRE_."userplus ON "._PRE_."userplus.idplus="._PRE_."user.userid
							LEFT JOIN "._PRE_."groups ON "._PRE_."groups.id_group = "._PRE_."user.userstatus 
							WHERE "._PRE_."user.userid=%d", $user)->execute();
	$nb=$query->num_rows();
	
	if($nb==1)
	{
		$InfosMB=$query->fetch_array();
		
		$query=$sql->query("SELECT "._PRE_."skins.valeur,"._PRE_."language.langue FROM "._PRE_."skins LEFT JOIN "._PRE_."language ON "._PRE_."language.code='%s' WHERE "._PRE_."skins.id=%d AND "._PRE_."skins.propriete='skinname'", array($InfosMB['lng'], $InfosMB['skin']))->execute();
		$InfosDIV=$query->fetch_array();
		
		if(preg_match("|^\"http://|",$InfosMB['userlogo']) > 0)
		{
			if($_FORUMCFG['logos'][6] == "Y" && $_FORUMCFG['logos'][0] == "Y")
				$tpl->box['avatar']=$tpl->gettemplate("popup","pf_extavatar");
		}
		elseif(!empty($InfosMB['userlogo']) && $_FORUMCFG['logos'][0] == "Y")
			$tpl->box['avatar']=$tpl->gettemplate("popup","pf_avatar");
		else
			$tpl->box['avatar']=$tpl->attlang("noavatar");
		
		// **** STATUS ****
		$InfosMB['login'] = getformatrecup($InfosMB['login']);
		
		$tpl->box['mbstatus']=$InfosMB['userstatus'];
		if($InfosMB['userstatus'] > 0)
			$tpl->box['mbpseudo']=getformatrecup($InfosMB['Nom_group']);
		else
			$tpl->box['mbpseudo']=$tpl->attlang("mb_banned");

		$tpl->box['userstatus']=$tpl->gettemplate("entete","mbpseudo");
		
		$tpl->box['registerdate'] = getlocaltime($InfosMB['registerdate'],1);
		
		if(!empty($InfosMB['usermail']) && $InfosMB['showmail']=="Y")
		{
			$tpl->box['tempmail']=getemail($InfosMB['usermail']);
			$tpl->box['usermail']=$tpl->gettemplate("popup","pf_mail");
		}
		else
			$tpl->box['usermail']=$tpl->attlang("pf_inconnu");

		if(!empty($InfosMB['usersite']) && $InfosMB['showusersite']=="Y")
			$tpl->box['usersite']=$tpl->gettemplate("popup","pf_site");
		else
			$tpl->box['usersite']=$tpl->attlang("pf_inconnu");
		
		if(!empty($InfosMB['description']))
		{
			$InfosMB['description']=getformatrecup($InfosMB['description']);
			$tpl->box['description']=$tpl->gettemplate("popup","pf_description");
		}
		else
			$tpl->box['description']=NULLSTR;
		
		if(!empty($InfosMB['icq']))
			$tpl->box['infoicq']=$tpl->gettemplate("popup","pf_infoicq");
		else
		{
			$InfosMB['icq']=$tpl->attlang("pf_inconnu");
			$tpl->box['infoicq']=NULLSTR;
		}
		
		if(empty($InfosMB['aim']))
			$InfosMB['aim']=$tpl->attlang("pf_inconnu");
		
		if(empty($InfosMB['msn']))
			$InfosMB['msn']=$tpl->attlang("pf_inconnu");
		
		if(empty($InfosMB['yahoomsg']))
			$InfosMB['yahoomsg']=$tpl->attlang("pf_inconnu");
		
		if(preg_match("|^[0-9]{2}-[0-9]{2}-[0-9]{4}$|",$InfosMB['birth']) > 0)
			$tpl->box['birthday']=implode("/",explode("-",$InfosMB['birth']));
		else
			$tpl->box['birthday']=$tpl->attlang("pf_inconnu");

		if($InfosMB['sex']=="M")
			$tpl->box['usersex']=$tpl->attlang("pf_sexm");
		else
			$tpl->box['usersex']=$tpl->attlang("pf_sexf");
			
		if($InfosMB['usermsg']>0)
		{
			$datetoday=time();
			$nbjours= Ceil(($datetoday - $InfosMB['registerdate'])/86400);
			if($nbjours>0)
				$tpl->box['nbpostperday'] = number_format($InfosMB['usermsg']/$nbjours,2);
			else
				$tpl->box['nbpostperday'] = 0;
			$tpl->box['statperday']=$tpl->gettemplate("popup","pf_statperday");
		}
		else
			$tpl->box['statperday']=NULLSTR;
		$cache.=$tpl->gettemplate("popup","profile");	
	}
	
}

if($_REQUEST['action']=="popuppm")
{
	eval("\$tpl->box['popuppmcmt']=\"".$tpl->attlang("popuppmcmt")."\";");
	$cache .= $tpl->gettemplate("popup","popuppm");
}

$tps = number_format(get_microtime() - $tps_start,4);
$cache.=$tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
