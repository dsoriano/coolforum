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

if(preg_match('|profile.php|',$_SERVER['PHP_SELF']) == 0)
{
	header('location: profile.php');
	exit;
}

getlangage("profile_mdp");

$tpl->box['error1'] = NULLSTR;
$tpl->box['error2'] = NULLSTR;

// ###### Navigation ######
$tpl->treenavs=$tpl->gettemplate("treenav","treeprofil");
$cache.=$tpl->gettemplate("treenav","hierarchy");

	if($_REQUEST['action']=="updatequestrep")
	{
		$quest=getformatmsg($_POST['quest'],false);
		$rep=getformatmsg($_POST['rep'],false);
		
		if(strlen($quest)>0 && strlen($rep)>0)
			$query=$sql->query("UPDATE "._PRE_."userplus SET question='%s', reponse='%s' WHERE idplus=%d", array($quest, $rep, $_USER['userid']))->execute();
			
		else
			$tpl->box['error2']=$tpl->gettemplate("profil_mdp","errorbox");
			
		$_REQUEST['action'] = NULLSTR;		
		
	}

	if($_REQUEST['action']=="updatemdp")
	{
		$query = $sql->query("SELECT password FROM "._PRE_."user WHERE userid=%d",$_USER['userid'])->execute();
		list($password)=$query->fetch_array();
		
		$realpass=getdecrypt(rawurldecode($password),$_FORUMCFG['chainecodage']);
		
		if($_POST['oldmdp']==$realpass && $_POST['newmdp1']==$_POST['newmdp2'])
		{
			$newpass=rawurlencode(getencrypt($_POST['newmdp1'],$_FORUMCFG['chainecodage']));
			
			$query=$sql->query("UPDATE "._PRE_."user SET password='%s' WHERE userid=%d", array($newpass, $_USER['userid']))->execute();
			
			$tpl->box['profilcontent']=$tpl->gettemplate("profil_mdp","majmdpok");
			$tpl->box['profilcontent'].=getjsredirect("identify.php",3000);
		}
		else
		{
			$tpl->box['error1']=$tpl->gettemplate("profil_mdp","majmdpnotok");
			$_REQUEST['action'] = NULLSTR;
		}
			
	}

	if(empty($_REQUEST['action']))
	{
		$tpl->box['profilcontent']=$tpl->gettemplate("profil_mdp","changemdpform");
		
		if($_FORUMCFG['confirmparmail'] < 2)
		{
			$query=$sql->query("SELECT question,reponse FROM "._PRE_."userplus WHERE idplus=%d",$_USER['userid'])->execute();
			
			if($query->num_rows()>0)
			{
				$Result=$query->fetch_array();
				$Result['question']=getformatrecup($Result['question']);
				$Result['reponse']=getformatrecup($Result['reponse']);
			}
			$tpl->box['profilcontent'].=$tpl->gettemplate("profil_mdp","questrepform");
		}	
	}
