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
getlangage("adm_ban");

if($_REQUEST['action']=="deban")
{
	$query=$sql->query("UPDATE "._PRE_."user SET userstatus=-userstatus WHERE userid=".$_GET['idmb']);
	$query=$sql->query("DELETE FROM "._PRE_."banlist WHERE userid=".$_GET['idmb']);
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="ban")
{
	$query=$sql->query("UPDATE "._PRE_."user SET userstatus=-userstatus WHERE userid=".$_GET['idmb']);
	$query=$sql->query("SELECT "._PRE_."user.userid,"._PRE_."user.login,"._PRE_."user.usermail,"._PRE_."userplus.mailorig FROM "._PRE_."user LEFT JOIN "._PRE_."userplus ON "._PRE_."user.userid="._PRE_."userplus.idplus WHERE userid=".$_GET['idmb']);
	$j=mysql_fetch_array($query);
	$query=$sql->query("INSERT INTO "._PRE_."banlist (userid,login,mail1,mail2) VALUES ('".$j['userid']."','".$j['login']."','".$j['usermail']."','".$j['mailorig']."')");
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="search")
{
	$tpl->box['lignemembre']=NULLSTR;
	if(strlen($_POST['pseudo'])==0)
	{
		$Error=$tpl->attlang("errorpseudo1");
		$_REQUEST['action'] = NULLSTR;
	}
	
	else
	{	
		$pseudo = getformatmsg($_POST['pseudo']);
		$query=$sql->query("SELECT * FROM "._PRE_."user WHERE login LIKE \"%$pseudo%\" ORDER BY login");
		$nb=mysql_num_rows($query);
		if($nb==0)
			$Error=$tpl->attlang("errorpseudo2");
		else
		{
			while($Noms=mysql_fetch_array($query))
				$tpl->box['lignemembre'] .= $tpl->gettemplate("adm_ban","lignemembre");

			$tpl->box['admcontent']=$tpl->gettemplate("adm_ban","memberchoice");	
		}
	}
	
	if(strlen($Error)>0)
		$_REQUEST['action'] = NULLSTR;

	/*if(strlen($_POST[pseudo])==0)
	{
		$error="<B>Vous devez entrer au moins une lettre!</B><P>";
		unset($_REQUEST[action]);
	}
	
	else
	{
		$pseudo=getformatmsg($_POST[pseudo],false);	
		echo("<B>Recherche sur : $pseudo</B><P>");
		$sql=mysql_query("SELECT * FROM CF_user WHERE login LIKE \"%$pseudo%\" ORDER BY login");
		$nb=mysql_num_rows($sql);
		if($nb==0)
			echo("<B>Il n'y a pas de résultat à votre recherche</B><P>");
		else
		{
			echo("<B>Il y a $nb résultat à votre recherche:</B><P>");
			echo("<TABLE border=1 bordercolorlight=$_SKIN[colorborder1] bordercolordark=$_SKIN[colorborder2] cellpadding=2 cellspacing=0 width=50%>");
			echo("<TR bgcolor=$_SKIN[color1]><TD class=jaune align=center><font size=1><B>Pseudo</B></font></TD><TD class=jaune align=center><font size=1><B>Bannir</B></font></TD></TR>");
			
			while($j=mysql_fetch_array($sql))
			{
				echo("<TR><TD bgcolor=$_SKIN[color2] class=jaune><font size=1>".stripslashes($j[login])."</font></TD><TD class=jaune align=center><font size=1><a href=\"ban.php?action=ban&idmb=".$j[userid]."\" class=men>bannir</A></font></TD></TR>");
					
			}
			echo("</table><P>");	
		}
	}*/
}

if(empty($_REQUEST['action']))
{
	$tpl->box['listmember'] = NULLSTR;
	$query=$sql->query("SELECT userid,login,userstatus FROM "._PRE_."user WHERE userstatus<0 ORDER BY login");
	$nb=mysql_num_rows($query);
	
	if($nb==0)
		$tpl->box['listmember']=$tpl->gettemplate("adm_ban","ifnobanmb");
	else
		while($Mb=mysql_fetch_array($query))
		{
			$Mb['login']=getformatrecup($Mb['login']);
			$tpl->box['listmember'].=$tpl->gettemplate("adm_ban","lignemember");
		}	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_ban","listmembre");
}

$cache.=$tpl->gettemplate("adm_ban","content");
require("bas.php");

