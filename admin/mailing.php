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
getlangage("adm_mailing");

if($_REQUEST['action']=="savemailing")
{
	$error = "";
	
	// **** Test du titre ****
	$testchain=preg_replace("/([\s]{1,})/","",$_POST['Titre']);
	if(strlen($testchain)==0)
		$error=$tpl->attlang("badtitre");

	// **** Test du message ****
	$testchain=preg_replace("/([\s]{1,})/","",$_POST['Message']);
	if(strlen($testchain)==0)
		$error=$tpl->attlang("badmsg");

	if(strlen($error)==0)
	{		
		$Titre = getformatmsg($_POST['Titre']);
		$Message = getformatmsg($_POST['Message']);
		$Date = time();
		
		$query = $sql->query("INSERT INTO "._PRE_."mailing (date,titre,message) VALUES ('$Date','$Titre','$Message')");
		
		$_GET['id'] = mysql_insert_id();
		
		$_REQUEST['action'] = "send";
	}
	else
	{
		$Titre = getrecupforform($_POST['Titre']);
		$Message = getrecupforform($_POST['Message']);
		
		$tpl->box['error'] = $tpl->gettemplate("adm_mailing","error");
		
		$_REQUEST['action'] = NULLSTR;
	}
}

if($_REQUEST['action']=="send")
{
	$id = intval($_GET['id']);
	
	// **** définition des limites par boucle ****
	$Limite = 30;
	
	if(isset($_GET['debut']))
		$debut = intval($_GET['debut']);
	else
		$debut = 0;
		
	$fin = $debut + $Limite;

	// **** Sélection du message à envoyer ****
	$query = $sql->query("SELECT * FROM "._PRE_."mailing WHERE id='$id'");
	$Mail = mysql_fetch_array($query);
	
	$Titre = formatstrformail(recupDBforMail($Mail['titre']));
	$Message = strip_tags(formatstrformail(recupDBforMail($Mail['message'])));
	
	$forumname	=	$_FORUMCFG['mailforumname'];
	
	eval("\$Footer = ".$tpl->attlang("mailfooter").";");
	$Message .= $Footer;
	
	// **** Nombre de mails au total ****
	$query = $sql->query("SELECT COUNT(*) AS nbmail FROM "._PRE_."user WHERE mailing='Y' AND userstatus > 0");
	//$query = $sql->query("SELECT COUNT(*) AS nbmail FROM "._PRE_."user WHERE userid = 1");

	list($nbmail) = mysql_fetch_array($query);
	
	// **** Envoi des emails ****
	$query = $sql->query("SELECT usermail FROM "._PRE_."user WHERE mailing='Y' AND userstatus > 0 ORDER BY userid LIMIT $debut,$Limite");
	//$query = $sql->query("SELECT usermail FROM "._PRE_."user WHERE userid = 1 ORDER BY userid LIMIT $debut,$fin");

	while(list($To) = mysql_fetch_array($query))
		sendmail($To,$Titre,$Message);
	
	// **** Rechargement page ou fin ****
	if($fin < $nbmail)
	{
		header("location: mailing.php?action=send&id=".$id."&debut=".$fin);
		exit;
	}
	else
		$tpl->box['admcontent'] = $tpl->gettemplate("adm_mailing","endmailing");	
	
}

if(empty($_REQUEST['action']))
{
	$tpl->box['error']		=	NULLSTR;
	$tpl->box['admcontent'] = $tpl->gettemplate("adm_mailing","mailingform");
	
}

$cache.=$tpl->gettemplate("adm_mailing","content");
require("bas.php");
