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
 
getlangage("adm_addmembre");

$tpl->box['errorbox']=NULLSTR;

if($_REQUEST['action']=="save")
{
		$error="";
		
		//**** test du pseudo ****
		$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['pseudo']);
		if(strlen($testchain)==0)
			$error	=	$tpl->attlang("errorpseudo1");
			
		$rgpseudo	=	trim($_POST['pseudo']);
		$rgpseudo	=	getformatmsg($rgpseudo,false);
		$query		=	$sql->query("SELECT COUNT(*) AS nbpseudos FROM ".$_PRE."user WHERE login='$rgpseudo'");
		list($nbpseudos)=mysql_fetch_array($query);
		if ($nbpseudos>0)
			$error	=	$tpl->attlang("errorpseudo2");

		// test du mot de passe:
		$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['password1']);
		if(strlen($testchain)==0)
			$error	=	$tpl->attlang("errormdp1");
			
		if($_POST['password1']!=$_POST['password2'])
			$error	=	$tpl->attlang("errormdp2");

		// test de l'email
		if(!testemail($_POST['email']))
			$error	=	$tpl->attlang("errormail1");
		else
		{
			$regemail=$_POST['email'];
			$query=$sql->query("SELECT COUNT(*) AS nbmail1 FROM ".$_PRE."user WHERE usermail='$regemail'");
			list($nbmail1)=mysql_fetch_array($query);
			
			$query=$sql->query("SELECT COUNT(*) AS nbmail2 FROM ".$_PRE."userplus WHERE mailorig='$regemail'");
			list($nbmail2)=mysql_fetch_array($query);
			
			if($nbmail1>0 || $nbmail2>0)
				$error=$tpl->attlang("errormail2");
		}			
			
		// test question/réponse si confirmation d'email désactivée
		if ($_FORUMCFG['confirmparmail'] < 2)
		{
			$testchain=preg_replace("/([\s]{1,})/","",$_POST['question']);
			if(strlen($testchain)==0)
				$error=$tpl->attlang("errorquest");			
			$testchain=preg_replace("/([\s]{1,})/","",$_POST['reponse']);
			if(strlen($testchain)==0)
				$error=$tpl->attlang("errorrep");
		}

		if(strlen($error)==0)
		{		
			$date=time();

			$password=rawurlencode(getencrypt($_POST['password1'],$_FORUMCFG['chainecodage']));

			if ($_FORUMCFG['confirmparmail'] < 2)
			{
				$question	=	getformatmsg($_POST['question']);
				$reponse	=	getformatmsg($_POST['reponse']);
			}
			else
				$question	=	$reponse	=	NULLSTR;
				
				
			$query=$sql->query("INSERT INTO ".$_PRE."user (login,password,userstatus,registerdate,usermsg,usermail,timezone,lng) VALUES ('$rgpseudo','$password',2,'$date',0,'$regemail','".$_FORUMCFG['defaulttimezone']."','".$_FORUMCFG['defaultlangage']."')");
			$rguserid=mysql_insert_id();
				
			$query=$sql->query("INSERT INTO ".$_PRE."userplus(idplus,question,reponse,mailorig) VALUES ('$rguserid','$question','$reponse','$regemail')");
			updatemembers();
				
			$tpl->box['admcontent']=$tpl->gettemplate("adm_addmembre","registerok");
		}
		else
		{
			$tpl->box['errorbox']=$tpl->gettemplate("adm_addmembre","errorbox");
			$Result=$_POST;
			$_REQUEST['action'] = NULLSTR;
		}
}

if(empty($_REQUEST['action']))
{
	$tpl->box['isquestion'] = NULLSTR;
	
	if(strlen($error)>0)
		$tpl->box['errormsg']=$tpl->gettemplate("adm_addmembre","errorbox");
	if($_FORUMCFG['confirmparmail'] < 2)
		$tpl->box['isquestion']=$tpl->gettemplate("adm_addmembre","questrepform");
	$tpl->box['admcontent']=$tpl->gettemplate("adm_addmembre","registerform");

}


$cache.=$tpl->gettemplate("adm_addmembre","content");
require("bas.php");
