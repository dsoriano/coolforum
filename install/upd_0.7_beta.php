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

$update = array();

switch($_GET['steps'])
{
	case 1:
		
		// **** Création de la table userplus ****
		
		$update['sql']="CREATE TABLE "._PRE_."userplus (
				  idplus int(11) NOT NULL default '0',
				  question varchar(200) NOT NULL default '',
				  reponse varchar(200) NOT NULL default '',
				  icq varchar(10) NOT NULL default '',
				  showicq enum('Y','N') NOT NULL default 'N',
				  aim varchar(16) NOT NULL default '',
				  yahoomsg varchar(50) NOT NULL default '',
				  msn varchar(50) NOT NULL default '',
				  birth varchar(10) NOT NULL default '',
				  sex enum('M','F') NOT NULL default 'M',
				  mailorig varchar(200) NOT NULL default '',
				  UNIQUE KEY idplus (idplus))";
		$update['ok']="Table "._PRE_."userplus créée";
		$update['nok']="Problème lors de la création de la table "._PRE_."userplus";
		
		exec_request();
		
		affseparate();
		
		$update['sql']="INSERT INTO "._PRE_."userplus (idplus,question,reponse,icq,showicq,mailorig) SELECT userid,question,reponse,usericq,showicq,mailorig FROM "._PRE_."user";
		$update['ok']="Exportation des données membres réussie";
		$update['nok']="Problème lors de l'exportation des données membres";
		
		exec_request();

		affseparate();
				
		$update['sql']="ALTER TABLE "._PRE_."user DROP question";
		$update['ok']="Champ <i>question</i> supprimé";
		$update['nok']="Problème lors de la suppression du champ <i>question</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."user DROP reponse";
		$update['ok']="Champ <i>reponse</i> supprimé";
		$update['nok']="Problème lors de la suppression du champ <i>reponse</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."user DROP showicq";
		$update['ok']="Champ <i>showicq</i> supprimé";
		$update['nok']="Problème lors de la suppression du champ <i>showicq</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."user DROP usericq";
		$update['ok']="Champ <i>usericq</i> supprimé";
		$update['nok']="Problème lors de la suppression du champ <i>usericq</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."user DROP mailorig";
		$update['ok']="Champ <i>mailorig</i> supprimé";
		$update['nok']="Problème lors de la suppression du champ <i>mailorig</i>";
		
		exec_request();

		affseparate();
				
		$update['sql']="ALTER TABLE "._PRE_."user ADD lng VARCHAR( 5 ) NOT NULL AFTER timezone";
		$update['ok']="Champ <i>lng</i> ajouté";
		$update['nok']="Problème lors de l'ajout du champ <i>lng</i><br>";
		
		exec_request();
		
		$update['sql']="UPDATE "._PRE_."user SET lng='fr'";
		$update['ok']="Langage par défaut définit";
		$update['nok']="Problème lors de l'insertion du langage par défaut";
		
		exec_request();
		
		next_step();
		break;
	case 2:
	
		// **** Ajout de nouveaux index ****
		
		$update['sql']="ALTER TABLE "._PRE_."posts ADD INDEX (idforum)";
		$update['ok']="Nouvel index ajouté";
		$update['nok']="Problème sur l'index <i>idforum</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."posts ADD INDEX (idmembre)";
		$update['ok']="Nouvel index ajouté";
		$update['nok']="Problème sur l'index <i>idmembre</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."config ADD INDEX (options)";
		$update['ok']="Nouvel index ajouté";
		$update['nok']="Problème sur l'index <i>options</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."moderateur ADD INDEX (forumident)";
		$update['ok']="Nouvel index ajouté";
		$update['nok']="Problème sur l'index <i>forumident</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."forumperm ADD INDEX (userid)";
		$update['ok']="Nouvel index ajouté";
		$update['nok']="Problème sur l'index <i>userid</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE "._PRE_."banlist ADD INDEX (userid)";
		$update['ok']="Nouvel index ajouté";
		$update['nok']="Problème sur l'index <i>userid</i>";
		
		exec_request();
		
		affseparate();
		
		// **** suppression des couleurs persos ****
		
		$update['sql']="DROP TABLE "._PRE_."persocolor";
		$update['ok']="Table "._PRE_."persocolor supprimée";
		$update['nok']="Problème lors de la suppression de la table "._PRE_."persocolor";
		
		exec_request();

		$update['sql']="ALTER TABLE "._PRE_."user DROP persocolor";
		$update['ok']="Champ <i>persocolor</i> supprimé";
		$update['nok']="Problème lors de la suppression du champ <i>persocolor</i>";
		
		exec_request();
		
		affseparate();			

		$update['sql']="ALTER TABLE "._PRE_."posts ADD notifyme ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL";
		$update['ok']="Champ <i>notifyme</i> inséré";
		$update['nok']="Problème lors de l'insertion du champ <i>notifyme</i>";
		
		exec_request();

		next_step();
		break;

	case 3:
	
		// **** insertions dans la table de configuration ****
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('defaultlangage', 'fr')";
		$update['ok']="Option <i>defaultlangage</i> insérée";
		$update['nok']="Option <i>defaultlangage</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('emailmask', '|at|')";
		$update['ok']="Option <i>emailmask</i> insérée";
		$update['nok']="Option <i>emailmask</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('statnbtopics', '0')";
		$update['ok']="Option <i>statnbtopics</i> insérée";
		$update['nok']="Option <i>statnbtopics</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('statnbposts', '0')";
		$update['ok']="Option <i>statnbposts</i> insérée";
		$update['nok']="Option <i>statnbposts</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('statnbuser', '0')";
		$update['ok']="Option <i>statnbuser</i> insérée";
		$update['nok']="Option <i>statnbuser</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('statlastmember', '')";
		$update['ok']="Option <i>statlastmember</i> insérée";
		$update['nok']="Option <i>statlastmember</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('defaultskin', '1')";
		$update['ok']="Option <i>defaultskin</i> insérée";
		$update['nok']="Option <i>defaultskin</i> non insérée";
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('statsconfig', '111111')";
		$update['ok']="Option <i>statsconfig</i> insérée";
		$update['nok']="Option <i>statsconfig</i> non insérée";
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('logosparams', '150-150-15')";
		$update['ok']="Option <i>logosparams</i> insérée";
		$update['nok']="Option <i>logosparams</i> non insérée";
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('activateLogos', 'Y')";
		$update['ok']="Option <i>activateLogos</i> insérée";
		$update['nok']="Option <i>activateLogos</i> non insérée";
		
		exec_request();
				
		$update['sql']="DELETE FROM "._PRE_."config WHERE options='modocanban' OR options='persocolor'";
		$update['ok']="Données obsolètes supprimées";
		$update['nok']="Données obsolètes non supprimées";
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('closeregmsg', 'Les inscriptions sont actuellement closes. Nous n\'acceptons plus de membres.')";
		$update['ok']="Insertion <b>closeregmsg</b> dans "._PRE_."config effectuée";
		$update['nok']="Insertion <b>closeregmsg</b> dans "._PRE_."config non effectuée";		

		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('mustbeidentify', 'N')";
		$update['ok']="Valeur <i>mustbeidentify</i> insérée dans "._PRE_."config";
		$update['nok']="Valeur <i>mustbeidentify</i> non insérée dans "._PRE_."config";		
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('mailfunction', 'normal')";
		$update['ok']="Valeur <i>mailfunction</i> insérée dans "._PRE_."config";
		$update['nok']="Valeur <i>mailfunction</i> non insérée dans "._PRE_."config";		
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('censuredwords', '')";
		$update['ok']="Valeur <i>censuredwords</i> insérée dans "._PRE_."config";
		$update['nok']="Valeur <i>censuredwords</i> non insérée dans "._PRE_."config";		
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('usepub', 'N')";
		$update['ok']="Valeur <i>usepub</i> insérée dans "._PRE_."config";
		$update['nok']="Valeur <i>usepub</i> non insérée dans "._PRE_."config";		
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('usemails', 'Y')";
		$update['ok']="Valeur <i>usemails</i> insérée dans "._PRE_."config";
		$update['nok']="Valeur <i>usemails</i> non insérée dans "._PRE_."config";		
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."config (options,valeur) VALUES ('editionlibre', 'N');";
		$update['ok']="Valeur <i>editionlibre</i> insérée dans "._PRE_."config";
		$update['nok']="Valeur <i>editionlibre</i> non insérée dans "._PRE_."config";		
		
		exec_request();
				
		affseparate();
		
		$query=$sql->query("SELECT COUNT(*) AS nbtopics FROM "._PRE_."posts WHERE parent='0'")->execute();
		list($nbtopics)=$query->fetch_array();
	
		$update['sql']="UPDATE "._PRE_."config SET valeur='$nbtopics' WHERE options='statnbtopics'";
		$update['ok']="Stats sujets mises à jour";
		$update['nok']="Stats sujets non mises à jour";
		
		exec_request();

		$query=$sql->query("SELECT COUNT(*) AS nbposts FROM "._PRE_."posts WHERE parent>'0'")->execute();
		list($nbposts)=$query->fetch_array();
	
		$update['sql']="UPDATE "._PRE_."config SET valeur='$nbposts' WHERE options='statnbposts'";
		$update['ok']="Stats réponses mises à jour";
		$update['nok']="Stats réponses non mises à jour";		
		
		exec_request();

		$query=$sql->query("SELECT COUNT(*) AS nbmb FROM "._PRE_."user")->execute();
		list($nbmb)=$query->fetch_array();
		
		$query=$sql->query("SELECT login FROM "._PRE_."user ORDER BY registerdate DESC LIMIT 0,1")->execute();
		list($lastpseudo)=$query->fetch_array();
		
		$update['sql']="UPDATE "._PRE_."config SET valeur='$nbmb' WHERE options='statnbuser'";
		$update['ok']="Stats nombre de membres mises à jour";
		$update['nok']="Stats nombre de membres non mises à jour";		
		
		exec_request();		

		$update['sql']="UPDATE "._PRE_."config SET valeur='$lastpseudo' WHERE options='statlastmember'";
		$update['ok']="Stats dernier membre mises à jour";
		$update['nok']="Stats dernier membre non mises à jour";		
		
		exec_request();
		
		next_step();	
		break;
		
	case 4:
	
		$update['sql']="CREATE TABLE "._PRE_."language (
				  id int(11) NOT NULL auto_increment,
				  code varchar(6) NOT NULL default '',
				  langue varchar(20) NOT NULL default '',
				  PRIMARY KEY  (id)
				)";
		$update['ok']="Table "._PRE_."language créée";
		$update['nok']="Problème lors de la création de la table "._PRE_."language";		
		
		exec_request();	

		$update['sql']="INSERT INTO "._PRE_."language VALUES (1, 'fr', 'français')";
		$update['ok']="Langage <i>français</i> inséré";
		$update['nok']="Langage <i>français</i> non inséré";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="DROP TABLE "._PRE_."session";
		$update['ok']="Table "._PRE_."session supprimée";
		$update['nok']="Table "._PRE_."session non supprimée";		
		
		exec_request();
		
		$update['sql']="CREATE TABLE "._PRE_."session (
				  sessionID varchar(200) NOT NULL default '',
				  username varchar(100) NOT NULL default '0',
				  userid int(11) NOT NULL default '0',
				  userstatus smallint(6) NOT NULL default '0',
				  time int(11) NOT NULL default '0',
				  UNIQUE KEY sessionID (sessionID)
				)";
		$update['ok']="Table "._PRE_."session recréée";
		$update['nok']="Impossible de recréer "._PRE_."session";		
		
		exec_request();
		
		next_step();
		break;
		
	case 5:
		$update['sql']="CREATE TABLE "._PRE_."posts2 (
				  idpost int(11) NOT NULL auto_increment,
				  idforum int(10) default NULL,
				  sujet varchar(200) default NULL,
				  date bigint(20) default NULL,
				  parent int(10) default NULL,
				  msg text,
				  nbrep int(10) default NULL,
				  nbvues int(10) default NULL,
				  datederrep bigint(20) default NULL,
				  derposter varchar(100) default NULL,
				  idderpost int(11) NOT NULL default '0',
				  icone varchar(50) default NULL,
				  idmembre int(10) default NULL,
				  pseudo varchar(100) NOT NULL default '',
				  postip varchar(15) NOT NULL default '',
				  opentopic char(1) NOT NULL default '',
				  smiles enum('Y','N') NOT NULL default 'Y',
				  bbcode enum('Y','N') NOT NULL default 'Y',
				  poll int(11) NOT NULL default '0',
				  notifyme enum('Y','N') NOT NULL default 'N',
				  PRIMARY KEY  (idpost),
				  KEY idforum (idforum),
				  KEY idmembre (idmembre)
				)";
		$update['ok']="Table "._PRE_."posts2 créée";
		$update['nok']="Impossible de créer "._PRE_."posts2";		
		
		exec_request();

		$update['sql']="CREATE TABLE "._PRE_."tampon (
				  idpost int(11) NOT NULL auto_increment,
				  pseudo varchar(100) NOT NULL default '',
				  PRIMARY KEY  (idpost)
				)";
		$update['ok']="Table "._PRE_."tampon créée";
		$update['nok']="Impossible de créer "._PRE_."tampon";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="INSERT INTO "._PRE_."tampon (idpost,pseudo) SELECT "._PRE_."posts.idpost,"._PRE_."user.login FROM "._PRE_."posts LEFT JOIN "._PRE_."user ON "._PRE_."posts.idmembre="._PRE_."user.userid WHERE "._PRE_."posts.idmembre>0";
		$update['ok']="Insertion des membres dans "._PRE_."tampon";
		$update['nok']="Problème d'insertion des membres dans "._PRE_."tampon";		
		
		exec_request();	
		
		$update['sql']="INSERT INTO "._PRE_."tampon (idpost,pseudo) SELECT "._PRE_."posts.idpost,"._PRE_."guest.guestname FROM "._PRE_."posts LEFT JOIN "._PRE_."guest ON "._PRE_."posts.idpost="._PRE_."guest.idguestpost WHERE "._PRE_."posts.idmembre=0";
		$update['ok']="Insertion des invités dans "._PRE_."tampon";
		$update['nok']="Problème d'insertion des invités dans "._PRE_."tampon";		
		
		exec_request();
		
		next_step();
		break;
	
	case 6:
		$update['sql']="INSERT INTO "._PRE_."posts2 SELECT 
					"._PRE_."posts.idpost,
					"._PRE_."posts.idforum,
					"._PRE_."posts.sujet,
					"._PRE_."posts.date,
					"._PRE_."posts.parent,
					"._PRE_."posts.msg,
					"._PRE_."posts.nbrep,
					"._PRE_."posts.nbvues,
					"._PRE_."posts.datederrep,
					"._PRE_."posts.derposter,
					"._PRE_."posts.idderpost,
					"._PRE_."posts.icone,
					"._PRE_."posts.idmembre,
					"._PRE_."tampon.pseudo,
					"._PRE_."posts.postip,
					"._PRE_."posts.opentopic,
					"._PRE_."posts.smiles,
					"._PRE_."posts.bbcode,
					"._PRE_."posts.poll,
					"._PRE_."posts.notifyme
					FROM "._PRE_."posts
					LEFT JOIN "._PRE_."tampon ON "._PRE_."tampon.idpost="._PRE_."posts.idpost";
		$update['ok']="Insertion dans "._PRE_."posts2 réussie";
		$update['nok']="Insertion dans "._PRE_."posts2 échouée";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="DROP TABLE "._PRE_."posts";
		$update['ok']="Suppression "._PRE_."posts réussie";
		$update['nok']="Suppression "._PRE_."posts échouée";		
		
		exec_request();
		
		$update['sql']="DROP TABLE "._PRE_."guest";
		$update['ok']="Suppression "._PRE_."guest réussie";
		$update['nok']="Suppression "._PRE_."guest échouée";		
		
		exec_request();
		
		$update['sql']="DROP TABLE "._PRE_."tampon";
		$update['ok']="Suppression "._PRE_."tampon réussie";
		$update['nok']="Suppression "._PRE_."tampon échouée";		
		
		exec_request();	
		
		affseparate();
		
		$update['sql']="ALTER TABLE "._PRE_."posts2 RENAME "._PRE_."posts";
		$update['ok']="Table "._PRE_."posts2 renommée";
		$update['nok']="Table "._PRE_."posts2 non renommée";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="ALTER TABLE "._PRE_."user ADD lastpost INT NOT NULL, ADD lastvisit INT NOT NULL, ADD nbpmtot INT NOT NULL, ADD nbpmvu INT NOT NULL, ADD mailing ENUM( 'Y', 'N' ) DEFAULT 'Y' NOT NULL, DROP notifymsg";
		$update['ok']="Table "._PRE_."user modifiée";
		$update['nok']="Table "._PRE_."user non modifiée";		
		
		exec_request();		
		
		next_step();
		break;
	
	case 7:
	
		// **** Création de la nouvelle table de skins ****
		
		$update['sql']="DROP TABLE "._PRE_."skins";
		$update['ok']="Suppression "._PRE_."skins réussie";
		$update['nok']="Suppression "._PRE_."skins échouée";		
		
		exec_request();		
		
		$update['sql']="CREATE TABLE "._PRE_."skins (
				  id smallint(6) NOT NULL default '0',
				  propriete varchar(15) NOT NULL default '',
				  valeur varchar(15) NOT NULL default '',
				  KEY id (id)
				)";
		$update['ok']="Table "._PRE_."skins recréée";
		$update['nok']="Table "._PRE_."skins non recréée";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bg1', '#909090');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bg2', '#C1C1C1');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bordercolor', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bgtable1', '#537FAC');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bgtable2', '#A4B6C9');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bgtable3', '#265789');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'colsupadm', 'red');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'coladm', 'purple');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'colmodo', 'blue');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'colmb', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'colinvit', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'colban', 'yellow');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'lien1col', 'white');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'lien1visit', 'white');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'lien1hov', 'white');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'lien2col', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'lien2visit', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'lien2hov', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'textcol1', 'white');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'textcol2', 'black');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'smallfont', '1');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'middlefont', '2');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'bigfont', '3');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'repimg', 'defaut');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'reptpl', 'defaut');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'skinname', 'Defaut');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();
		
		$update['sql']="INSERT INTO "._PRE_."skins VALUES (1, 'font', 'verdana');";
		$update['ok']="Donnée skin intégrée";
		$update['nok']="Donnée skin non intégrée";		
		
		exec_request();	

		$update['sql']="INSERT INTO "._PRE_."skins (id,propriete,valeur) VALUES ('1', 'affdegrad', 'Y');";
		$update['ok']="Valeur <i>affdegrad</i> insérée dans "._PRE_."skins";
		$update['nok']="Valeur <i>affdegrad</i> non insérée dans "._PRE_."skins";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="UPDATE "._PRE_."user SET skin='1'";
		$update['ok']="skin membres mis à jour";
		$update['nok']="skin membres non mis à jour";		
		
		exec_request();
		
		next_step();
		break;


	case 8:
	
		$update['sql']="ALTER TABLE "._PRE_."categorie ADD catcoment VARCHAR(250) NOT NULL AFTER cattitle";
		$update['ok']="Table "._PRE_."categorie mise à jour";
		$update['nok']="Table "._PRE_."categorie non mise à jour";		
		
		exec_request();

		$update['sql']="CREATE TABLE "._PRE_."topics (
				  idtopic int(11) NOT NULL auto_increment,
				  idforum int(11) NOT NULL default '0',
				  sujet varchar(200) NOT NULL default '',
				  date bigint(20) NOT NULL default '0',
				  nbrep int(11) NOT NULL default '0',
				  nbvues int(11) NOT NULL default '0',
				  datederrep bigint(20) NOT NULL default '0',
				  derposter varchar(100) NOT NULL default '',
				  idderpost int(11) NOT NULL default '0',
				  icone varchar(50) NOT NULL default '',
				  idmembre int(11) NOT NULL default '0',
				  pseudo varchar(100) NOT NULL default '',
				  opentopic char(1) NOT NULL default 'Y',
				  poll int(11) NOT NULL default '0',
				  postit char(1) NOT NULL default '0',
				  PRIMARY KEY  (idtopic),
				  KEY datederrep (datederrep)
				)";
		$update['ok']="Table "._PRE_."topics créée";
		$update['nok']="Table "._PRE_."topics non créée";		
		
		exec_request();

		$update['sql']="INSERT INTO "._PRE_."topics (idtopic,idforum,sujet,date,nbrep,nbvues,datederrep,derposter,idderpost,icone,idmembre,pseudo,opentopic,poll) 
					SELECT 
					idpost,idforum,sujet,date,nbrep,nbvues,datederrep,derposter,idderpost,icone,idmembre,pseudo,opentopic,poll FROM "._PRE_."posts WHERE parent=0";
		$update['ok']="Insertions dans "._PRE_."topics effectuées";
		$update['nok']="Insertions dans "._PRE_."topics non effectuées";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="UPDATE "._PRE_."posts SET parent=idpost WHERE parent=0";
		$update['ok']="Mise à jour "._PRE_."posts effectuée";
		$update['nok']="Mise à jour "._PRE_."posts non effectuée";		

		exec_request();

		$update['sql']="ALTER TABLE "._PRE_."posts DROP nbrep ,
					DROP nbvues ,
					DROP datederrep ,
					DROP derposter ,
					DROP idderpost ,
					DROP opentopic";
		$update['ok']="Suppression des informations obsolètes "._PRE_."posts effectuée";
		$update['nok']="Suppression des informations obsolètes "._PRE_."posts non effectuée";
		
		exec_request();
			
		next_step();
		break;	

	case 9:
	
		$update['sql']="UPDATE "._PRE_."moderateur SET modorights=CONCAT(modorights,'1')";
		$update['ok']="Mise à jour "._PRE_."moderateur effectuée";
		$update['nok']="Mise à jour "._PRE_."moderateur non effectuée";		

		exec_request();
		
		affseparate();

		$update['sql']="ALTER TABLE "._PRE_."annonces DROP nbrep";
		$update['ok']="Table "._PRE_."annonces modifiée";
		$update['nok']="Table "._PRE_."annonces non modifiée";		
		
		exec_request();

		$update['sql']="ALTER TABLE "._PRE_."annonces DROP idderpost";
		$update['ok']="Table "._PRE_."annonces modifiée";
		$update['nok']="Table "._PRE_."annonces non modifiée";		
		
		exec_request();

		affseparate();

		$update['sql']="CREATE TABLE "._PRE_."campagnes (
				  id int(11) NOT NULL auto_increment,
				  nom varchar(250) NOT NULL default '',
				  url varchar(250) NOT NULL default '',
				  banniere varchar(250) NOT NULL default '',
				  typefin enum('click','date','aff') NOT NULL default 'click',
				  dtedebut int(11) NOT NULL default '0',
				  fincamp int(11) NOT NULL default '0',
				  ratio int(1) NOT NULL default '0',
				  nbaffichages int(11) NOT NULL default '0',
				  clicks int(11) NOT NULL default '0',
				  regie text NOT NULL,
				  lastvue int(11) NOT NULL default '0',
				  todayvue int(11) NOT NULL default '0',
				  todayclick int(11) NOT NULL default '0',
				  PRIMARY KEY  (id),
				  KEY dtedebut (dtedebut)
				)";
		$update['ok']="Table "._PRE_."campagnes créée";
		$update['nok']="Table "._PRE_."campagnes non créée";		
		
		exec_request();	

		$update['sql']="CREATE TABLE "._PRE_."statcamp (
				  iddate varchar(13) NOT NULL default '',
				  vu int(11) NOT NULL default '0',
				  clicks int(11) NOT NULL default '0',
				  UNIQUE KEY iddate (iddate)
				)";
		$update['ok']="Table "._PRE_."statcamp créée";
		$update['nok']="Table "._PRE_."statcamp non créée";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="CREATE TABLE "._PRE_."mailing (
				id INT NOT NULL AUTO_INCREMENT ,
				date BIGINT NOT NULL ,
				titre VARCHAR( 250 ) NOT NULL ,
				message TEXT NOT NULL ,
				type ENUM( 'text', 'html' ) NOT NULL ,
				PRIMARY KEY (id) 
				)";
		$update['ok']="Table "._PRE_."mailing créée";
		$update['nok']="Table "._PRE_."mailing non créée";		
		
		exec_request();
		
		affseparate();

		$update['sql']="UPDATE "._PRE_."config SET valeur='0.7 beta' WHERE options='ForumDBVersion'";
		$update['ok']="Version de DB mise à jour";
		$update['nok']="Version de DB non mise à jour";		
		
		exec_request();

		next_step();
		break;	
		
	case 10:
		end_maj();
		break;							
}
