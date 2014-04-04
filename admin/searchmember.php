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
getlangage("adm_searchmember");

$tpl->box['errorbox']	=	NULLSTR;
$error1					=	NULLSTR;
$error2					=	NULLSTR;

$_LOGO = explode("-",$_FORUMCFG['logos']); // Array: active - upload - gallerie - externe - largeur - hauteur - poids

if($_REQUEST['action']=="newpass")
{
	
	//**** Génération Aléatoire du mot de passe ****
	srand((float)microtime()*1000000);
	
	$NewPass=array();
	
	$Alphabet=array();
	for($i=65;$i<91;$i++)
		$Alphabet[]=chr($i);
		
		
	for($i=97;$i<123;$i++)
		$Alphabet[]=chr($i);	
	
	$Expr = array(".",
		      "-",
		      "_",
		      "!",
		      "&",
		      "+",
		      "*",
		      "?");
	
	$z=0;
		      
	for($i=0;$i<4;$i++)
	{
		$z=rand(0,count($Alphabet));
		$NewPass[]=$Alphabet[$z];	
	}
	
	$z = rand(0,7);
	$NewPass[] = $Expr[$z];
	
	for($i=0;$i<3;$i++)
		$NewPass[] = rand(0,9);
		
	$FinalPass = implode("",$NewPass);
	
	//**** encryption / mise à jour / affichage du nouveau Pass ****
	$password=rawurlencode(getencrypt($FinalPass,$_FORUMCFG['chainecodage']));
	$query=$sql->query("UPDATE "._PRE_."user SET password='$password' WHERE userid=".$_GET['id']);
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","affnewpass");
	
}

if($_REQUEST['action']=="delmember")
{
	if($_POST['msg']=="delete")
	{
		//**** Mise à jour du nombre de réponses des sujets ****
		$query=$sql->query("SELECT parent,COUNT(*) AS nbposts FROM "._PRE_."posts WHERE idmembre=%d GROUP BY parent", $_POST[id])->execute();
		$nb=$query->num_rows();
		
		if($nb>0)
			while($j=$query->fetch_array())
				$MajTopics = $sql->query("UPDATE "._PRE_."topics SET nbrep = nbrep-".$j['nbposts']." WHERE idtopic = ".$j['parent']);
		
		//**** Suppression des messages ****
		$query=$sql->query("DELETE FROM "._PRE_."posts WHERE idmembre=%d",$_POST['id'])->execute();
	
		
		//**** Suppression des sujets ****
		$query=$sql->query("DELETE FROM "._PRE_."topics WHERE idmembre=%d",$_POST['id'])->execute();
		
		//**** Mise à jour du dernier posteur des topics ****
		$query=$sql->query("SELECT login FROM "._PRE_."user WHERE userid=%d",$_POST['id'])->execute();
		list($login)=$query->fetch_array();
	
		$login = getformatdbtodb($login);
		$query=$sql->query("SELECT idtopic FROM "._PRE_."topics WHERE derposter='%s'", $login)->execute();
		$nb=$query->num_rows();
		
		if($nb>0)
			while(list($idtopic)=$query->fetch_array())
				updatetopiclastposter($idtopic);
	
		//**** Mise à jour des forums ****
		$selectforums=$sql->query("SELECT forumid FROM "._PRE_."forums ORDER BY forumid")->execute();
		
		if($selectforums->num_rows()>0)
		{
			while($forumss=$selectforums->fetch_array())
			{
				updateforumlastposter($forumss['forumid']);
			}
		}
	}
	else
	{
		$query=$sql->query("UPDATE "._PRE_."posts SET idmembre='0' WHERE idmembre=%d",$_POST['id'])->execute();
		$query=$sql->query("UPDATE "._PRE_."topics SET idmembre='0' WHERE idmembre=%d",$_POST['id'])->execute();
	}
	
	//**** Table des bannis ***		
	$query=$sql->query("DELETE FROM "._PRE_."banlist WHERE userid=%d",$_POST['id'])->execute();
	
	//**** Table des moderateurs ****		
	$query=$sql->query("DELETE FROM "._PRE_."moderateur WHERE idusermodo=%d",$_POST['id'])->execute();
	
	//**** Table des messages privés ****
	$query=$sql->query("DELETE FROM "._PRE_."privatemsg WHERE iddest=%d OR idexp=%d", array($_POST['id'],$_POST['id']))->execute();

	//**** Table des membres ****		
	$query=$sql->query("DELETE FROM "._PRE_."user WHERE userid=%d",$_POST['id'])->execute();
	$query=$sql->query("DELETE FROM "._PRE_."userplus WHERE idplus=%d",$_POST['id'])->execute();
	
	updatenbtopics();
	updatenbposts();
	updatemembers();

	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="confdelmb")
{
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","confdelmb");	
}

if($_REQUEST['action']=="updatemember")
{
	$error1="";

	//**** test du pseudo ****
	if($_POST['userlogin'] != $_POST['pseudoorig'])
	{
		$testchain	=	preg_replace("/([\s]{1,})/","",$_POST['userlogin']);
		if(strlen($testchain)==0)
			$error1	=	$tpl->attlang("errorpseudo1");
				
		$rgpseudo	=	trim($_POST['userlogin']);
		$rgpseudo	=	getformatmsg($rgpseudo,false);
		$query		=	$sql->query("SELECT COUNT(*) AS nbpseudos FROM "._PRE_."user WHERE login='%s' AND userid<>%d", array($rgpseudo, $_POST['id']))->execute();
		list($nbpseudos)=$query->fetch_array();
		if ($nbpseudos>0)
			$error1	=	$tpl->attlang("errorpseudo2");
	}
	
	//**** test de l'email ****
	if(!testemail($_POST['usermail']))
		$error1=$tpl->attlang("errormail");		
	
	//**** formattage du skin ****
	if($_USER['userskin'] != $_POST['skin'])	$skin	=	intval($_POST['skin']);
		else				$skin	=	$_USER['userskin'];
		
	//**** upload / enregistrement du logo ****
	$filename = NULLSTR;
	
	if(isset($_POST['deletelogo']) && $_POST['deletelogo']=="Y")
		$filename="";
	else
	{
		if(isset($_POST['infologo']) && preg_match("|^[a-zA-Z0-9_\.-]+$|",$_POST['infologo']) > 0 && $_LOGO[2]=="Y")
			$filename=$_POST['infologo'];
		elseif(isset($_POST['extlogo']) && strlen($_POST['extlogo'])>0 && $_LOGO[3]=="Y")
		{
			$Size = @getimagesize($_POST['extlogo']);
			
			if($Size && preg_match("'^(http|ftp|https):\/\/([a-zA-Z0-9-\/\.@:%~_])+(.gif|.jpg|.jpeg|.png)$'",$_POST['extlogo']) && ($Size[2]=="1" || $Size[2]=="2" || $Size[2]=="3"))
			{
				if($Size[0]<($_LOGO[4]+1) && $Size[1]<($_LOGO[5]+1))
					$filename="\"".$_POST['extlogo']."\" ".$Size[3];
				else
					$error=$tpl->attlang("logoerror2");
					
			}
			else
				$error=$tpl->attlang("logoerror3");
		}
		elseif(!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['tmp_name']<>"none" && $_LOGO[1]=="Y")
		{
			$taille=GetImageSize($_FILES['logo']['tmp_name']);
			if (($taille[0]<($_LOGO[4]+1)) && ($taille[1]<($_LOGO[5]+1)))
			{
				if((($_FILES['logo']['type']=="image/pjpeg") || ($_FILES['logo']['type']=="image/jpeg") || ($_FILES['logo']['type']=="image/gif")) && ($_FILES['logo']['size']<($_LOGO['6']*1024)))
				{
					if ($_FILES['logo']['type']=="image/pjpeg" || $_FILES['logo']['type']=="image/jpeg")
						$ext=".jpg";
					elseif ($_FILES['logo']['type']=="image/gif")
						$ext=".gif";
					$filename="logo".$_POST['id'].$ext;
					move_uploaded_file($_FILES['logo']['tmp_name'],"../logos/".$filename);
					@chmod("../logos/".$filename, 0777);
				}
				else
					$error=$tpl->attlang("logoerror1");
			}
			else
				$error=$tpl->attlang("logoerror2");
		}
		else
		{
			$query 	= 	$sql->query("SELECT userlogo FROM "._PRE_."user WHERE userid=%d",$_POST['id'])->execute();
			$j	=	$query->fetch_array();
	
			if(!empty($j['userlogo']))
				$filename=$j['userlogo'];
		}
	}
	
	if(strlen($error1)==0)
	{
		if($_POST['userlogin'] != $_POST['pseudoorig'])
		{
			$query = $sql->query("UPDATE "._PRE_."user SET login='%s' WHERE userid=%d", array($rgpseudo, $_POST['id']))->execute();
			$query = $sql->query("UPDATE "._PRE_."banlist SET login='%s' WHERE userid=%d", array($rgpseudo, $_POST['id']))->execute();
			$query = $sql->query("UPDATE "._PRE_."forums SET lastforumposter='%s' WHERE lastforumposter='%s'", array($rgpseudo, getformatmsg($_POST['pseudoorig'],false)))->execute();
			$query = $sql->query("UPDATE "._PRE_."moderateur SET modologin='%s' WHERE idusermodo=%d", array($rgpseudo, $_POST['id']))->execute();
			$query = $sql->query("UPDATE "._PRE_."posts SET pseudo='%s' WHERE idmembre=%d", array($rgpseudo, $_POST['id']))->execute();
			$query = $sql->query("UPDATE "._PRE_."privatemsg SET pseudo='%s' WHERE idexp=%d", array($rgpseudo, $_POST['id']))->execute();
			$query = $sql->query("UPDATE "._PRE_."topics SET pseudo='%s' WHERE idmembre=%d", array($rgpseudo, $_POST['id']))->execute();
			$query = $sql->query("UPDATE "._PRE_."topics SET derposter='%s' WHERE derposter='%s'", array($rgpseudo, getformatmsg($_POST['pseudoorig'],false)))->execute();
		}
		
		//*** formattage variables diverses ***
		$site		=	getformatmsg($_POST['usersite'],false);		
		$citation	=	getformatmsg($_POST['usercitation'],false);
		$sign		=	getformatmsg($_POST['usersign']);
		$lng		=	getformatmsg($_POST['lng'],false);
			
		$timezone	=	intval($_POST['timezone']);
	
		if($_POST['showmail']=="N")	$showmail	=	"N";
			else			$showmail	=	"Y";
	
		if($_POST['showusersite']=="N")	$showusersite	=	"N";
			else			$showusersite	=	"Y";
	
		if(isset($_POST['notifypm']) && $_POST['notifypm']=="N")	$notifypm	=	"N";
			else													$notifypm	=	"Y";
		
		if(isset($_POST['popuppm']) && $_POST['popuppm']=="N")		$popuppm	=	"N";
			else													$popuppm	=	"Y";

		if(isset($_POST['mailing']) && $_POST['mailing']=="N")		$mailing	=	"N";
			else													$mailing	=	"Y";

		if(isset($_POST['wysiwyg']) && $_POST['wysiwyg']=="N")		$wysiwyg	=	"N";
			else													$wysiwyg	=	"Y";
		
		if($_POST['userstatus']=="-1")	$newuserstatus = "-userstatus";
		else				$newuserstatus = "'".$_POST['userstatus']."'";
		
		$query = $sql->query("UPDATE "._PRE_."user SET userstatus=%d, usermail='%s',usersite='%s', showmail='%s', showusersite='%s', usersign='%s',usercitation='%s', userlogo='%s', skin='%s', timezone='%s', lng='%s', notifypm='%s', popuppm='%s', mailing='%s', wysiwyg='%s'  WHERE userid=%d", array($newuserstatus, $_POST['usermail'], $site, $showmail, $showusersite, $sign, $citation, $filename, $skin, $timezone, $lng, $notifypm, $popuppm, $mailing, $wysiwyg, $_POST['id']))->execute();

		//$tpl->box[profilcontent].=$tpl->gettemplate("profil_options","changeok");
		//$tpl->box[profilcontent].=getjsredirect("profile.php?p=profile",2000);
	}
	else
	{
		$Error = $error1;
		$tpl->box['error']=$tpl->gettemplate("adm_searchmember","errorbox");
	}
		
	$_REQUEST['action']="detailmb";
	$Id = $_POST['id'];	
}

if($_REQUEST['action']=="updateinfocomp")
{
	$error2="";
	
	if(strlen($_POST['msn'])>0 && !testemail($_POST['msn']))
		$error2=$tpl->attlang("error1");
	
	if(strlen($_POST['aim'])>16)
		$error2=$tpl->attlang("error2");
		
	if(strlen($_POST['yahoomsg'])>50)
		$error2=$tpl->attlang("error3");
		
	if((strlen($_POST['icq'])>0 && !is_numeric($_POST['icq'])) || (strlen($_POST['icq'])>9 && strlen($_POST['icq'])<8))
		$error2=$tpl->attlang("error4");
		
	if(strlen($_POST['year'])>0 && (strlen($_POST['year'])!=4 || !is_numeric($_POST['year'])))
		$error2=$tpl->attlang("error5");
		
	if($_POST['sex']!="M" && $_POST['sex']!="F")
		$error2=$tpl->attlang("error6");
	
	
	if(strlen($error2)==0)
	{
		$Birth = $_POST['day']."-".$_POST['month']."-".$_POST['year'];
		$yahoo = getformatmsg($_POST['yahoomsg'],false);
		$aim = getformatmsg($_POST['aim'],false);
		$description = getformatmsg($_POST['description']);
		$query = $sql->query("UPDATE "._PRE_."userplus SET icq='%s',aim='%s',yahoomsg='%s',msn='%s', birth='%s', sex='%s', description = '%s' WHERE idplus=%d", array($_POST['icq'], $aim, $yahoo, $_POST['msn'], $Birth, $_POST['sex'], $description, $_POST['id']))->execute();
		updatebirth();
	}
	else
	{
		$Error = $error2;
		$tpl->box['error2']=$tpl->gettemplate("adm_searchmember","errorbox");
	}
	
	$_REQUEST['action']="detailmb";
	$Id = $_POST['id'];	
	
}

if($_REQUEST['action']=="detailmb")
{
	$tpl->box['groups_list']	=	NULLSTR;
	$tpl->box['error']			=	NULLSTR;
	$tpl->box['error2']			=	NULLSTR;
	
	$timezn = array();
	array_rempl($timezn,0,24,NULLSTR);
	
	if(!isset($Id))
		$Id = intval($_GET['id']);
		
	if(strlen($error1)==0)
	{
		$query			=	$sql->query("SELECT 	"._PRE_."user.login,
									"._PRE_."user.userstatus,
									"._PRE_."user.usermsg,
									"._PRE_."user.usermail,
									"._PRE_."user.usersite,
									"._PRE_."user.usersign,
									"._PRE_."user.usercitation,
									"._PRE_."user.showmail,
									"._PRE_."user.showusersite,
									"._PRE_."user.userlogo,
									"._PRE_."user.skin,
									"._PRE_."user.timezone,
									"._PRE_."user.lng,
									"._PRE_."user.notifypm,
									"._PRE_."user.popuppm,
									"._PRE_."user.mailing,
									"._PRE_."user.wysiwyg,
									"._PRE_."groups.*
									 FROM "._PRE_."user LEFT JOIN "._PRE_."groups ON "._PRE_."user.userstatus="._PRE_."groups.id_group
									 WHERE userid=%d", $Id)->execute();
		$Result			=	$query->fetch_array();
		
		$Result['login']		=	getformatrecup($Result['login']);
		$Result['usercitation']	=	getformatrecup($Result['usercitation']);
		$Result['usersign']	=	getformatrecup($Result['usersign'],true);
	}
	else
	{
		//$tpl->box[error]	=	$tpl->gettemplate("adm_searchmember","errorbox");
		$Result			=	$_POST;
		$Result['userlogo']	=	$filename;
		$Result['login']     	=       getformatdbtodb($_POST['pseudoorig']);
	}
	
	//**** recherche du status ****
	$userstat=array();
	if($Result['userstatus']<0)		$userstat['-1']=" SELECTED";
	else							$userstat['-1']=NULLSTR;
	
	if($Result['userstatus']==0)	$userstat[0]=" SELECTED";
	else							$userstat[0]=NULLSTR;
	
	$query = $sql->query("SELECT id_group,Nom_group from "._PRE_."groups WHERE id_group>1 ORDER BY id_group")->execute();
	while($LstGrp=$query->fetch_array())
	{
		$userstat[2] = "";
		if($LstGrp['id_group']==$Result['userstatus'])
			$userstat[2] = " SELECTED";
		$tpl->box['groups_list'] .= $tpl->gettemplate("adm_searchmember","groups_list");	
	}
		
	//**** sélection du timezone ****
	$timezn[$Result['timezone']+12]=" SELECTED";

	//**** email visible? ****
	if ($Result['showmail']=="Y")
	{
		$Result['checkmailY']	=	"CHECKED";
		$Result['checkmailN']	=	NULLSTR;
	}
	else
	{
		$Result['checkmailN']	=	"CHECKED";
		$Result['checkmailY']	=	NULLSTR;
	}

	//**** site web visible? ****
	if ($Result['showusersite']=="Y")
	{
		$Result['checksiteY']	=	"CHECKED";
		$Result['checksiteN']	=	NULLSTR;
	}
	else
	{
		$Result['checksiteN']	=	"CHECKED";
		$Result['checksiteY']	=	NULLSTR;
	}

	//**** bbcode autorisé dans signature? ****
	$tpl->box['whatis'] 						= 	$tpl->attlang("bbcodeare");
	if($_FORUMCFG['bbcodeinsign']=="Y")	$tpl->box['yesorno']	=	$tpl->attlang("allow1");
		else				$tpl->box['yesorno']	=	$tpl->attlang("disabled1");		
	$Result['canusebbcode']						=	$tpl->gettemplate("adm_searchmember","isallowed");

	//**** smileys autorisés dans signature? ****
	$tpl->box['whatis'] 						= 	$tpl->attlang("smileysare");
	if($_FORUMCFG['smileinsign']=="Y")	$tpl->box['yesorno']	=	$tpl->attlang("allow2");
		else				$tpl->box['yesorno']	=	$tpl->attlang("disabled2");		
	$Result['canusesmileys']						=	$tpl->gettemplate("adm_searchmember","isallowed");		
		
	//**** notification pour pm? ****	
	if($_FORUMCFG['mailnotify']=="Y")
	{	
		if ($Result['notifypm']=="Y")
		{
			$Result['checknotifypmY'] 	=	"CHECKED";
			$Result['checknotifypmN']	=	NULLSTR;
		}
		else
		{
			$Result['checknotifypmN']	=	"CHECKED";
			$Result['checknotifypmY'] 	=	NULLSTR;
		}
		$tpl->box['mailnotify']=$tpl->gettemplate("adm_searchmember","notifyok");
	}
	else	$tpl->box['mailnotify']=NULLSTR;
	
	//**** popup pour pm? ****
	if ($Result['popuppm']=="Y")
	{
		$Result['checkpopuppmY'] =	"CHECKED";
		$Result['checkpopuppmN'] =	NULLSTR;
	}
	else
	{
		$Result['checkpopuppmN'] =	"CHECKED";
		$Result['checkpopuppmY'] =	NULLSTR;
	}
		
	//**** les logos sont-ils activés ?? ****
	if($_LOGO[0]=="Y")
	{		
		//**** peut-on uploader des logos? ****
		if($_LOGO[1]=="Y")	$tpl->box['persologo']		=	$tpl->gettemplate("adm_searchmember","uploadlogobox");
		else				$tpl->box['persologo']		=	NULLSTR;
		
		//**** peut-on utiliser la collection de logos? ****
		if($_LOGO[2]=="Y")	$tpl->box['defaultlogo']	=	$tpl->gettemplate("adm_searchmember","logocollection");
		else				$tpl->box['defaultlogo']	=	NULLSTR;
		
		//**** peut-on indiquer une url externe ? ****
		if($_LOGO[3]=="Y")	$tpl->box['extlogo']		=	$tpl->gettemplate("adm_searchmember","extlogo");
		else				$tpl->box['extlogo']		=	NULLSTR;
		
		//**** affichage du logo courant ****
		if(!empty($Result['userlogo']))
		{
			if(preg_match("|^\"http://|",$Result['userlogo']) > 0 && $_LOGO[3]=="Y")
				$tpl->box['logo'] = $tpl->gettemplate("adm_searchmember","affextavatar");
			elseif(preg_match("|^\"http://|",$Result['userlogo']) == 0)
				$tpl->box['logo'] = $tpl->gettemplate("adm_searchmember","affavatar");
		}
		else	$tpl->box['logo'] = $tpl->attlang("nologonow");	
					
		//**** chargement du template ****
		$tpl->box['logotpl']=$tpl->gettemplate("adm_searchmember","logotpl");	
		
	}
	else
		$tpl->box['logotpl']=NULLSTR;

	// **** option mailing ****
	if($_FORUMCFG['usemails']=="Y")
	{
		if ($Result['mailing']=="Y")
		{
			$Result['mailingY'] =	"CHECKED";
			$Result['mailingN'] =	NULLSTR;
		}
		else
		{
			$Result['mailingN'] =	"CHECKED";
			$Result['mailingY'] =	NULLSTR;
		}
						
		$tpl->box['mailing'] = $tpl->gettemplate("adm_searchmember","mailing");
	}

	// **** interface WYSIWYG ? ****
	if($Result['wysiwyg'] == "Y")
	{
		$Result['wysiwygY'] = 	"CHECKED";
		$Result['wysiwygN'] = 	NULLSTR;
	}
	else
	{
		$Result['wysiwygN'] = 	"CHECKED";
		$Result['wysiwygY'] = 	NULLSTR;
	}
		
	//**** affichage du skin utilisé ****
	$tpl->box['skinlist']	=	"";	
	$query			=	$sql->query("SELECT * FROM "._PRE_."skins WHERE propriete='skinname'")->execute();
	while($j=$query->fetch_array())
	{
		$selected	=	"";
		if($Result['skin']==$j['id'])	$selected=" SELECTED";
			
		$tpl->box['skinlist'].=$tpl->gettemplate("adm_searchmember","skinlist");
	}
		
	//**** sélection de la langue ****
	$tpl->box['lnglist']	=	"";
	$query			=	$sql->query("SELECT * FROM "._PRE_."language")->execute();
	while($j=$query->fetch_array())
	{
		$selected	=	"";
		if($Result['lng']==$j['code'])	$selected=" SELECTED";
			
		$tpl->box['lnglist'].=$tpl->gettemplate("adm_searchmember","lnglist");
	}
		
		
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","detailmembre");
	
	//**** Affichage des infos complémentaires ****
	
	if(strlen($error2)==0)
	{
		$query=$sql->query("SELECT * FROM "._PRE_."userplus WHERE idplus=%d", $Id)->execute();
		$Results=$query->fetch_array();
		$Results['description'] = getformatrecup($Results['description'],true);
	}
	else
	{
		$Results=$_POST;
		
		$Results['day'] = intval($Results['day']);
		$Results['month'] = intval($Results['month']);
		$Results['year'] = intval($Results['year']);
		$Results['birth']=$Results['day']."-".$Results['month']."-".$Results['year'];
		
		$Results['msn'] = getrecupforform($Results['msn']);
		$Results['aim'] = getrecupforform($Results['aim']);
		$Results['yahoomsg'] = getrecupforform($Results['yahoomsg']);
		$Results['icq'] = getrecupforform($Results['icq']);
		$Results['sex'] = getrecupforform($Results['sex']);
		$Results['description'] = getrecupforform($Results['description']);
	}
	
	$Birth=explode("-",$Results['birth']);
	
	$selectedd = array();
	$selectedm = array();
	
	for($i=1;$i<32;$i++)
	{
		$selectedd[$i]="";
		$selectedm[$i]="";
	}
	
	if(isset($Birth[0]))	$selectedd[intval($Birth[0])]=" selected";
	else						$selectedd[0] = NULLSTR;

	if(isset($Birth[1]))	$selectedm[intval($Birth[1])]=" selected";
	else						$selectedm[0] = NULLSTR;
	
	$tpl->box['day']=$tpl->gettemplate("adm_searchmember","annifday");
	$tpl->box['month']=$tpl->gettemplate("adm_searchmember","annifmonth");
	
	if(isset($Birth[2]))		$tpl->box['year']=$Birth[2];
	else						$tpl->box['year']=NULLSTR;
	
	if($Results['sex']=="M")
	{
		$sexM=" selected";
		$sexF="";
	}
	else
	{
		$sexF=" selected";
		$sexM="";
	}
	
	$tpl->box['sexe']=$tpl->gettemplate("adm_searchmember","sexbox");
	
	$tpl->box['admcontent'].=$tpl->gettemplate("adm_searchmember","infopersoform");
		
}

if($_REQUEST['action']=="searchbypseudo")
{
	$error = "";
	
	if(strlen($_POST['pseudo'])==0)
	{
		$error=$tpl->attlang("errorpseudo"); //"<B>Vous devez entrer au moins une lettre!</B><P>";
		$_REQUEST['action'] = NULLSTR;
	}
	
	else
	{
		$pseudo=getformatmsg($_POST['pseudo'],false);
		$pseudo = addslashes($pseudo);
		$query=$sql->query("SELECT "._PRE_."user.*, "._PRE_."groups.Nom_group FROM "._PRE_."user LEFT JOIN "._PRE_."groups ON "._PRE_."groups.id_group = "._PRE_."user.userstatus WHERE "._PRE_."user.login LIKE \"%%%s%%\" ORDER BY "._PRE_."user.login", $pseudo)->execute();
		$nb=$query->num_rows();
		if($nb==0)
		{
			$error=$tpl->attlang("pseudonotfound");
			$_REQUEST['action'] = NULLSTR;
		}
		else
		{
			$tpl->box['pseudolist']="";
			while($LnPseudo=$query->fetch_array())
			{
				$LnPseudo['login'] = getformatrecup($LnPseudo['login']);
				$LnPseudo['registerdate']=getlocaltime($LnPseudo['registerdate']);
				$LnPseudo['usermail']=getemail($LnPseudo['usermail']);
				
				if($LnPseudo['userstatus'] < 0)
					$LnPseudo['status']=$tpl->attlang("mbbanned");
				elseif($LnPseudo['userstatus'] == 0)
					$LnPseudo['status']=$tpl->attlang("mbwait");
				else
					$LnPseudo['status']=getformatrecup($LnPseudo['Nom_group']);
					
				$tpl->box['pseudolist'].=$tpl->gettemplate("adm_searchmember","structlignepseudo");
			}
			
			$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","structtablepseudo");	
		}
	}
}

if($_REQUEST['action']=="affichetout")
{
	switch($_REQUEST['sortby'])
	{
	case "1":
		$ajout=" ORDER BY "._PRE_."user.login ";
		break;
	case "2":
		$ajout=" ORDER BY "._PRE_."user.registerdate ";
		break;
	case "3":
		$ajout=" ORDER BY "._PRE_."user.usermsg DESC ";
		break;
	case "4":
		$ajout=" WHERE "._PRE_."user.userstatus='0' ORDER BY "._PRE_."user.login ";
		break;
	case "5":
		$ajout=" WHERE "._PRE_."user.userstatus<'0' ORDER BY "._PRE_."user.login ";
		break;
	default :
		$ajout=" WHERE "._PRE_."user.userstatus='".($_REQUEST['sortby']-10)."' ORDER BY "._PRE_."user.login ";
	}
	
	$query=$sql->query("SELECT COUNT(*) AS nbusers FROM "._PRE_."user".$ajout)->execute();
	list($nb)=$query->fetch_array();
	
	if(!isset($_GET['page']))		$page	=	1;
	else							$page	=	intval($_GET['page']);
	
	$tpl->box['navpages']=getnumberpages($nb,"adm_searchmember",$_REQUEST['number'],$page);
	if($nbpages>1)
		$tpl->box['pagebox']=$tpl->gettemplate("adm_searchmember","boxpages");
	
	
	
	$debut=($page*$_REQUEST['number'])-$_REQUEST['number'];
	$fin=$debut+$_REQUEST['number'];
	
	$query=$sql->query("SELECT "._PRE_."user.*, "._PRE_."groups.Nom_group FROM "._PRE_."user LEFT JOIN "._PRE_."groups ON "._PRE_."groups.id_group = "._PRE_."user.userstatus".$ajout."LIMIT %d,%d", array($debut, $_REQUEST['number']))->execute();
	
	$tpl->box['pseudolist']="";
	while($LnPseudo=$query->fetch_array())
	{
		$LnPseudo['login'] = getformatrecup($LnPseudo['login']);
		$LnPseudo['registerdate']=getlocaltime($LnPseudo['registerdate']);
		$LnPseudo['usermail']=getemail($LnPseudo['usermail']);
		
		if($LnPseudo['userstatus'] < 0)
			$LnPseudo['status']=$tpl->attlang("mbbanned");
		elseif($LnPseudo['userstatus'] == 0)
			$LnPseudo['status']=$tpl->attlang("mbwait");
		else
			$LnPseudo['status']=getformatrecup($LnPseudo['Nom_group']);
			
		$tpl->box['pseudolist'].=$tpl->gettemplate("adm_searchmember","structlignepseudo");
	}
			
	$tpl->box['tablemember']=$tpl->gettemplate("adm_searchmember","structtablepseudo");
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","listmembers");
}

if(empty($_REQUEST['action']))
{
	if(strlen($error)>0)
		$tpl->box['errorbox'] = $tpl->gettemplate("adm_searchmember","errorboxsearch");
	
	$tpl->box['group_list'] = "";
	$query = $sql->query("SELECT id_group, Nom_group FROM "._PRE_."groups WHERE id_group>1 ORDER BY id_group")->execute();
	while($group = $query->fetch_array())
	{
		$tpl->box['idgrp'] = $group['id_group']+10;
		$tpl->box['group_name'] = $group['Nom_group'];
		$tpl->box['group_list'] .= $tpl->gettemplate("adm_searchmember","optionsearch");	
	}
		
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","searchmember");
}

$cache.=$tpl->gettemplate("adm_searchmember","content");
require("bas.php");

	