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
getlangage("adm_options_avatars");

$tpl->box['listadd'] = NULLSTR;

if($_REQUEST['action']=="save")
{
	if(!isset($_REQUEST['activateLogos']) || $_REQUEST['activateLogos']!="Y")			$_REQUEST['activateLogos']="N";
	if(!isset($_REQUEST['activeDefaultLogo']) || $_REQUEST['activeDefaultLogo']!="Y")	$_REQUEST['activeDefaultLogo']="N";
	if(!isset($_REQUEST['activePersoLogo']) || $_REQUEST['activePersoLogo']!="Y")		$_REQUEST['activePersoLogo']="N";
	if(!isset($_REQUEST['activeExtLogo']) || $_REQUEST['activeExtLogo']!="Y")			$_REQUEST['activeExtLogo']="N";

	$_REQUEST['WidthLogo']	= intval($_REQUEST['WidthLogo']);
	$_REQUEST['HeightLogo']	= intval($_REQUEST['HeightLogo']);
	$_REQUEST['SizeLogo']	= intval($_REQUEST['SizeLogo']);

	$chain=$_REQUEST['activateLogos']."-".$_REQUEST['activePersoLogo']."-".$_REQUEST['activeDefaultLogo']."-".$_REQUEST['activeExtLogo']."-".$_REQUEST['WidthLogo']."-".$_REQUEST['HeightLogo']."-".$_REQUEST['SizeLogo'];

	$query=$sql->query("UPDATE "._PRE_."config SET valeur='".$chain."' WHERE options='logos'")->execute();

	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="uploadnewlogo")
{
	if($_FILES['logo']['tmp_name']<>"none" && !empty($_FILES['logo']['tmp_name']) && ($_FILES['logo']['type']=="image/pjpeg" || $_FILES['logo']['type']=="image/gif" || $_FILES['logo']['type']=="image/png"))
	{
		switch($_FILES['logo']['type'])
		{
			case "image/pjpeg":	$ext=".jpg";	break;
			case "image/gif":	$ext=".gif";	break;
			case "image/png":	$ext=".png";	break;
		}

		$insert_avatar = $sql->query("INSERT INTO "._PRE_."avatars (ext) VALUES ('".$ext."')")->execute();
		$id=$sql->insertId();

		$filename="default".$id.$ext;
		move_uploaded_file($_FILES['logo']['tmp_name'],"../logos/".$filename);
	}
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="autosearch")
{
	// **** récupération de la liste des IDs des logos ****
	$ListID=array();
	$query = $sql->query("SELECT idlogo FROM "._PRE_."avatars")->execute();
	while($j=$query->fetch_array())
		$ListID[]=$j['idlogo'];
	$ListID="-".implode("-",$ListID)."-";

	// **** recherche des logos qui ne sont pas dans la liste ****
	$listadd=array();
	$dir=opendir("../logos");
	while($file=readdir($dir))
		if(preg_match("/^default([0-9]+)(\.gif|\.jpg|\.jpeg|\.png)$/",$file,$out))
			if(preg_match("|-".$out[1]."-|",$ListID) == 0 && $out[1][0]>0) // sécurité
			{
				$sql->query("INSERT INTO "._PRE_."avatars (idlogo,ext) VALUES (%d,'%s')", array($out[1], $out[2]))->execute();

				$ListID		.=	$out[1]."-"; // sécurité
				$listadd[]	=	"default".$out[1].$out[2];
			}
	closedir($dir);

	// **** combien d'avatars ajoutés ? ****
	if(count($listadd)>0)
	{
		$howmanyadd		=	count($listadd);
		$listadd		=	implode("<br>",$listadd);
		$tpl->box['listadd']	=	$tpl->gettemplate("adm_options_avatars","listadd");
	}

	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="delete")
{
	$sql->query("DELETE FROM "._PRE_."avatars WHERE idlogo=%d", intval($_REQUEST['id']))->execute();
	$sql->query("UPDATE "._PRE_."user SET userlogo='' WHERE userlogo LIKE 'default%d%%'", intval($_REQUEST['id']))->execute();
	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	$checked=array(NULLSTR, NULLSTR, NULLSTR, NULLSTR);

	$tpl->box['listlogos'] = NULLSTR;
	$tpl->box['defaultlogos'] = NULLSTR;

	$configuration=getconfig();

	$_LOGO = explode("-",$configuration['logos']); // Array: active - upload - gallerie - externe - largeur - hauteur - poids

	if($_LOGO[0]=="Y")	$checked[0]=" CHECKED";
	if($_LOGO[1]=="Y")	$checked[1]=" CHECKED";
	if($_LOGO[3]=="Y")	$checked[3]=" CHECKED";
	if($_LOGO[2]=="Y")
	{
		$checked[2]=" CHECKED";

		$query = $sql->query("SELECT * FROM "._PRE_."avatars")->execute();
		$nb = $query->num_rows();

		if($nb>0)
		{
			$compt=0;
			while($j=$query->fetch_array())
			{
				$tpl->box['listlogos'] .= $tpl->gettemplate("adm_options_avatars","caselogo");

				$compt++;
				if($compt%3==0)
					$tpl->box['listlogos'] .= $tpl->gettemplate("adm_options_avatars","separatelogo");
			}
		}
		else	$tpl->box['listlogos'] = $tpl->gettemplate("adm_options_avatars","nologo");

		$tpl->box['defaultlogos'] = $tpl->gettemplate("adm_options_avatars","defaultlogos");
	}

	$cache.=$tpl->gettemplate("adm_options_avatars","optionslist");
}

require("bas.php");
