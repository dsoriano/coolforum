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
getlangage("adm_skindumb");


// -------------------------------
// Importation du fichier skin
// -------------------------------

if($_REQUEST['action']=="import")
{
	if($_FILES['skin']['tmp_name']<>"none" && !empty($_FILES['skin']['tmp_name']) && is_uploaded_file($_FILES['skin']['tmp_name']))
	{
		
		// Récupération du contenu du fichier
		$f = fopen($_FILES['skin']['tmp_name'], "rb");
		$content = fread($f, filesize($_FILES['skin']['tmp_name']));
		fclose($f);
		
		
		// Traitement du fichier
		$content = preg_replace("/^(#|\/\/|-- )[^\n\t\r]*(\n|\t|\r)/im", "", $content);
		$content = str_replace(array("\n","\t","\r"),"",$content);
		$content = trim($content);
		
		if(preg_match("/^array[ ]*\(([ ]*\'[-_a-zA-Z0-9]+\'[ ]*=>[ ]*\'(.*?)\',)+([ ]*\'[-_a-zA-Z0-9]+\'[ ]*=>[ ]*\'(.*?)\'){0,1}\)$/",$content))
		{
			
			// Récupération de l'ID du futur skin
			$query=$sql->query("SELECT id FROM ".$_PRE."skins ORDER BY id DESC LIMIT 0,1");
			list($skinid)=mysql_fetch_array($query);
			$skinid++;
			
			
			// Insertion dans la base de données
			eval( "\$table = $content;" );
			
			while(list($propriete,$valeur)=each($table))
				$sql->query("INSERT INTO ".$_PRE."skins (id,propriete,valeur) VALUES ('".$skinid."','".$propriete."','".addslashes($valeur)."')");
			
			if($_REQUEST['toalluser']=="on")	$sql->query("UPDATE ".$_PRE."user SET skin='".$skinid."'");
			if($_REQUEST['todefaultskin']=="on")	$sql->query("UPDATE ".$_PRE."config SET valeur='".$skinid."' WHERE options='defaultskin'");
			
			
			$tpl->box['display'] = mysql_error() ? $tpl->attlang("importnok") : $tpl->attlang("importok");
			
		}
		else
			$tpl->box['display']=$tpl->attlang("invalidfile");
	}
	else
		$tpl->box['display']=$tpl->attlang("nofile");
	
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_skindumb","display");
	$cache.=$tpl->gettemplate("adm_skindumb","content");
	require("bas.php");
}



// -------------------------------
// Exportation du fichier skin
// -------------------------------

if($_REQUEST['action']=="export")
{
	
	// Récupération des valeurs du skin
	$list=array();
	$query=$sql->query("SELECT * FROM ".$_PRE."skins WHERE id='".intval($_REQUEST['id'])."'");
	while($skin=mysql_fetch_array($query))
		$list[$skin['propriete']]=$skin['valeur'];
	
	
	if(count($list)>0)
	{
		$filename = preg_match("|^([- _a-zA-Z0-9]+)$|",$list['skinname']) > 0 ? str_replace(" ","_",$list['skinname']) : "coolforum";
		header("Content-disposition: filename=".$filename."_".str_replace(array(" ","."),"-",$ForumVersion).".skin");
		header("Content-type: application/octetstream");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		// Compilation du fichier skin
		$chaine="";
		$chaine.="# ----------------------------------\n";
		$chaine.="# CoolForum Skin File\n";
		$chaine.="# Compatibilité ".$ForumVersion."\n";
		$chaine.="# ".strftime("%d/%m/%Y %H:%M",time())."\n";
		$chaine.="# Skin: ".$list['skinname']."\n";
		$chaine.="# ----------------------------------\n";
		$chaine.="\n\n";
		
		//$chaine.=var_export($list,TRUE); // PHP >= 4.2.0
		
		$chaine.="array (\n";
		reset($list);
		$list2=array();
		while(list($key,$val)=each($list))
			$chaine.="  '".$key."' => '".addslashes($val)."',\n";
		$chaine.=")";
		
		echo($chaine);
	}
	
}

// -------------------------------
// Accueil
// -------------------------------

if(empty($_REQUEST['action']))
{
	$tpl->box['skinlist'] = NULLSTR;
	
	$query=$sql->query("SELECT * FROM ".$_PRE."skins WHERE propriete='skinname'");
	while($j=mysql_fetch_array($query))
		$tpl->box['skinlist'].=$tpl->gettemplate("adm_skindumb","skinopt");
	
	$tpl->box['admcontent']=$tpl->gettemplate("adm_skindumb","accueilopt");
	$cache.=$tpl->gettemplate("adm_skindumb","content");
	require("bas.php");
}

