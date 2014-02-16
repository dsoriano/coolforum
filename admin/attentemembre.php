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
getlangage("adm_attentemembre");

$tpl->box['error'] = NULLSTR;

if($_REQUEST['action']=="del")
{
	if(isset($_POST['todel']) && count($_POST['todel'])>0)
	{
		for($i=0;$i<count($_POST['todel']);$i++)
		{
			$trans=each($_POST['todel']);
			$query=$sql->query("DELETE FROM ".$_PRE."user WHERE userid=".$trans['value']);
			$query=$sql->query("DELETE FROM ".$_PRE."userplus WHERE idplus=".$trans['value']);
		}
		
		$query=$sql->query("OPTIMIZE TABLE ".$_PRE."user");
		updatemembers();
	}
	
	if(isset($_POST['tovalid']) && count($_POST['tovalid'])>0)
	{
		for($i=0;$i<count($_POST['tovalid']);$i++)
		{
			$trans 		= 	each($_POST['tovalid']);
			
			if($_FORUMCFG['confirmparmail'] == 2)
			{
				$query	=	$sql->query("SELECT login, password, usermail FROM ".$_PRE."user WHERE userid=".$trans['value']);
				list($username, $userpass, $mail) = mysql_fetch_array($query);
				
				$username	=	formatstrformail($username);
				
				$userpass	=	rawurldecode($userpass);
				$userpass	=	getdecrypt($userpass, $_FORUMCFG['chainecodage']);
				$userpass	=	formatstrformail(stripslashes(recupDBforMail($userpass)));
				
				$forumname	=	$_FORUMCFG['mailforumname'];
				
				eval("\$subject = ".$tpl->attlang("mailsujet").";");
				eval("\$msg = ".$tpl->attlang("mailmsg").";");
				
				if(!sendmail($mail,$subject,$msg))
				{
					$tpl->box['error']=$tpl->gettemplate("adm_attentemembre","errormail");
					break;
				}
				
			}
			
			if(strlen($tpl->box['error']) == 0)
				$query = $sql->query("UPDATE ".$_PRE."user SET userstatus=2 WHERE userid=".$trans['value']);
		}
		updatemembers();
	}
	
	$_REQUEST['action'] = NULLSTR;;
}

if(empty($_REQUEST['action']))
{
	
	$query=$sql->query("SELECT userid,login,registerdate FROM ".$_PRE."user WHERE userstatus=0 ORDER BY registerdate");
	$nb=mysql_num_rows($query);
	
	if($nb==0)
		$tpl->box['listmember']=$tpl->gettemplate("adm_attentemembre","ifnoattmb");
	else
	{
		$tpl->box['listmember']="";
		$i=0;
		while($Mb=mysql_fetch_array($query))
		{
			$Mb['login']=getformatrecup($Mb['login']);
			$Mb['registerdate']=getlocaltime($Mb['registerdate']);
			$tpl->box['listmember'].=$tpl->gettemplate("adm_attentemembre","lignemember");
			$i++;
		}			
	}	
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_attentemembre","listmembre");
}

$cache.=$tpl->gettemplate("adm_attentemembre","content");
require("bas.php");

