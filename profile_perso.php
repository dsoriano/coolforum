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

getlangage("profile_perso");

$Error = NULLSTR;

// ###### Navigation ######
$tpl->treenavs=$tpl->gettemplate("treenav","treeprofil");
$cache.=$tpl->gettemplate("treenav","hierarchy");

if($_REQUEST['action']=="update")
{
	$Error			=		"";
	$birth_flag		=		false;
	
	if(strlen($_POST['msn'])>0 && !testemail($_POST['msn']))
		$Error=$tpl->attlang("error1");
	
	if(strlen($_POST['aim'])>16)
		$Error=$tpl->attlang("error2");
		
	if(strlen($_POST['yahoomsg'])>50)
		$Error=$tpl->attlang("error3");
		
	if((strlen($_POST['icq'])>0 && !is_numeric($_POST['icq'])) || (strlen($_POST['icq'])>9 && strlen($_POST['icq'])<8))
		$Error=$tpl->attlang("error4");
		
	if(intval($_POST['day']) > 0 || intval($_POST['month']) > 0 || intval($_POST['year']) > 0)
	{
		if((intval($_POST['year']) < 0 || strlen($_POST['year'])!=4) || (empty($_POST['day']) || intval($_POST['day']) < 0 || intval($_POST['day']) > 31) || (empty($_POST['month']) || intval($_POST['month']) < 0 || intval($_POST['month']) > 12))
			$Error=$tpl->attlang("error5");
		else
			$birth_flag	=	true;
	}
		
	if($_POST['sex']!="M" && $_POST['sex']!="F")
		$Error=$tpl->attlang("error6");
		
	testlength("description",$_USER['Max_Desc']);
	
	if(strlen($Error)==0)
	{
		if($birth_flag)
			$Birth = $_POST['day']."-".$_POST['month']."-".$_POST['year'];
		else
			$Birth = "";
		$yahoo = getformatmsg($_POST['yahoomsg'],false);
		$aim = getformatmsg($_POST['aim'],false);
		$Description = getformatmsg($_POST['description']);
		$query = $sql->query("UPDATE "._PRE_."userplus SET icq='%s',aim='%s',yahoomsg='%s',msn='%s', birth='%s', sex='%s', description='%s' WHERE idplus=%d", array($_POST['icq'], $aim, $yahoo, $_POST['msn'], $Birth, $_POST['sex'], $Description, $_USER['userid']))->execute();
		updatebirth();
	}
	else
		$tpl->box['error']=$tpl->gettemplate("profil_perso","errorbox");
	
	$_REQUEST['action'] = NULLSTR;;
}

if(empty($_REQUEST['action']))
{
	if(strlen($Error)==0)
	{
		$query=$sql->query("SELECT * FROM "._PRE_."userplus WHERE idplus=%d",$_USER['userid'])->execute();
		$Result=$query->fetch_array();
		$Result['description'] = getformatrecup($Result['description'],true);
		$tpl->box['error']	=	NULLSTR;
	}
	else
	{
		$Result=$_POST;

		$Result['day'] = intval($Result['day']);
		$Result['month'] = intval($Result['month']);
		$Result['year'] = intval($Result['year']);
		$Result['birth']=$Result['day']."-".$Result['month']."-".$Result['year'];
		
		$Result['msn'] = getrecupforform($Result['msn']);
		$Result['aim'] = getrecupforform($Result['aim']);
		$Result['yahoomsg'] = getrecupforform($Result['yahoomsg']);
		$Result['icq'] = getrecupforform($Result['icq']);
		$Result['sex'] = getrecupforform($Result['sex']);
		$Result['description'] = getrecupforform($Result['description']);
		
		$tpl->box['error'] = $tpl->gettemplate("profil_perso","errorbox");
	}
	
	$Birth=explode("-",$Result['birth']);
	
	$selectedd = array();
	$selectedm = array();
	
	for($i=1;$i<32;$i++)
	{
		$selectedd[$i]="";
		$selectedm[$i]="";
	}
	
	$selectedd[intval($Birth[0])]=" selected";
	$selectedm[intval($Birth[1])]=" selected";
	
	$tpl->box['day']=$tpl->gettemplate("profil_perso","annifday");
	$tpl->box['month']=$tpl->gettemplate("profil_perso","annifmonth");
	$tpl->box['year']=$Birth[2];
	
	if($Result['sex']=="M")
	{
		$sexM=" selected";
		$sexF="";
	}
	else
	{
		$sexF=" selected";
		$sexM="";
	}
	
	$tpl->box['sexe']=$tpl->gettemplate("profil_perso","sexbox");
	
	$tpl->box['profilcontent']=$tpl->gettemplate("profil_perso","infopersoform");
}
