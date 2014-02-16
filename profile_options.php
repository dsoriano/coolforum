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

getlangage("profile_options");

$error = NULLSTR;

// ###### Navigation ######
$tpl->treenavs=$tpl->gettemplate("treenav","treeprofil");
$cache.=$tpl->gettemplate("treenav","hierarchy");

	if(!isset($_POST['action']))
		$_POST['action']="profile";

	$_LOGO = explode("-",$_FORUMCFG['logos']); // Array: active - upload - gallerie - externe - largeur - hauteur - poids

	if ($_POST['action']=="save")
	{
		$error="";
	
		//**** test de l'email ****
		if(!testemail($_POST['usermail']))
			$error=$tpl->attlang("error1");		

		//**** test du site web ****
		if(preg_match("'^www\\.(([a-zA-Z0-9.\/@:%=?~_#\-]|&amp;)+)(?<![\.:#%?])$'",$_POST['usersite']))
			$_POST['usersite']	=	"http://".$_POST['usersite'];
		
		if(!preg_match("'^(http|ftp|https):\/\/([a-zA-Z0-9-\/\.@:%=?&;~_]+(?<![\.:%?&;]))$'",$_POST['usersite']))
			$_POST['usersite']="";	
				
		//**** formattage du skin ****
		if($_USER['userskin'] != $_POST['skin'])	$skin	=	intval($_POST['skin']);
			else					$skin	=	$_USER['userskin'];
		
		//**** upload / enregistrement du logo ****
		if(isset($_POST['deletelogo']) && $_POST['deletelogo']=="Y")
			$filename="";
		else
		{
			if(preg_match("|^[a-zA-Z0-9_\.-]+$|",$_POST['infologo']) > 0 && $_LOGO[2]=="Y")
				$filename=$_POST['infologo'];
			elseif(strlen($_POST['extlogo'])>0 && $_LOGO[3]=="Y")
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
						$filename="logo".$_USER['userid'].$ext;
						move_uploaded_file($_FILES['logo']['tmp_name'],"logos/".$filename);
						@chmod("logos/".$filename, 0777);
					}
					else
						$error=$tpl->attlang("logoerror1");
				}
				else
					$error=$tpl->attlang("logoerror2");
			}
			else
			{
				$query 	= 	$sql->query("SELECT userlogo FROM ".$_PRE."user WHERE userid=".$_USER['userid']);  
				$j	=	mysql_fetch_array($query);
		
				if(!empty($j['userlogo']))
					$filename=$j['userlogo'];
			}
		}
		
		if(strlen($error)==0)
		{
			//*** formattage variables diverses ***
			//$site		=	getformatmsg($_POST['usersite'],false);		
			$citation	=	getformatmsg($_POST['usercitation'],false);
			$sign		=	getformatmsg($_POST['usersign']);
			$lng		=	getformatmsg($_POST['lng'],false);
			
			// **** test des limites ****
			$citation	=	test_max_length($citation,$_USER['Max_Cit']);
			$sign		=	test_max_length($sign,$_USER['Max_Sign']);
			
			$timezone	=	intval($_POST['timezone']);
	
			if($_POST['showmail']=="N")	$showmail	=	"N";
				else			$showmail	=	"Y";
	
			if($_POST['showusersite']=="N")	$showusersite	=	"N";
				else			$showusersite	=	"Y";
	
			if(isset($_POST['notifypm']) && $_POST['notifypm']=="N")	$notifypm	=	"N";
				else			$notifypm	=	"Y";

			if($_POST['popuppm']=="N")	$popuppm	=	"N";
				else			$popuppm	=	"Y";

			if($_POST['mailing']=="N")	$mailing	=	"N";
				else			$mailing	=	"Y";
						
			if($_POST['wysiwyg']=="N")	$wysiwyg	=	"N";
				else			$wysiwyg	=	"Y";

			$query = $sql->query("UPDATE ".$_PRE."user SET usermail='".$_POST['usermail']."',usersite='".$_POST['usersite']."', showmail='$showmail', showusersite='$showusersite', usersign='$sign',usercitation='$citation', userlogo='$filename', skin='$skin', timezone='$timezone', lng='$lng', notifypm='$notifypm', popuppm='$popuppm', mailing='$mailing', wysiwyg='$wysiwyg'  WHERE userid=".$_USER['userid']);
			if(!$query)
				echo(mysql_error());
				
			$tpl->box['profilcontent']=$tpl->gettemplate("profil_options","changeok");
			$tpl->box['profilcontent'].=getjsredirect("profile.php?p=profile",2000);
		}
		else
			$_POST['action']="profile";
	}
	
	if($_POST['action']=="profile")
	{
		$timezn = array();
		array_rempl($timezn,0,24,NULLSTR);
		
		if(strlen($error)==0)
		{
			$tpl->box['error']	= NULLSTR;
			$query			=	$sql->query("SELECT login,usermsg,usermail,usersite,usersign,usercitation,showmail,showusersite,userlogo,skin,timezone,lng,notifypm,popuppm,mailing,wysiwyg FROM ".$_PRE."user WHERE userid=".$_USER['userid']);
			$Result			=	mysql_fetch_array($query);

			
			//**** preview de la signature ****
			$PreviewUserSign = getformatrecup($Result['usersign']);
			
			$tpl->box['previewusersign'] = NULLSTR;
			
			if(!empty($PreviewUserSign))
			{
				if($_FORUMCFG['smileinsign']=="Y")
				{
					$table_smileys = getloadsmileys();
					$PreviewUserSign = getreturnsmilies($PreviewUserSign);
				}
				if($_FORUMCFG['bbcodeinsign']=="Y")
				{
					InitBBCode();
					$PreviewUserSign = getreturnbbcode($PreviewUserSign);
				}
				$tpl->box['previewusersign'] = $tpl->gettemplate("profil_options","previewusersign");
			}
			
	
			$Result['usercitation']	=	getformatrecup($Result['usercitation']);
			$Result['usersign']	=	getformatrecup($Result['usersign'],true);
		}
		else
		{
			$tpl->box['error']		=	$tpl->gettemplate("profil_options","errorbox");
			$Result					=	$_POST;
			$Result['userlogo']		=	$filename;
			$Result['usermail']		=	getrecupforform($Result['usermail']);
			$Result['usercitation']	=	getrecupforform($Result['usercitation']);
			$Result['login']		=	htmlentities($_USER['username'], ENT_COMPAT,'ISO-8859-1', true);
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
		$Result['canusebbcode']						=	$tpl->gettemplate("profil_options","isallowed");

		//**** smileys autorisés dans signature? ****
		$tpl->box['whatis'] 						= 	$tpl->attlang("smileysare");
		if($_FORUMCFG['smileinsign']=="Y")	$tpl->box['yesorno']	=	$tpl->attlang("allow2");
			else				$tpl->box['yesorno']	=	$tpl->attlang("disabled2");		
		$Result['canusesmileys']						=	$tpl->gettemplate("profil_options","isallowed");		
		
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
			$tpl->box['mailnotify']=$tpl->gettemplate("profil_options","notifyok");
		}
		else
			$tpl->box['mailnotify']=NULLSTR;
		
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
            $tpl->box['persologo'] = $_LOGO[1] == "Y" ? $tpl->gettemplate("profil_options","uploadlogobox") : NULLSTR;
			
			//**** peut-on utiliser la collection de logos? ****
            $tpl->box['defaultlogo'] = $_LOGO[2]=="Y" ? $tpl->gettemplate("profil_options","logocollection") : NULLSTR;

			//**** peut-on indiquer une url externe ? ****
            $tpl->box['extlogo'] = $_LOGO[3] == "Y" ? $tpl->gettemplate("profil_options","extlogo") : NULLSTR;

			//**** affichage du logo courant ****
			if(!empty($Result['userlogo']))
			{
				if(preg_match("|^\"http://|",$Result['userlogo']) > 0 && $_LOGO[3]=="Y")
					$tpl->box['logo'] = $tpl->gettemplate("profil_options","affextavatar");
				elseif(preg_match("|^\"http://|",$Result['userlogo']) == 0)
					$tpl->box['logo'] = $tpl->gettemplate("profil_options","affavatar");
			}
			else	$tpl->box['logo'] = $tpl->attlang("nologonow");	
						
			//**** chargement du template ****
			$tpl->box['logotpl']=$tpl->gettemplate("profil_options","logotpl");	
			
		}
		
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
							
			$tpl->box['mailing'] = $tpl->gettemplate("profil_options","mailing");
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
			
			$tpl->box['skinlist'].=$tpl->gettemplate("profil_options","skinlist");
		}
		
		//**** sélection de la langue ****
		$tpl->box['lnglist']	=	"";
		$query			=	$sql->query("SELECT * FROM ".$_PRE."language");
		while($j=mysql_fetch_array($query))
		{
			$selected	=	"";
			if($Result['lng']==$j['code'])	$selected=" SELECTED";
			
			$tpl->box['lnglist'].=$tpl->gettemplate("profil_options","lnglist");
		}
		
			
		$tpl->box['profilcontent']=$tpl->gettemplate("profil_options","optionsform");
	}
