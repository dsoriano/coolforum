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

// ################################################################################
//                            DEFINITION DES FONCTIONS

function geterrorcode()
{
 global $compteur,$_GET;
 	return($_GET['module']."-".$_GET['steps']."-".$compteur."<br>".mysql_error());
}

function exec_request()
{
	global $update,$sql,$continue,$c, $compteur;

	if($continue==true)
	{
		if($c <= $compteur)
		{	
			$query=$sql->query($update['sql']);
			if($query)
				echo($update['ok']."<br>");
			else
			{
				$error_code = geterrorcode();
				echo("<font color=red>".$update['nok']."<br>");
				echo("Votre code d'erreur : <font size=3>$error_code</font></font>");
				$continue=false;
			}
		}
		$compteur++;
	}	
}

function affseparate()
{
	global $c, $compteur,$continue;
	
	if(($c <= $compteur) && $continue)
		echo("<p><font color=red>##########</font><p>");
}

function next_step()
{
	global $_GET, $continue;
	
	if($continue)
		echo("<form action=\"install.php\" method=\"get\">
		<input type=\"hidden\" name=\"action\" value=\"update\">
		<input type=\"hidden\" name=\"module\" value=\"".$_GET['module']."\">
		<input type=\"hidden\" name=\"steps\" value=\"".($_GET['steps']+1)."\">
		<input type=\"submit\" value=\"Continuer ->>\">
		</form>");
}

function end_maj()
{
	global $start_key, $ForumDBVersion , $lastversion, $version;
	
	if($ForumDBVersion==$lastversion)
		echo("Votre forum a été mis à jour correctement.<p>
		
		Veuillez supprimer le dossier <i>install</i> de votre compte FTP avant de réutiliser votre forum.");
	else
	{
		echo("Nous allons maintenant lancerla mise à jour vers la version<br><font color=red>".$version[$start_key+1]."</font><p>
	<form action=\"install.php\" method=\"get\">
	<input type=\"hidden\" name=\"action\" value=\"update\">
	<input type=\"hidden\" name=\"module\" value=\"".($_GET['module']+1)."\">
	<input type=\"hidden\" name=\"steps\" value=\"1\">
	<input type=\"submit\" value=\"Lancer la mise à jour ->>\">
	</form>");
	}
}

require_once '../secret/config.inc.php';

// ################################################################################
//                               CONNEXION A MYSQL

require_once '../lib/vendor/cfFramework/database/databaseFactory.php';
$sql = databaseFactory::connect(DB_DRIVER, array(
    'hostname' => DB_HOST,
    'username' => DB_USER,
    'password' => DB_PASSWORD,
    'database' => DB_NAME
));

// ################################################################################

$c = isset($_REQUEST['c']) ? (int)$_REQUEST['c'] : 0;
$compteur = 1;

// #########################################
if(isset($_POST['errorcode'])) {
	$tablarray = explode("-",$_POST['errorcode']);
	$_GET['module'] = $tablarray[0];
	$_GET['steps'] = $tablarray[1];
	$c = $tablarray[2];
}

// #########################################

$versions = array();
$continue=true;

if (!isset($_REQUEST['action'])) {
	$_REQUEST['action'] = "";
}

// #####    Définition des versions    #####
$version[] = "0.6 beta";
$version[] = "0.7 beta";
$version[] = "0.8 beta";

// ################################################################################

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Mise à jour CoolForum</title>
	
<style type="text/css">
a.men:link{color: white; text-decoration:underline;}
a.men:visited{color: white; text-decoration:underline;}
a.men:hover{color: white; text-decoration:none;}

a.lien:link{color: black; text-decoration: underline;}
a.lien:visited{color: black; text-decoration: underline;}
a.lien:hover{color: black; text-decoration:none;}
	
.form{background:#265789; color:white; font: 8pt verdana; border-style:solid; border-color:black; border-width:1;}
.form2{background:#A4B6C9; color:black; font: 8pt ; border-style:solid; border-color:; border-width:1;}

.corp{font-family:verdana; color:white;}
.corp2{font-family:verdana; color:black;}
</style>
	
</head>
<body bgcolor="#909090">

<table border="0" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td height="89" width="30" nowrap><img src="images/bghautgauche.gif" alt=""></td>
    <td height="89" width="273"><a href="index.php"><img src="images/logo.jpg" border="0" alt="" /></a></td>
    <td height="89" style="background: url('images/bghaut.jpg') transparent repeat top left;">&nbsp;</td>
    <td height="89" width="30" nowrap><img src="images/bghautdroit.gif" alt="" /></td>
  </tr>
</table>
	
<table border="0" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td width="30" nowrap style="background:url('images/bggauche.gif') transparent repeat top left;">&nbsp;</td>
    <td bgcolor="#C1C1C1">
      <table border="1" style="border-collapse:collapse; border-color: #000;" width="100%" cellpadding="0" rules="none">
        <tr>
          <td height="25" class="corp" style="background: url('images/bgdegrad.jpg') transparent repeat top left; padding-left:10;">
            <font size="2">
            <b>Mise à jour de CoolForum</b>
            </font>
          </td>
        </tr>
      </table>
      <img src="images/blank.gif" alt=""><br>
    </td>
    <td width="30" nowrap style="background: url('images/bgdroite.gif') transparent repeat top left">&nbsp;</td>
  </tr>
</table>
	
<table border="0" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td width="30" nowrap style="background: url('images/bggauche.gif') transparent repeat top left;">&nbsp;</td>
    <td bgcolor="#C1C1C1">
      &nbsp;
      <table border="1" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-color: #000;">
        <tr>
          <td bgcolor="#A4B6C9" class="corp2" align="center">
            <font size="2"><b>
            &nbsp;<br>
<?php
if($_REQUEST['action'] == "update")
{
	$query	=	$sql->query("SELECT valeur FROM ".$_PRE."config WHERE options='ForumDBVersion'");
	if (!$query) {
		echo(mysql_error());
    }
	$nb		=	mysql_num_rows($query);
		
	if ($nb>0) {
		list($ForumDBVersion)	=	mysql_fetch_array($query);
	
		$nbTotalVersions 		= 	count($version);		
		$lastversion			= 	$version[$nbTotalVersions-1];
		
		for($i = 0; $i < $nbTotalVersions; $i++) {
			if ($version[$i] == $ForumDBVersion) {
				$start_key		=	$i;
            }
        }
	}
	
	if (!isset($_GET['module'])) {
		if ($nb>0) {
			echo("Nous avons détecté que la version <font color=red>".$ForumDBVersion."</font> est installée.<p>");
			
			$nbSteps=$nbTotalVersions-$start_key-1;
			if ($nbSteps==0) {
				echo("La dernière version est déjà installée. Aucune mise à jour disponible");
            } else {
				if ($nbSteps==1) {
					echo("Nous allons mettre à jour votre forum à la version <font color=red>".$lastversion."</font>.<p>");
                } else {
					echo("Pour mettre à jour votre forum à la dernière version, nous allons procéder en $nbSteps étapes:<p>");
					echo("<table border=1 bordercolor=\"black\" style=\"border-collapse:collapse;\">
					<tr>
					  <td class=\"corp\" bgcolor=\"#537FAC\">
					  <font size=2>");
					  
					for ($i = $start_key; $i < ($nbTotalVersions-1); $i++) {
						echo("- Mise à jour de la version <font color=silver><b>".$version[$i]."</b></font> à la version <font color=silver><b>".$version[$i+1]."</b></font><br>");
					}
					echo("</font></td>
					</tr>
					</table><p>");
				}			
				echo("<hr color=\"black\">
					<center><font color=\"red\"><u>ATTENTION</u></font></center><p>
					Nous vous rappelons qu'avant toute mise à jour de votre base de donnée, vous <u>DEVEZ</u> effectuer une sauvegarde de celle-ci.
					En cas de problème lors de la mise à jour, votre base de donnée pourrait être irrémédiablement endommagée. Si vous ne savez pas comment
					sauvegarder votre base de donnée, un tutorial est disponible à l'adresse suivante: <a href=\"http://www.coolforum.net/faqs/faq-bdd.html#bdd01\" target=\"_blank\" class=\"men\">http://www.coolforum.net/faqs/faq-bdd.html#bdd01</a><p>
					<hr color=\"black\"><p>
				
					Pour commencer la mise à jour, cliquez sur le bouton ci-dessous:<p>
					<form action=\"install.php\" method=\"get\">
					<input type=\"hidden\" name=\"action\" value=\"update\">
					<input type=hidden name=\"module\" value=\"".($start_key+1)."\">
					<input type=hidden name=\"steps\" value=\"1\">
					<input type=submit value=\"Commencer la mise à jour\" class=\"form\">
					</form>");
			}
			
			echo("<hr color=\"black\"><p>
				Si pendant l'installation le script rencontre un problème lors d'une requête, il vous donnera un code d'erreur que vous devez noter.
				Aprés avoir identifié et éventuellement réparé ce problème, insérez le code d'erreur ci-dessous et cliquez sur le bouton \"Continuer\",
				cela vous permettra de reprendre la mise à jour là où elle s'était arrêtée.<p>
	
					<form action=\"install.php\" method=\"post\">
					Entrez le code d'erreur: <input type=\"text\" name=\"errorcode\" value=\"\" class=\"form\">
					<input type=submit value=\"Reprendre la mise à jour\" class=\"form\">
					<input type=\"hidden\" name=\"action\" value=\"update\">
					</form>");			
				 
		}
	} else {
		$Module_Name=str_replace(" ","_",$version[$_GET['module']]);
		include("./upd_" . $Module_Name . ".php");
	
	}
}


//////////////////////////////////////////////////
//		   INSTALLATION	        	//

if ($_REQUEST['action'] == "install") {
	require_once("mod_install.php");
}

//////////////////////////////////////////////////
//		   SUPPRESSION	        	//

if ($_REQUEST['action']=="delete") {
	include("mod_delete.php");	
}

//////////////////////////////////////////////////
//		ACCUEIL INSTALLATION		//

if (empty($_REQUEST['action'])) {
	?>
	Bienvenue dans l'installation de <font color="red">CoolForum</font>.<p>
	<hr color="red" width=250>
	Ce script va vous permettre d'installer votre nouveau forum, de mettre à jour votre forum actuel ou bien supprimer les tables MySQL du forum.<p>
	
	Choisissez votre type d'installation:<br>
	<form action="install.php" method="post">
	<select name="action" class="form">
	  <option value="install">Installation complète
	  <option value="update">Mise à jour
	  <option value="delete">Suppression des tables
	</select>
	<input type="submit" value="Valider" class="form">
	</form>
	<?php
}
	?>
            </b></font><p>
          </td>
        </tr>
      </table>
      &nbsp;<br>
    </td>
    <td width="30" nowrap style="background: url('images/bgdroite.gif') transparent repeat top left">&nbsp;</td>
  </tr>
</table>
<table border="0" width="100%" cellpadding="0" cellspacing="0">
  <tr height="21">
    <td width="16" nowrap><img src="images/bgbasgauche.gif" alt=""></td>
    <td bgcolor="#C1C1C1" valign="top" style="background: url('images/bgbas.gif') transparent repeat top left">
      <table border="0" height="20" cellspacing="0" width="100%">
        <tr>
          <td class="corp" align="center" bgcolor="#265789">
            &nbsp;
          </td>
        </tr>
      </table>
    </td>
    <td width="16" nowrap><img src="images/bgbasdroite.gif"></td>
  </tr>
</table><p>

</body>
</html>
