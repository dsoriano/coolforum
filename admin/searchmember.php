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
	$query=$sql->query("UPDATE ".$_PRE."user SET password='$password' WHERE userid=".$_GET['id']);
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","affnewpass");
	
}

if($_REQUEST['action']=="delmember")
{
	if($_POST['msg']=="delete")
	{
		//**** Mise à jour du nombre de réponses des sujets ****
		$query=$sql->query("SELECT parent,COUNT(*) AS nbposts FROM ".$_PRE."posts WHERE idmembre='$_POST[id]' GROUP BY parent");
		$nb=mysql_num_rows($query);
		
		if($nb>0)
			while($j=mysql_fetch_array($query))
				$MajTopics = $sql->query("UPDATE ".$_PRE."topics SET nbrep = nbrep-".$j['nbposts']." WHERE idtopic = ".$j['parent']);	
		
		//**** Suppression des messages ****
		$query=$sql->query("DELETE FROM ".$_PRE."posts WHERE idmembre=".$_POST['id']);
	
		
		//**** Suppression des sujets ****
		$query=$sql->query("DELETE FROM ".$_PRE."topics WHERE idmembre=".$_POST['id']);
		
		//**** Mise à jour du dernier posteur des topics ****
		$query=$sql->query("SELECT login FROM ".$_PRE."user WHERE userid=".$_POST['id']);
		list($login)=mysql_fetch_array($query);
	
		$login = getformatdbtodb($login);
		$query=$sql->query("SELECT idtopic FROM ".$_PRE."topics WHERE derposter='$login'");
		$nb=mysql_num_rows($query);
		
		if($nb>0)
			while(list($idtopic)=mysql_fetch_array($query))
				updatetopiclastposter($idtopic);
	
		//**** Mise à jour des forums ****
		$selectforums=$sql->query("SELECT forumid FROM ".$_PRE."forums ORDER BY forumid");
		
		if(mysql_num_rows($selectforums)>0)
		{
			while($forumss=mysql_fetch_array($selectforums))
			{
				updateforumlastposter($forumss['forumid']);
			}
		}
	}
	else
	{
		$query=$sql->query("UPDATE ".$_PRE."posts SET idmembre='0' WHERE idmembre='".$_POST['id']."'");
		$query=$sql->query("UPDATE ".$_PRE."topics SET idmembre='0' WHERE idmembre='".$_POST['id']."'");
	}
	
	//**** Table des bannis ***		
	$query=$sql->query("DELETE FROM ".$_PRE."banlist WHERE userid=".$_POST['id']);
	
	//**** Table des moderateurs ****		
	$query=$sql->query("DELETE FROM ".$_PRE."moderateur WHERE idusermodo=".$_POST['id']);
	
	//**** Table des messages privés ****
	$query=$sql->query("DELETE FROM ".$_PRE."privatemsg WHERE iddest=".$_POST['id']." OR idexp=".$_POST['id']);

	//**** Table des membres ****		
	$query=$sql->query("DELETE FROM ".$_PRE."user WHERE userid=".$_POST['id']);
	$query=$sql->query("DELETE FROM ".$_PRE."userplus WHERE idplus=".$_POST['id']);
	
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
		$query		=	$sql->query("SELECT COUNT(*) AS nbpseudos FROM ".$_PRE."user WHERE login='$rgpseudo' AND userid<>".$_POST['id']);
		list($nbpseudos)=mysql_fetch_array($query);
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
			$query 	= 	$sql->query("SELECT userlogo FROM ".$_PRE."user WHERE userid=".$_POST['id']);  
			$j	=	mysql_fetch_array($query);
	
			if(!empty($j['userlogo']))
				$filename=$j['userlogo'];
		}
	}
	
	if(strlen($error1)==0)
	{
		if($_POST['userlogin'] != $_POST['pseudoorig'])
		{
			$query = $sql->query("UPDATE ".$_PRE."user SET login='$rgpseudo' WHERE userid=".$_POST['id']);
			$query = $sql->query("UPDATE ".$_PRE."banlist SET login='$rgpseudo' WHERE userid=".$_POST['id']);
			$query = $sql->query("UPDATE ".$_PRE."forums SET lastforumposter='$rgpseudo' WHERE lastforumposter='".getformatmsg($_POST['pseudoorig'],false)."'");
			$query = $sql->query("UPDATE ".$_PRE."moderateur SET modologin='$rgpseudo' WHERE idusermodo=".$_POST['id']);
			$query = $sql->query("UPDATE ".$_PRE."posts SET pseudo='$rgpseudo' WHERE idmembre=".$_POST['id']);
			$query = $sql->query("UPDATE ".$_PRE."privatemsg SET pseudo='$rgpseudo' WHERE idexp=".$_POST['id']);
			$query = $sql->query("UPDATE ".$_PRE."topics SET pseudo='$rgpseudo' WHERE idmembre=".$_POST['id']);
			$query = $sql->query("UPDATE ".$_PRE."topics SET derposter='$rgpseudo' WHERE derposter='".getformatmsg($_POST['pseudoorig'],false)."'");
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
		
		$query = $sql->query("UPDATE ".$_PRE."user SET userstatus=".$newuserstatus.",usermail='".$_POST['usermail']."',usersite='$site', showmail='$showmail', showusersite='$showusersite', usersign='$sign',usercitation='$citation', userlogo='$filename', skin='$skin', timezone='$timezone', lng='$lng', notifypm='$notifypm', popuppm='$popuppm', mailing='$mailing', wysiwyg='$wysiwyg'  WHERE userid=".$_POST['id']);
		if(!$query)
			echo(mysql_error());
				
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
		$query = $sql->query("UPDATE ".$_PRE."userplus SET icq='".$_POST['icq']."',aim='$aim',yahoomsg='$yahoo',msn='".$_POST['msn']."', birth='$Birth', sex='".$_POST['sex']."', description = '$description' WHERE idplus='".$_POST['id']."'");
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
		$query			=	$sql->query("SELECT 	".$_PRE."user.login,
									".$_PRE."user.userstatus,
									".$_PRE."user.usermsg,
									".$_PRE."user.usermail,
									".$_PRE."user.usersite,
									".$_PRE."user.usersign,
									".$_PRE."user.usercitation,
									".$_PRE."user.showmail,
									".$_PRE."user.showusersite,
									".$_PRE."user.userlogo,
									".$_PRE."user.skin,
									".$_PRE."user.timezone,
									".$_PRE."user.lng,
									".$_PRE."user.notifypm,
									".$_PRE."user.popuppm,
									".$_PRE."user.mailing,
									".$_PRE."user.wysiwyg,
									".$_PRE."groups.*
									 FROM ".$_PRE."user LEFT JOIN ".$_PRE."groups ON ".$_PRE."user.userstatus=".$_PRE."groups.id_group
									 WHERE userid='$Id'");
		$Result			=	mysql_fetch_array($query);
		
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
	
	$query = $sql->query("SELECT id_group,Nom_group from ".$_PRE."groups WHERE id_group>1 ORDER BY id_group");
	while($LstGrp=mysql_fetch_array($query))
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
	$query			=	$sql->query("SELECT * FROM ".$_PRE."skins WHERE propriete='skinname'");
	while($j=mysql_fetch_array($query))
	{
		$selected	=	"";
		if($Result['skin']==$j['id'])	$selected=" SELECTED";
			
		$tpl->box['skinlist'].=$tpl->gettemplate("adm_searchmember","skinlist");
	}
		
	//**** sélection de la langue ****
	$tpl->box['lnglist']	=	"";
	$query			=	$sql->query("SELECT * FROM ".$_PRE."language");
	while($j=mysql_fetch_array($query))
	{
		$selected	=	"";
		if($Result['lng']==$j['code'])	$selected=" SELECTED";
			
		$tpl->box['lnglist'].=$tpl->gettemplate("adm_searchmember","lnglist");
	}
		
		
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","detailmembre");
	
	//**** Affichage des infos complémentaires ****
	
	if(strlen($error2)==0)
	{
		$query=$sql->query("SELECT * FROM ".$_PRE."userplus WHERE idplus='$Id'");
		$Results=mysql_fetch_array($query);
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
		$query=$sql->query("SELECT ".$_PRE."user.*, ".$_PRE."groups.Nom_group FROM ".$_PRE."user LEFT JOIN ".$_PRE."groups ON ".$_PRE."groups.id_group = ".$_PRE."user.userstatus WHERE ".$_PRE."user.login LIKE \"%$pseudo%\" ORDER BY ".$_PRE."user.login");
		$nb=mysql_num_rows($query);
		if($nb==0)
		{
			$error=$tpl->attlang("pseudonotfound");
			$_REQUEST['action'] = NULLSTR;
		}
		else
		{
			$tpl->box['pseudolist']="";
			while($LnPseudo=mysql_fetch_array($query))
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
		$ajout=" ORDER BY ".$_PRE."user.login ";
		break;
	case "2":
		$ajout=" ORDER BY ".$_PRE."user.registerdate ";
		break;
	case "3":
		$ajout=" ORDER BY ".$_PRE."user.usermsg DESC ";
		break;
	case "4":
		$ajout=" WHERE ".$_PRE."user.userstatus='0' ORDER BY ".$_PRE."user.login ";
		break;
	case "5":
		$ajout=" WHERE ".$_PRE."user.userstatus<'0' ORDER BY ".$_PRE."user.login ";
		break;
	default :
		$ajout=" WHERE ".$_PRE."user.userstatus='".($_REQUEST['sortby']-10)."' ORDER BY ".$_PRE."user.login ";
	}
	
	$query=$sql->query("SELECT COUNT(*) AS nbusers FROM ".$_PRE."user".$ajout);
	list($nb)=mysql_fetch_array($query);
	
	if(!isset($_GET['page']))		$page	=	1;
	else							$page	=	intval($_GET['page']);
	
	$tpl->box['navpages']=getnumberpages($nb,"adm_searchmember",$_REQUEST['number'],$page);
	if($nbpages>1)
		$tpl->box['pagebox']=$tpl->gettemplate("adm_searchmember","boxpages");
	
	
	
	$debut=($page*$_REQUEST['number'])-$_REQUEST['number'];
	$fin=$debut+$_REQUEST['number'];
	
	$query=$sql->query("SELECT ".$_PRE."user.*, ".$_PRE."groups.Nom_group FROM ".$_PRE."user LEFT JOIN ".$_PRE."groups ON ".$_PRE."groups.id_group = ".$_PRE."user.userstatus".$ajout."LIMIT ".$debut.",".$_REQUEST['number']);
	
	$tpl->box['pseudolist']="";
	while($LnPseudo=mysql_fetch_array($query))
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
	$query = $sql->query("SELECT id_group, Nom_group FROM ".$_PRE."groups WHERE id_group>1 ORDER BY id_group");
	while($group = mysql_fetch_array($query))
	{
		$tpl->box['idgrp'] = $group['id_group']+10;
		$tpl->box['group_name'] = $group['Nom_group'];
		$tpl->box['group_list'] .= $tpl->gettemplate("adm_searchmember","optionsearch");	
	}
		
	$tpl->box['admcontent']=$tpl->gettemplate("adm_searchmember","searchmember");
}

$cache.=$tpl->gettemplate("adm_searchmember","content");
require("bas.php");

	