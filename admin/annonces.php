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

if(empty($_REQUEST['action']) || !isset($_REQUEST['action']))
	$_REQUEST['action'] = "";
	
if($_REQUEST['action']=="preview")
	$nocache=true;
	
require("entete.php"); 
getlangage("adm_annonces");

if($_REQUEST['action']=="delann")
{
	$query=$sql->query("DELETE FROM ".$_PRE."annonces WHERE idpost=".$_POST['id']);
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="avert")
{
	$tpl->box['admcontent']=$tpl->gettemplate("adm_annonces","avert");
}

if($_REQUEST['action']=="preview")
{
	//$nocache=true;
	//require("entete.php");

	$table_smileys=getloadsmileys();
		
	$_POST['msg']=getformatpreview($_POST['msg']);
	if($_POST['smilecode']!="non")
		$_POST['msg']=getreturnsmilies($_POST['msg']);
	if($_POST['bbcode']!="non")
	{
		InitBBcode();
		$_POST['msg']=getreturnbbcode($_POST['msg']);
	}
		
	$tpl->box['affmessage']=$_POST['msg'];
	
	$cache.=$tpl->gettemplate("writebox","msgpreview");
	
	require("bas.php");
	exit;	
}

if($_REQUEST['action']=="saveann")
{
	// **** test du sujet ****
	if($_POST['parent']==0)
	{
		$testchain=preg_replace("/([\s]{1,})/","",$_POST['sujet']);
		if(strlen($testchain)==0)
			$error=$tpl->attlang("badsujet");
	}

	// **** test et formattage du message ****
	$testchain=preg_replace("/([\s]{1,})/","",$_POST['msg']);
	if(strlen($testchain)==0)
		$error=$tpl->attlang("badmsg");

	$inforums = "";
	$intestingrele = array();
	
	if(is_array($_POST['forumapp']) && count($_POST['forumapp']) > 0)
	{
		$_POST['forumapp'] = array_map("intval",$_POST['forumapp']);
		$inforums = "/".implode("/",$_POST['forumapp'])."/";
	}

	$testsond = preg_replace("/([\s]{1,})/","",$_POST['pollquest']);
	if(strlen($testsond)>0)
	{
		for($i=1;$i<$_FORUMCFG['limitpoll']+1;$i++)
		{
			$testchain=preg_replace("/([\s]{1,})/","",$_POST['choixvote'][$i]);
			
			if(strlen($testchain)>0)
			{
				$choice[]=getformatmsg($_POST['choixvote'][$i]);
				$nbrep[]=0;
			}
		}
		if(count($choice)<2)
			$error=$tpl->attlang("badreppoll");
	}
	
	if(strlen($error)==0)
	{
		$date=time();
		$sujet = getformatmsg($_POST['sujet']);

		if($_USER['wysiwyg']=="Y")
		{
			$msg		=	convert_html_to_bbcode($_POST['msg']);
			$msg		=	getformatmsghtml($msg);
		}
		else
			$msg		=	getformatmsg($_POST['msg']);	// formattage du message
		
		if($_POST['bbcode'] == "non")	$nobb		=	"N";		// test si bbcode actif ou non
			else			$nobb		=	"Y";
			
		if($_POST['smilecode'] == "non")	$smiles		=	"N";		// active ou non smileys
			else			$smiles		=	"Y";
		
		if($_POST['id']==0)
		{
			if(strlen($testsond)>0)
			{
				$chainechoix 	= 	implode(" >> ",$choice);
				$chainerep 	= 	implode(" >> ",$nbrep);
				$pollquest	=	getformatmsg($_POST['pollquest'],false);
				
				$query		=	$sql->query("INSERT INTO ".$_PRE."poll (date,question,choix,rep,votants) VALUES ('".$date."','".$pollquest."','".$chainechoix."','".$chainerep."','-')");
				$idpoll		=	mysql_insert_id();
			}
			else	$idpoll		=	0;
			
			$query = $sql->query("INSERT INTO ".$_PRE."annonces (
						sujet,
						date,
						msg,
						nbvues,
						datederrep,
						derposter,
						icone,
						idmembre,
						smiles,
						bbcode,
						inforums,
						poll) 
						VALUES 
						('$sujet','$date','$msg',0,'$date','".$_USER['username']."','".$_POST['icon']."', '".$_USER['userid']."','$smiles','$nobb','$inforums','".$idpoll."')"); 
		}
		else
		{
			$query = $sql->query("UPDATE ".$_PRE."annonces SET
						sujet='$sujet', 
						msg='$msg', 
						icone='".$_POST['icon']."',
						datederrep='$date', 
						inforums='$inforums', 
						smiles='$smiles', 
						bbcode='$nobb' 
						WHERE idpost=".$_POST['id']);
		}
		$_REQUEST['action'] = NULLSTR;
	}
	else
	{
		$tpl->box['errorbox']=$tpl->gettemplate("adm_annonces","errorbox");
		$_REQUEST['action'] = "edit";
	}

}

if($_REQUEST['action']=="edit")
{
	getlangage("writebox");
	$table_smileys		=		getloadsmileys();
	$cachedir			=		"../";
	$tpl->box['listforum'] = NULLSTR;
	$tpl->box['cancache'] = NULLSTR;
	$tpl->box['mailnotify'] = NULLSTR;
	$tpl->box['sondage'] = NULLSTR;
	$tpl->box['errorbox'] = NULLSTR;
	$tpl->box['affpoll']	=NULLSTR;
	
	$Icon_Select = array(	NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, 
							NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR);
	

	if($_REQUEST['id']>0 && strlen($error)==0)
	{
		$query							=		$sql->query("SELECT * FROM ".$_PRE."annonces WHERE idpost=".$_REQUEST['id']);
		$Ann							=		mysql_fetch_array($query);
		
		$Ann['sujet']  					= 		getformatrecup($Ann['sujet']);
		$Ann['msg']						=		getformatrecup($Ann['msg']);
		
		if($Ann['smiles'] == "N")
			$tpl->box['smilechecked']		=		" checked";
		else
			$tpl->box['smilechecked']		=		NULLSTR;
			
		if($Ann['bbcode'] == "N")
			$tpl->box['bbcodechecked']	=		" checked";
		else
			$tpl->box['bbcodechecked']		=		NULLSTR;
	}
	elseif(strlen($error)>0)
	{
		$Ann 							= 		$_POST;
		$Ann['sujet']  					= 		getrecupforform($Ann['sujet']);
		$Ann['msg']  						= 		getrecupforform($Ann['msg']);
		$Ann['inforums'] 					= 		$inforums;

		if($Ann['bbcode'] == "non")		$tpl->box['bbcodechecked']	=	" checked";		// test si bbcode actif ou non
		if($Ann['smilecode'] == "non")	$tpl->box['smilechecked']		=	" checked";		// active ou non smileys
	}
	else
		$Ann 							= 		array();
	
	$IconNumber 						= 	substr($Ann['icone'],4);
	if(strlen($IconNumber)>0)
		$Icon_Select[$IconNumber] 		= 	"CHECKED";
	else
		$Icon_Select[1] 				= 	"CHECKED";
	
	$LimiteLength 						= 	0;
	$tpl->box['limitmsgdef'] 				= 	$tpl->attlang("nolimit");

	if($_USER['wysiwyg'] == "Y")
		$tpl->box['javascript']			=	$tpl->gettemplate("writebox_wysiwyg","wysiwygjs");
	else
		$tpl->box['javascript']			=	$tpl->gettemplate("entete","getjscompter");
	
	// **** Affichage WriteBox ****
	$tpl->box['quotemsg']					=	$Ann['msg'];
	$tpl->box['boxwritepage']				=	affwritebox("N");
	
	// **** Liste des forums ****
	$query								=	$sql->query("SELECT forumid,forumtitle FROM ".$_PRE."forums ORDER BY forumid");
	$nbforums							=	mysql_num_rows($query);
	
	if($nbforums>0)
	{
		$intestingrele = array();
		
		$Ann['inforums'] = substr($Ann['inforums'],1,strlen($Ann['inforums'])-2);
		$intestingrele = explode("/",$Ann['inforums']);
		
		while($zz = mysql_fetch_array($query))
		{
			$ForumLst = "";
			if(in_array($zz['forumid'],$intestingrele))
				$ForumLst = " CHECKED";
			$zz['forumtitle']=getformatrecup($zz['forumtitle']);
			$tpl->box['listforum'].=$tpl->gettemplate("adm_annonces","checkforum");
		}
	}
	else
		$tpl->box['listforum']=$tpl->gettemplate("adm_annonces","ifnoforum");
	
	// **** Sondage ****
	if(intval($_REQUEST['id'])==0)
	{
		$pollquest = getrecupforform($_POST['pollquest']);
		
		for($i=1;$i<$_FORUMCFG['limitpoll']+1;$i++)
		{
			$pollvalue		 =	getrecupforform($_POST['choixvote'][$i]);
			$tpl->box['pollchoix']	.=	$tpl->gettemplate("adm_annonces","pagepollchoice");
		}
		$tpl->box['affpoll']	=	$tpl->gettemplate("adm_annonces","pagepoll");
	}
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_annonces","formannonce");
}

if(empty($_REQUEST['action']))
{
	$tpl->box['listannonces'] = NULLSTR;
	$tpl->box['forumslist'] = NULLSTR;
	$intestingrele = array();
	
	$query=$sql->query("SELECT *,".$_PRE."user.login FROM ".$_PRE."annonces LEFT JOIN ".$_PRE."user ON ".$_PRE."annonces.idmembre=".$_PRE."user.userid ORDER by idpost");
	$nb=mysql_num_rows($query);
	if($nb==0)
		$tpl->box['listannonces']=$tpl->gettemplate("adm_annonces","ifnoann");
	
	else
	{
		$Forumz=$sql->query("SELECT forumid,forumtitle FROM ".$_PRE."forums ORDER BY forumid");
		while($zz=mysql_fetch_array($Forumz))
			$appforum[$zz['forumid']]=getformatrecup($zz['forumtitle']);
			
		while($Ann=mysql_fetch_array($query))
		{
			$tpl->box['forumslist'] = NULLSTR;
			
			$Ann['sujet'] = getformatrecup($Ann['sujet']);
			$Ann['login'] = getformatrecup($Ann['login']);
			$Ann['date'] = getlocaltime($Ann['date'],1);
			
			
			$Ann['inforums'] = substr($Ann['inforums'],1,strlen($Ann['inforums'])-2);
			$intestingrele = explode("/",$Ann['inforums']);
			
			if(is_array($intestingrele) && count($intestingrele)>0)
				foreach($intestingrele as $value)
					if(!empty($appforum[$value]))
						$transit[] = $appforum[$value];
			
			if(count($transit) > 0)
				$tpl->box['forumslist'] = implode(", ",$transit);
			
			if($Ann['poll']>0)	$tpl->box['poll']=$tpl->attlang("haspoll");
			else			$tpl->box['poll']=$tpl->attlang("hasnopoll");
			
			$tpl->box['listannonces'].=$tpl->gettemplate("adm_annonces","tableannonces");
			unset($transit);
		}
	}
	$tpl->box['admcontent']=$tpl->gettemplate("adm_annonces","listannonces");	
}

$cache.=$tpl->gettemplate("adm_annonces","content");
require("bas.php");
