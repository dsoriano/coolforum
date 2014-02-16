<?php
//*********************************************************************************
//*                                                                               *
//*                  CoolForum v.0.8.5 Beta : Forum de discussion                   *
//*              Copyright �2001-2014 SORIANO Denis alias Cool Coyote             *
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
//*       Forum Cr�� par SORIANO Denis (Cool Coyote)                              *
//*       contact : coyote@coolcoyote.net                                         *
//*       site web et t�l�chargement : http://www.coolforum.net                   *
//*                                                                               *
//*********************************************************************************

function getconfig()
{
	global $sql,$_PRE;
	
	$result=$sql->query("SELECT * FROM ".$_PRE."config");
	while($j=mysql_fetch_array($result))
	{
		$tableau[$j['options']]=$j['valeur'];
	}
	
	$tableau['catseparate']=htmlentities($tableau['catseparate'], ENT_COMPAT,'ISO-8859-1', true);
	return($tableau);
}

function getright($chaine)
{
	$retval = 0;
	if($chaine[0] == "1")
		$retval += 7;
	if($chaine[1] == "1")
		$retval += 16;
	if($chaine[2] == "1")
		$retval += 8;
	if($chaine[3] == "1")
		$retval += 32;
		
	return($retval);		
}

function getright2($chaine)
{
	$retval = 0;
	if($chaine[0] == "1")
		$retval += 1;
	if($chaine[1] == "1")
		$retval += 2;
	if($chaine[2] == "1")
		$retval += 4;
	if($chaine[3] == "1")
		$retval += 8;
	if($chaine[4] == "1")
		$retval += 16;		
	if($chaine[5] == "1")
		$retval += 32;
	if($chaine[6] == "1")
		$retval += 64;
	if($chaine[6] == "1")
		$retval += 128;
	return($retval);		
}

$update = array();

switch($_GET['steps'])
{
	case 1:
	
		// ##############################################
		// #### CREATION TABLES GROUPS / GROUPS_PERM ####
		// ##############################################

		$update['sql']="CREATE TABLE ".$_PRE."groups (
				  id_group int(11) NOT NULL auto_increment,
				  parent int(11) NOT NULL default '0',
				  Nom_group varchar(100) NOT NULL default '',
				  Droits_generaux int(11) NOT NULL default '0',
				  Max_Pm int(11) NOT NULL default '0',
				  Max_Cit int(11) NOT NULL default '0',
				  Max_Sign int(11) NOT NULL default '0',
				  Max_Desc int(11) NOT NULL default '0',
				  PRIMARY KEY  (id_group),
				  KEY parent (parent)
			) TYPE=MyISAM";
		$update['ok']="Table ".$_PRE."groups cr��e";
		$update['nok']="Probl�me lors de la cr�ation de la table ".$_PRE."groups";
		
		exec_request();
		
		$update['sql']="CREATE TABLE ".$_PRE."groups_perm (
				  id_group int(11) NOT NULL default '0',
				  id_forum int(11) NOT NULL default '0',
				  droits int(11) NOT NULL default '0',
				  MaxChar int(11) NOT NULL default '0',
				  UNIQUE KEY id_group (id_group,id_forum)
				) TYPE=MyISAM";
		$update['ok']="Table ".$_PRE."groups_perm cr��e";
		$update['nok']="Probl�me lors de la cr�ation de la table ".$_PRE."groups_perm";
		
		exec_request();
		
		affseparate();
		
		// ################################
		// #### INSERTIONS DES GROUPES ####
		// ################################

		$CONFIG = getconfig();
		
		$limitpmlength		=	$CONFIG['limitpmlength'];
		$limitusercitlength	=	$CONFIG['limitusercitlength'];
		$limitusersignlength	=	$CONFIG['limitusersignlength'];
		
		if($CONFIG['statsconfig'][1] == "1")	$statguest	=	3;
		else					$statguest	=	2;
		
		if($CONFIG['statsconfig'][2] == "1")	$statmbr	=	3;
		else					$statmbr	=	2;
		
		if($CONFIG['statsconfig'][3] == "1")	$statmodo	=	524291;
		else					$statmodo	=	524290;
		
		if($CONFIG['statsconfig'][5] == "1")	$statadm	=	1572867;
		else					$statadm	=	1572866;
		
		$update['sql']="INSERT INTO ".$_PRE."groups VALUES (1, 0, 'Visiteur', $statguest, $limitpmlength, $limitusercitlength, $limitusersignlength, $limitusersignlength)";
		$update['ok']="Groupe <b>Visiteur</b> cr��";
		$update['nok']="Groupe <b>Visiteur</b> non cr��";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."groups VALUES (2, 0, 'Membre', $statmbr, $limitpmlength, $limitusercitlength, $limitusersignlength, $limitusersignlength)";
		$update['ok']="Groupe <b>Membre</b> cr��";
		$update['nok']="Groupe <b>Membre</b> non cr��";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."groups VALUES (3, 0, 'Mod�rateur', $statmodo, $limitpmlength, $limitusercitlength, $limitusersignlength, $limitusersignlength)";
		$update['ok']="Groupe <b>Mod�rateur</b> cr��";
		$update['nok']="Groupe <b>Mod�rateur</b> non cr��";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."groups VALUES (4, 0, 'Administrateur', $statadm, $limitpmlength, $limitusercitlength, $limitusersignlength, $limitusersignlength)";
		$update['ok']="Groupe <b>Administrateur</b> cr��";
		$update['nok']="Groupe <b>Administrateur</b> non cr��";
		
		exec_request();
		
		$update['sql']="UPDATE ".$_PRE."user SET userstatus=userstatus-1 WHERE userstatus>4";
		$update['ok']="Status d'administrateur supprim�";
		$update['nok']="Status d'administrateur non supprim�";
		
		exec_request();
		
		next_step();
		break;
		
	case 2:
		// ###############################################
		// #### CONVERTION DES DROITS SUR LES GROUPES ####
		// ###############################################
		
		$CONFIG = getconfig();
		$maxmsg = $CONFIG['limitmsglength'];
		
		$query = $sql->query("SELECT forumid, mbrights FROM ".$_PRE."forums WHERE mbrights LIKE \"0%\" ORDER BY forumid");
		$nb = mysql_num_rows($query);
		
		if($nb>0)
		{
			while(list($idforum, $mbrights) = mysql_fetch_array($query))
			{
				$right = getright(substr($mbrights,1,3)."0");
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (1,$idforum,$right,$maxmsg)";
				$update['ok']="Droits <b>Visiteur</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Visiteur</b> non mis � jour";
		
				exec_request();
				
				$right = getright(substr($mbrights,4,4));
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (2,$idforum,$right,$maxmsg)";
				$update['ok']="Droits <b>Membre</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Membre</b> non mis � jour";
		
				exec_request();
								
				$right = getright(substr($mbrights,8,4));
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (3,$idforum,$right,$maxmsg)";
				$update['ok']="Droits <b>Mod�rateur</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Mod�rateur</b> non mis � jour";
		
				exec_request();
								
				$right = 127;	
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (4,$idforum,$right,$maxmsg)";
				$update['ok']="Droits <b>Administrateur</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Administrateur</b> non mis � jour";
		
				exec_request();
				
				affseparate();				
			}
		}

		$query = $sql->query("SELECT forumid, mbrights FROM ".$_PRE."forums WHERE mbrights LIKE \"1%\" ORDER BY forumid");
		$nb = mysql_num_rows($query);
		
		if($nb>0)
		{
			while(list($idforum, $mbrights) = mysql_fetch_array($query))
			{
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (1,$idforum,0,$maxmsg)";
				$update['ok']="Droits <b>Visiteur</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Visiteur</b> non mis � jour";
		
				exec_request();
				
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (2,$idforum,0,$maxmsg)";
				$update['ok']="Droits <b>Membre</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Membre</b> non mis � jour";
		
				exec_request();
								
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (3,$idforum,0,$maxmsg)";
				$update['ok']="Droits <b>Mod�rateur</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Mod�rateur</b> non mis � jour";
		
				exec_request();
								
				$update['sql']="INSERT INTO ".$_PRE."groups_perm (id_group, id_forum, droits, MaxChar) VALUES (4,$idforum,0,$maxmsg)";
				$update['ok']="Droits <b>Administrateur</b> mis � jour avec forum $idforum";
				$update['nok']="Droits <b>Administrateur</b> non mis � jour";
		
				exec_request();
				
				affseparate();				
			}
		}

		next_step();
		break;
		
	case 3:
		// #################################
		// #### MISE A JOUR DES SMILEYS ####
		// #################################
		
		$update['sql']="ALTER TABLE ".$_PRE."smileys ADD ordersmile INT( 5 ) NOT NULL";
		$update['ok']="Table smileys mise � jour";
		$update['nok']="Table smileys non mise � jour";
		
		exec_request();
		
		$query = $sql->query("SELECT idsmile FROM ".$_PRE."smileys ORDER BY idsmile");
		$i = 1;
		
		while(list($idsmile)=mysql_fetch_array($query))
		{
			$update['sql']="UPDATE ".$_PRE."smileys SET ordersmile=$i WHERE idsmile=$idsmile";
			$update['ok']="Smileys $idsmile mis � jour";
			$update['nok']="Smileys $idsmile non mis � jour";
			
			exec_request();
			
			$i++;
		}			
		
		affseparate();
		
		$update['sql']="UPDATE ".$_PRE."smileys SET codesmile = ':gne:' WHERE codesmile = ':gogol:'";
		$update['ok']="Table smileys mise � jour";
		$update['nok']="Table smileys non mise � jour";		
		
		exec_request();

		$update['sql']="UPDATE ".$_PRE."smileys SET codesmile = ':gne:' WHERE codesmile = ':gogol:'";
		$update['ok']="Table smileys mise � jour";
		$update['nok']="Table smileys non mise � jour";		
		
		exec_request();

		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,':gogol:',':gne:') WHERE smiles = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";	
		
		affseparate();
		
		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,'[quote1]','[quote]') WHERE bbcode = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";
		
		exec_request();		

		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,'[/quote1]','[/quote]') WHERE bbcode = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";
		
		exec_request();	
		
		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,'[quote2]','[quote]') WHERE bbcode = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";
		
		exec_request();	

		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,'[/quote2]','[/quote]') WHERE bbcode = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";
		
		exec_request();	

		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,'[quote3]','[quote]') WHERE bbcode = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";
		
		exec_request();	

		$update['sql']="UPDATE ".$_PRE."posts SET msg = REPLACE(msg,'[/quote3]','[/quote]') WHERE bbcode = 'Y'";
		$update['ok']="Table posts mise � jour";
		$update['nok']="Table posts non mise � jour";
		
		exec_request();	
				
		next_step();
		
		break;

	case 4:
		// ########################################
		// #### INSERTIONS TABLE CONFIGURATION ####
		// ########################################

		$CONFIG = getconfig();
		$maxmsg = $CONFIG['limitmsglength'];

		$logos			= 	$CONFIG['activateLogos']."-".$CONFIG['activePersoLogo']."-".$CONFIG['activeDefaultLogo']."-N-".$CONFIG['logosparams'];

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('logos', '$logos')";
		$update['ok']="Valeur <i>logos</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>logos</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('birth','')";
		$update['ok']="Valeur <i>birth</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>birth</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('nextdailyupdate','0')";
		$update['ok']="Valeur <i>nextdailyupdate</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>nextdailyupdate</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('sendpmbymail', 'N')";
		$update['ok']="Valeur <i>sendpmbymail</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>sendpmbymail</i> non ins�r�e dans table configuration";
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('repflash','Y')";
		$update['ok']="Valeur <i>repflash</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>repflash</i> non ins�r�e dans table configuration";
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config VALUES ('grades', 'a:5:{i:1;a:3:{i:0;s:7:\"Nouveau\";i:1;i:1;i:2;i:1;}i:2;a:3:{i:0;s:8:\"Visiteur\";i:1;i:50;i:2;i:2;}i:3;a:3:{i:0;s:14:\"Habitu&eacute;\";i:1;i:100;i:2;i:3;}i:4;a:3:{i:0;s:9:\"Titulaire\";i:1;i:200;i:2;i:4;}i:5;a:3:{i:0;s:6:\"Pilier\";i:1;i:500;i:2;i:5;}}')";
		$update['ok']="Valeur <i>grades</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>grades</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config VALUES ('use_grades', 'Y')";
		$update['ok']="Valeur <i>use_grades</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>use_grades</i> non ins�r�e dans table configuration";
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config VALUES ('conn_accueil', 'Y')";
		$update['ok']="Valeur <i>conn_accueil</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>conn_accueil</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config VALUES ('conn_forum', 'Y')";
		$update['ok']="Valeur <i>conn_forum</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>conn_forum</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config VALUES ('conn_topic', 'Y')";
		$update['ok']="Valeur <i>conn_topic</i> ins�r�e dans table configuration";
		$update['nok']="Valeur <i>conn_topic</i> non ins�r�e dans table configuration";
		
		exec_request();
		
		affseparate();

		// #########################################
		// #### SUPPRESSION TABLE CONFIGURATION ####
		// #########################################
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='limitpmlength'";
		$update['ok']="Valeur obsol�te <i>limitpmlength</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>limitpmlength</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='limitusercitlength'";
		$update['ok']="Valeur obsol�te <i>limitusercitlength</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>limitusercitlength</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='limitusersignlength'";
		$update['ok']="Valeur obsol�te <i>limitusersignlength</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>limitusersignlength</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='statsconfig'";
		$update['ok']="Valeur obsol�te <i>statsconfig</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>statsconfig</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='timezone'";
		$update['ok']="Valeur obsol�te <i>timezone</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>timezone</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='activePersoLogo'";
		$update['ok']="Valeur obsol�te <i>activePersoLogo</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>activePersoLogo</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='activeDefaultLogo'";
		$update['ok']="Valeur obsol�te <i>activeDefaultLogo</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>activeDefaultLogo</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='logosparams'";
		$update['ok']="Valeur obsol�te <i>logosparams</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>logosparams</i> non supprim�e de la configuration";
		
		exec_request();
		
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='activateLogos'";
		$update['ok']="Valeur obsol�te <i>activateLogos</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>activateLogos</i> non supprim�e de la configuration";
		
		exec_request();

		$update['sql']="DELETE FROM ".$_PRE."config WHERE options = 'limitmsglength'";
		$update['ok']="Valeur obsol�te <i>limitmsglength</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>limitmsglength</i> non supprim�e de la configuration";
		
		exec_request();

		$update['sql']="DELETE FROM ".$_PRE."config WHERE options = 'editionlibre'";
		$update['ok']="Valeur obsol�te <i>editionlibre</i> supprim�e de la configuration";
		$update['nok']="Valeur obsol�te <i>editionlibre</i> non supprim�e de la configuration";
		
		exec_request();
		
		affseparate();

		// ###########################################
		// #### MODIFICATIONS TABLE CONFIGURATION ####
		// ###########################################

		$update['sql']="UPDATE ".$_PRE."config SET valeur='0' WHERE options='confirmparmail' AND valeur='N'";
		$update['ok']="Valeur <i>confirmparmail</i> de la configuration modifi�e";
		$update['nok']="Valeur <i>confirmparmail</i> de la configuration non modifi�e";
		
		exec_request();

		$update['sql']="UPDATE ".$_PRE."config SET valeur='3' WHERE options='confirmparmail' AND valeur='Y'";
		$update['ok']="Valeur <i>confirmparmail</i> de la configuration modifi�e";
		$update['nok']="Valeur <i>confirmparmail</i> de la configuration non modifi�e";
		
		exec_request();

		affseparate();
				
		$update['sql']="ALTER TABLE ".$_PRE."annonces ADD poll INT DEFAULT '0' NOT NULL";
		$update['ok']="Table annonces modifi�e";
		$update['nok']="Table annonces non modifi�e";
		
		exec_request();
		
		$query = $sql->query("SELECT id FROM ".$_PRE."skins GROUP BY id ORDER BY id");
		
		while(list($idskin)=mysql_fetch_array($query))
		{
			$update['sql']="INSERT INTO ".$_PRE."skins (id, propriete, valeur) VALUES ($idskin, 'searchcolor','orange')";
			$update['ok']="Table skins modifi�e";
			$update['nok']="Table skins non modifi�e";
			
			exec_request();
		}
		
		next_step();
		break;

	case 5:
		// #############################################
		// #### MODIFICATIONS TABLE USER / USERPLUS ####
		// #############################################
		
		$update['sql']="ALTER TABLE ".$_PRE."user ADD popuppm ENUM('Y','N') DEFAULT 'Y' NOT NULL AFTER notifypm";
		$update['ok']="Table ".$_PRE."user modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."user";
		
		exec_request();

		$update['sql']="ALTER TABLE ".$_PRE."user ADD wysiwyg ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL";
		$update['ok']="Table ".$_PRE."user modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."user";
		
		exec_request();

		$update['sql']="ALTER TABLE ".$_PRE."userplus ADD description TEXT NOT NULL AFTER sex";
		$update['ok']="Table ".$_PRE."userplus modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."userplus";
		
		exec_request();
		
		affseparate();

		// ###################################
		// #### MODIFICATIONS TABLE SKINS ####
		// ###################################
				
		$update['sql']="DELETE FROM ".$_PRE."skins WHERE propriete='coladm'";
		$update['ok']="Table ".$_PRE."skins modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."skins";
		
		exec_request();		

		$update['sql']="UPDATE ".$_PRE."skins SET propriete='grp4' WHERE propriete='colsupadm'";
		$update['ok']="Table ".$_PRE."skins modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."skins";
		
		exec_request();	
		
		$update['sql']="UPDATE ".$_PRE."skins SET propriete='grp3' WHERE propriete='colmodo'";
		$update['ok']="Table ".$_PRE."skins modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."skins";
		
		exec_request();
		
		$update['sql']="UPDATE ".$_PRE."skins SET propriete='grp2' WHERE propriete='colmb'";
		$update['ok']="Table ".$_PRE."skins modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."skins";
		
		exec_request();
		
		$update['sql']="UPDATE ".$_PRE."skins SET propriete='grp1' WHERE propriete='colinvit'";
		$update['ok']="Table ".$_PRE."skins modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."skins";
		
		exec_request();
		
		$update['sql']="UPDATE ".$_PRE."skins SET propriete='bangrp' WHERE propriete='colban'";
		$update['ok']="Table ".$_PRE."skins modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."skins";
		
		exec_request();		
			
		next_step();
		break;

	case 6:
		// ################################
		// #### MODIFICATIONS DIVERSES ####
		// ################################

		$update['sql']="ALTER TABLE ".$_PRE."session ADD typelieu ENUM( 'FOR', 'ACC', 'SEA', 'ADM', 'STA', 'HLP', 'PRO', 'TOP' ) DEFAULT 'ACC' NOT NULL , ADD forumid INT DEFAULT '0' NOT NULL , ADD topicid INT DEFAULT '0' NOT NULL";
		$update['ok']="Table ".$_PRE."session modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."session";
		
		exec_request();

		$update['sql']="ALTER TABLE ".$_PRE."forums DROP mbrights";
		$update['ok']="Table ".$_PRE."forums modifi�e";
		$update['nok']="Probl�me lors de la modification de la table ".$_PRE."forums";		
		
		exec_request();

		affseparate();

		// ###########################################
		// #### MODIFICATIONS DROITS DES ANNONCES ####
		// ###########################################
		
		$query = $sql->query("SELECT idpost, inforums FROM ".$_PRE."annonces ORDER BY idpost");
		if(!$query)
			die(mysql_error());
		$nb		=	mysql_num_rows($query);
		
		if($nb > 0)
		{
			while($Ann = mysql_fetch_array($query))
			{
				$transit = array();
				$inforums = "";
				$id = $Ann['idpost'];
				
				for($i=0;$i<strlen($Ann['inforums']);$i++)
				{
					if($Ann['inforums'][$i]=="1")
						$transit[]=$i+1;
				}
				
				if(count($transit) > 0)
					$inforums = "/".implode("/",$transit)."/";

				$update['sql']="UPDATE ".$_PRE."annonces SET inforums = '$inforums' WHERE idpost = $id";
				$update['ok']="Annonce n�<i>$id</i> mise � jour";
				$update['nok']="Probl�me lors de la mise � jour de l'annonce n�<i>$id</i>";
				
				exec_request();					
			}	
		}

		affseparate();

		// ##############################################
		// #### MODIFICATIONS DROITS DES MODERATEURS ####
		// ##############################################

		$query = $sql->query("SELECT forumident, idusermodo, modorights FROM ".$_PRE."moderateur");
		$nb = mysql_num_rows($query);
		
		if($nb>0)
		{
			while(list($idforum, $idmodo, $modorights) = mysql_fetch_array($query))
			{
				$right = getright2($modorights);

				$update['sql']="UPDATE ".$_PRE."moderateur SET modorights='$right' WHERE forumident='$idforum' AND idusermodo='$idmodo'";
				$update['ok']="Table ".$_PRE."moderateur mise � jour";
				$update['nok']="Table ".$_PRE."moderateur non mise � jour";		
				
				exec_request();				
			}
		}

		affseparate();

		$update['sql']="ALTER TABLE ".$_PRE."moderateur CHANGE modorights modorights INT DEFAULT '0' NOT NULL";
		$update['ok']="Table ".$_PRE."moderateur mise � jour";
		$update['nok']="Table ".$_PRE."moderateur non mise � jour";		
		
		exec_request();

		affseparate();

		// ######################################
		// #### SUPPRESSION TABLES OBSOLETES ####
		// ######################################

		$update['sql']="DROP TABLE ".$_PRE."forumperm";
		$update['ok']="Table obsol�te supprim�e";
		$update['nok']="Table obsol�te non supprim�e";		
		
		exec_request();

		affseparate();
		
		// #####################################
		// #### MODIFICATIONS VERSION DE DB ####
		// #####################################
				
		$update['sql']="UPDATE ".$_PRE."config SET valeur='0.8 beta' WHERE options='ForumDBVersion'";
		$update['ok']="Version de DB mise � jour";
		$update['nok']="Version de DB non mise � jour";		
		
		exec_request();
	
		next_step();
		break;		

	case 7:
		end_maj();
		break;							
}
