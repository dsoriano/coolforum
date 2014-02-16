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

$update = array();

switch($_GET['steps'])
{
	case 1:
		
		// **** Cr�ation de la table userplus ****
		
		$update['sql']="CREATE TABLE ".$_PRE."userplus (
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
		$update['ok']="Table ".$_PRE."userplus cr��e";
		$update['nok']="Probl�me lors de la cr�ation de la table ".$_PRE."userplus";
		
		exec_request();
		
		affseparate();
		
		$update['sql']="INSERT INTO ".$_PRE."userplus (idplus,question,reponse,icq,showicq,mailorig) SELECT userid,question,reponse,usericq,showicq,mailorig FROM ".$_PRE."user";
		$update['ok']="Exportation des donn�es membres r�ussie";
		$update['nok']="Probl�me lors de l'exportation des donn�es membres";
		
		exec_request();

		affseparate();
				
		$update['sql']="ALTER TABLE ".$_PRE."user DROP question";
		$update['ok']="Champ <i>question</i> supprim�";
		$update['nok']="Probl�me lors de la suppression du champ <i>question</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."user DROP reponse";
		$update['ok']="Champ <i>reponse</i> supprim�";
		$update['nok']="Probl�me lors de la suppression du champ <i>reponse</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."user DROP showicq";
		$update['ok']="Champ <i>showicq</i> supprim�";
		$update['nok']="Probl�me lors de la suppression du champ <i>showicq</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."user DROP usericq";
		$update['ok']="Champ <i>usericq</i> supprim�";
		$update['nok']="Probl�me lors de la suppression du champ <i>usericq</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."user DROP mailorig";
		$update['ok']="Champ <i>mailorig</i> supprim�";
		$update['nok']="Probl�me lors de la suppression du champ <i>mailorig</i>";
		
		exec_request();

		affseparate();
				
		$update['sql']="ALTER TABLE ".$_PRE."user ADD lng VARCHAR( 5 ) NOT NULL AFTER timezone";
		$update['ok']="Champ <i>lng</i> ajout�";
		$update['nok']="Probl�me lors de l'ajout du champ <i>lng</i><br>";
		
		exec_request();
		
		$update['sql']="UPDATE ".$_PRE."user SET lng='fr'";
		$update['ok']="Langage par d�faut d�finit";
		$update['nok']="Probl�me lors de l'insertion du langage par d�faut";
		
		exec_request();
		
		next_step();
		break;
	case 2:
	
		// **** Ajout de nouveaux index ****
		
		$update['sql']="ALTER TABLE ".$_PRE."posts ADD INDEX (idforum)";
		$update['ok']="Nouvel index ajout�";
		$update['nok']="Probl�me sur l'index <i>idforum</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."posts ADD INDEX (idmembre)";
		$update['ok']="Nouvel index ajout�";
		$update['nok']="Probl�me sur l'index <i>idmembre</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."config ADD INDEX (options)";
		$update['ok']="Nouvel index ajout�";
		$update['nok']="Probl�me sur l'index <i>options</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."moderateur ADD INDEX (forumident)";
		$update['ok']="Nouvel index ajout�";
		$update['nok']="Probl�me sur l'index <i>forumident</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."forumperm ADD INDEX (userid)";
		$update['ok']="Nouvel index ajout�";
		$update['nok']="Probl�me sur l'index <i>userid</i>";
		
		exec_request();
		
		$update['sql']="ALTER TABLE ".$_PRE."banlist ADD INDEX (userid)";
		$update['ok']="Nouvel index ajout�";
		$update['nok']="Probl�me sur l'index <i>userid</i>";
		
		exec_request();
		
		affseparate();
		
		// **** suppression des couleurs persos ****
		
		$update['sql']="DROP TABLE ".$_PRE."persocolor";
		$update['ok']="Table ".$_PRE."persocolor supprim�e";
		$update['nok']="Probl�me lors de la suppression de la table ".$_PRE."persocolor";
		
		exec_request();

		$update['sql']="ALTER TABLE ".$_PRE."user DROP persocolor";
		$update['ok']="Champ <i>persocolor</i> supprim�";
		$update['nok']="Probl�me lors de la suppression du champ <i>persocolor</i>";
		
		exec_request();
		
		affseparate();			

		$update['sql']="ALTER TABLE ".$_PRE."posts ADD notifyme ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL";
		$update['ok']="Champ <i>notifyme</i> ins�r�";
		$update['nok']="Probl�me lors de l'insertion du champ <i>notifyme</i>";
		
		exec_request();

		next_step();
		break;

	case 3:
	
		// **** insertions dans la table de configuration ****
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('defaultlangage', 'fr')";
		$update['ok']="Option <i>defaultlangage</i> ins�r�e";
		$update['nok']="Option <i>defaultlangage</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('emailmask', '|at|')";
		$update['ok']="Option <i>emailmask</i> ins�r�e";
		$update['nok']="Option <i>emailmask</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('statnbtopics', '0')";
		$update['ok']="Option <i>statnbtopics</i> ins�r�e";
		$update['nok']="Option <i>statnbtopics</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('statnbposts', '0')";
		$update['ok']="Option <i>statnbposts</i> ins�r�e";
		$update['nok']="Option <i>statnbposts</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('statnbuser', '0')";
		$update['ok']="Option <i>statnbuser</i> ins�r�e";
		$update['nok']="Option <i>statnbuser</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('statlastmember', '')";
		$update['ok']="Option <i>statlastmember</i> ins�r�e";
		$update['nok']="Option <i>statlastmember</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('defaultskin', '1')";
		$update['ok']="Option <i>defaultskin</i> ins�r�e";
		$update['nok']="Option <i>defaultskin</i> non ins�r�e";
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('statsconfig', '111111')";
		$update['ok']="Option <i>statsconfig</i> ins�r�e";
		$update['nok']="Option <i>statsconfig</i> non ins�r�e";
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('logosparams', '150-150-15')";
		$update['ok']="Option <i>logosparams</i> ins�r�e";
		$update['nok']="Option <i>logosparams</i> non ins�r�e";
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('activateLogos', 'Y')";
		$update['ok']="Option <i>activateLogos</i> ins�r�e";
		$update['nok']="Option <i>activateLogos</i> non ins�r�e";
		
		exec_request();
				
		$update['sql']="DELETE FROM ".$_PRE."config WHERE options='modocanban' OR options='persocolor'";
		$update['ok']="Donn�es obsol�tes supprim�es";
		$update['nok']="Donn�es obsol�tes non supprim�es";
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('closeregmsg', 'Les inscriptions sont actuellement closes. Nous n\'acceptons plus de membres.')";
		$update['ok']="Insertion <b>closeregmsg</b> dans ".$_PRE."config effectu�e";
		$update['nok']="Insertion <b>closeregmsg</b> dans ".$_PRE."config non effectu�e";		

		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('mustbeidentify', 'N')";
		$update['ok']="Valeur <i>mustbeidentify</i> ins�r�e dans ".$_PRE."config";
		$update['nok']="Valeur <i>mustbeidentify</i> non ins�r�e dans ".$_PRE."config";		
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('mailfunction', 'normal')";
		$update['ok']="Valeur <i>mailfunction</i> ins�r�e dans ".$_PRE."config";
		$update['nok']="Valeur <i>mailfunction</i> non ins�r�e dans ".$_PRE."config";		
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('censuredwords', '')";
		$update['ok']="Valeur <i>censuredwords</i> ins�r�e dans ".$_PRE."config";
		$update['nok']="Valeur <i>censuredwords</i> non ins�r�e dans ".$_PRE."config";		
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('usepub', 'N')";
		$update['ok']="Valeur <i>usepub</i> ins�r�e dans ".$_PRE."config";
		$update['nok']="Valeur <i>usepub</i> non ins�r�e dans ".$_PRE."config";		
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('usemails', 'Y')";
		$update['ok']="Valeur <i>usemails</i> ins�r�e dans ".$_PRE."config";
		$update['nok']="Valeur <i>usemails</i> non ins�r�e dans ".$_PRE."config";		
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."config (options,valeur) VALUES ('editionlibre', 'N');";
		$update['ok']="Valeur <i>editionlibre</i> ins�r�e dans ".$_PRE."config";
		$update['nok']="Valeur <i>editionlibre</i> non ins�r�e dans ".$_PRE."config";		
		
		exec_request();
				
		affseparate();
		
		$query=$sql->query("SELECT COUNT(*) AS nbtopics FROM ".$_PRE."posts WHERE parent='0'");
		list($nbtopics)=mysql_fetch_array($query);
	
		$update['sql']="UPDATE ".$_PRE."config SET valeur='$nbtopics' WHERE options='statnbtopics'";
		$update['ok']="Stats sujets mises � jour";
		$update['nok']="Stats sujets non mises � jour";
		
		exec_request();

		$query=$sql->query("SELECT COUNT(*) AS nbposts FROM ".$_PRE."posts WHERE parent>'0'");
		list($nbposts)=mysql_fetch_array($query);
	
		$update['sql']="UPDATE ".$_PRE."config SET valeur='$nbposts' WHERE options='statnbposts'";
		$update['ok']="Stats r�ponses mises � jour";
		$update['nok']="Stats r�ponses non mises � jour";		
		
		exec_request();

		$query=$sql->query("SELECT COUNT(*) AS nbmb FROM ".$_PRE."user");
		list($nbmb)=mysql_fetch_array($query);
		
		$query=$sql->query("SELECT login FROM ".$_PRE."user ORDER BY registerdate DESC LIMIT 0,1");
		list($lastpseudo)=mysql_fetch_array($query);
		
		$update['sql']="UPDATE ".$_PRE."config SET valeur='$nbmb' WHERE options='statnbuser'";
		$update['ok']="Stats nombre de membres mises � jour";
		$update['nok']="Stats nombre de membres non mises � jour";		
		
		exec_request();		

		$update['sql']="UPDATE ".$_PRE."config SET valeur='$lastpseudo' WHERE options='statlastmember'";
		$update['ok']="Stats dernier membre mises � jour";
		$update['nok']="Stats dernier membre non mises � jour";		
		
		exec_request();
		
		next_step();	
		break;
		
	case 4:
	
		$update['sql']="CREATE TABLE ".$_PRE."language (
				  id int(11) NOT NULL auto_increment,
				  code varchar(6) NOT NULL default '',
				  langue varchar(20) NOT NULL default '',
				  PRIMARY KEY  (id)
				)";
		$update['ok']="Table ".$_PRE."language cr��e";
		$update['nok']="Probl�me lors de la cr�ation de la table ".$_PRE."language";		
		
		exec_request();	

		$update['sql']="INSERT INTO ".$_PRE."language VALUES (1, 'fr', 'fran�ais')";
		$update['ok']="Langage <i>fran�ais</i> ins�r�";
		$update['nok']="Langage <i>fran�ais</i> non ins�r�";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="DROP TABLE ".$_PRE."session";
		$update['ok']="Table ".$_PRE."session supprim�e";
		$update['nok']="Table ".$_PRE."session non supprim�e";		
		
		exec_request();
		
		$update['sql']="CREATE TABLE ".$_PRE."session (
				  sessionID varchar(200) NOT NULL default '',
				  username varchar(100) NOT NULL default '0',
				  userid int(11) NOT NULL default '0',
				  userstatus smallint(6) NOT NULL default '0',
				  time int(11) NOT NULL default '0',
				  UNIQUE KEY sessionID (sessionID)
				)";
		$update['ok']="Table ".$_PRE."session recr��e";
		$update['nok']="Impossible de recr�er ".$_PRE."session";		
		
		exec_request();
		
		next_step();
		break;
		
	case 5:
		$update['sql']="CREATE TABLE ".$_PRE."posts2 (
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
		$update['ok']="Table ".$_PRE."posts2 cr��e";
		$update['nok']="Impossible de cr�er ".$_PRE."posts2";		
		
		exec_request();

		$update['sql']="CREATE TABLE ".$_PRE."tampon (
				  idpost int(11) NOT NULL auto_increment,
				  pseudo varchar(100) NOT NULL default '',
				  PRIMARY KEY  (idpost)
				)";
		$update['ok']="Table ".$_PRE."tampon cr��e";
		$update['nok']="Impossible de cr�er ".$_PRE."tampon";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="INSERT INTO ".$_PRE."tampon (idpost,pseudo) SELECT ".$_PRE."posts.idpost,".$_PRE."user.login FROM ".$_PRE."posts LEFT JOIN ".$_PRE."user ON ".$_PRE."posts.idmembre=".$_PRE."user.userid WHERE ".$_PRE."posts.idmembre>0";
		$update['ok']="Insertion des membres dans ".$_PRE."tampon";
		$update['nok']="Probl�me d'insertion des membres dans ".$_PRE."tampon";		
		
		exec_request();	
		
		$update['sql']="INSERT INTO ".$_PRE."tampon (idpost,pseudo) SELECT ".$_PRE."posts.idpost,".$_PRE."guest.guestname FROM ".$_PRE."posts LEFT JOIN ".$_PRE."guest ON ".$_PRE."posts.idpost=".$_PRE."guest.idguestpost WHERE ".$_PRE."posts.idmembre=0";
		$update['ok']="Insertion des invit�s dans ".$_PRE."tampon";
		$update['nok']="Probl�me d'insertion des invit�s dans ".$_PRE."tampon";		
		
		exec_request();
		
		next_step();
		break;
	
	case 6:
		$update['sql']="INSERT INTO ".$_PRE."posts2 SELECT 
					".$_PRE."posts.idpost,
					".$_PRE."posts.idforum,
					".$_PRE."posts.sujet,
					".$_PRE."posts.date,
					".$_PRE."posts.parent,
					".$_PRE."posts.msg,
					".$_PRE."posts.nbrep,
					".$_PRE."posts.nbvues,
					".$_PRE."posts.datederrep,
					".$_PRE."posts.derposter,
					".$_PRE."posts.idderpost,
					".$_PRE."posts.icone,
					".$_PRE."posts.idmembre,
					".$_PRE."tampon.pseudo,
					".$_PRE."posts.postip,
					".$_PRE."posts.opentopic,
					".$_PRE."posts.smiles,
					".$_PRE."posts.bbcode,
					".$_PRE."posts.poll,
					".$_PRE."posts.notifyme
					FROM ".$_PRE."posts
					LEFT JOIN ".$_PRE."tampon ON ".$_PRE."tampon.idpost=".$_PRE."posts.idpost";
		$update['ok']="Insertion dans ".$_PRE."posts2 r�ussie";
		$update['nok']="Insertion dans ".$_PRE."posts2 �chou�e";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="DROP TABLE ".$_PRE."posts";
		$update['ok']="Suppression ".$_PRE."posts r�ussie";
		$update['nok']="Suppression ".$_PRE."posts �chou�e";		
		
		exec_request();
		
		$update['sql']="DROP TABLE ".$_PRE."guest";
		$update['ok']="Suppression ".$_PRE."guest r�ussie";
		$update['nok']="Suppression ".$_PRE."guest �chou�e";		
		
		exec_request();
		
		$update['sql']="DROP TABLE ".$_PRE."tampon";
		$update['ok']="Suppression ".$_PRE."tampon r�ussie";
		$update['nok']="Suppression ".$_PRE."tampon �chou�e";		
		
		exec_request();	
		
		affseparate();
		
		$update['sql']="ALTER TABLE ".$_PRE."posts2 RENAME ".$_PRE."posts";
		$update['ok']="Table ".$_PRE."posts2 renomm�e";
		$update['nok']="Table ".$_PRE."posts2 non renomm�e";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="ALTER TABLE ".$_PRE."user ADD lastpost INT NOT NULL, ADD lastvisit INT NOT NULL, ADD nbpmtot INT NOT NULL, ADD nbpmvu INT NOT NULL, ADD mailing ENUM( 'Y', 'N' ) DEFAULT 'Y' NOT NULL, DROP notifymsg";
		$update['ok']="Table ".$_PRE."user modifi�e";
		$update['nok']="Table ".$_PRE."user non modifi�e";		
		
		exec_request();		
		
		next_step();
		break;
	
	case 7:
	
		// **** Cr�ation de la nouvelle table de skins ****
		
		$update['sql']="DROP TABLE ".$_PRE."skins";
		$update['ok']="Suppression ".$_PRE."skins r�ussie";
		$update['nok']="Suppression ".$_PRE."skins �chou�e";		
		
		exec_request();		
		
		$update['sql']="CREATE TABLE ".$_PRE."skins (
				  id smallint(6) NOT NULL default '0',
				  propriete varchar(15) NOT NULL default '',
				  valeur varchar(15) NOT NULL default '',
				  KEY id (id)
				)";
		$update['ok']="Table ".$_PRE."skins recr��e";
		$update['nok']="Table ".$_PRE."skins non recr��e";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bg1', '#909090');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bg2', '#C1C1C1');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bordercolor', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bgtable1', '#537FAC');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bgtable2', '#A4B6C9');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bgtable3', '#265789');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'colsupadm', 'red');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'coladm', 'purple');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'colmodo', 'blue');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'colmb', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'colinvit', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'colban', 'yellow');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'lien1col', 'white');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'lien1visit', 'white');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'lien1hov', 'white');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'lien2col', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'lien2visit', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'lien2hov', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'textcol1', 'white');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'textcol2', 'black');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'smallfont', '1');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'middlefont', '2');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'bigfont', '3');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'repimg', 'defaut');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'reptpl', 'defaut');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'skinname', 'Defaut');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();
		
		$update['sql']="INSERT INTO ".$_PRE."skins VALUES (1, 'font', 'verdana');";
		$update['ok']="Donn�e skin int�gr�e";
		$update['nok']="Donn�e skin non int�gr�e";		
		
		exec_request();	

		$update['sql']="INSERT INTO ".$_PRE."skins (id,propriete,valeur) VALUES ('1', 'affdegrad', 'Y');";
		$update['ok']="Valeur <i>affdegrad</i> ins�r�e dans ".$_PRE."skins";
		$update['nok']="Valeur <i>affdegrad</i> non ins�r�e dans ".$_PRE."skins";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="UPDATE ".$_PRE."user SET skin='1'";
		$update['ok']="skin membres mis � jour";
		$update['nok']="skin membres non mis � jour";		
		
		exec_request();
		
		next_step();
		break;


	case 8:
	
		$update['sql']="ALTER TABLE ".$_PRE."categorie ADD catcoment VARCHAR(250) NOT NULL AFTER cattitle";
		$update['ok']="Table ".$_PRE."categorie mise � jour";
		$update['nok']="Table ".$_PRE."categorie non mise � jour";		
		
		exec_request();

		$update['sql']="CREATE TABLE ".$_PRE."topics (
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
		$update['ok']="Table ".$_PRE."topics cr��e";
		$update['nok']="Table ".$_PRE."topics non cr��e";		
		
		exec_request();

		$update['sql']="INSERT INTO ".$_PRE."topics (idtopic,idforum,sujet,date,nbrep,nbvues,datederrep,derposter,idderpost,icone,idmembre,pseudo,opentopic,poll) 
					SELECT 
					idpost,idforum,sujet,date,nbrep,nbvues,datederrep,derposter,idderpost,icone,idmembre,pseudo,opentopic,poll FROM ".$_PRE."posts WHERE parent=0";
		$update['ok']="Insertions dans ".$_PRE."topics effectu�es";
		$update['nok']="Insertions dans ".$_PRE."topics non effectu�es";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="UPDATE ".$_PRE."posts SET parent=idpost WHERE parent=0";
		$update['ok']="Mise � jour ".$_PRE."posts effectu�e";
		$update['nok']="Mise � jour ".$_PRE."posts non effectu�e";		

		exec_request();

		$update['sql']="ALTER TABLE ".$_PRE."posts DROP nbrep ,
					DROP nbvues ,
					DROP datederrep ,
					DROP derposter ,
					DROP idderpost ,
					DROP opentopic";
		$update['ok']="Suppression des informations obsol�tes ".$_PRE."posts effectu�e";
		$update['nok']="Suppression des informations obsol�tes ".$_PRE."posts non effectu�e";
		
		exec_request();
			
		next_step();
		break;	

	case 9:
	
		$update['sql']="UPDATE ".$_PRE."moderateur SET modorights=CONCAT(modorights,'1')";
		$update['ok']="Mise � jour ".$_PRE."moderateur effectu�e";
		$update['nok']="Mise � jour ".$_PRE."moderateur non effectu�e";		

		exec_request();
		
		affseparate();

		$update['sql']="ALTER TABLE ".$_PRE."annonces DROP nbrep";
		$update['ok']="Table ".$_PRE."annonces modifi�e";
		$update['nok']="Table ".$_PRE."annonces non modifi�e";		
		
		exec_request();

		$update['sql']="ALTER TABLE ".$_PRE."annonces DROP idderpost";
		$update['ok']="Table ".$_PRE."annonces modifi�e";
		$update['nok']="Table ".$_PRE."annonces non modifi�e";		
		
		exec_request();

		affseparate();

		$update['sql']="CREATE TABLE ".$_PRE."campagnes (
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
		$update['ok']="Table ".$_PRE."campagnes cr��e";
		$update['nok']="Table ".$_PRE."campagnes non cr��e";		
		
		exec_request();	

		$update['sql']="CREATE TABLE ".$_PRE."statcamp (
				  iddate varchar(13) NOT NULL default '',
				  vu int(11) NOT NULL default '0',
				  clicks int(11) NOT NULL default '0',
				  UNIQUE KEY iddate (iddate)
				)";
		$update['ok']="Table ".$_PRE."statcamp cr��e";
		$update['nok']="Table ".$_PRE."statcamp non cr��e";		
		
		exec_request();
		
		affseparate();
		
		$update['sql']="CREATE TABLE ".$_PRE."mailing (
				id INT NOT NULL AUTO_INCREMENT ,
				date BIGINT NOT NULL ,
				titre VARCHAR( 250 ) NOT NULL ,
				message TEXT NOT NULL ,
				type ENUM( 'text', 'html' ) NOT NULL ,
				PRIMARY KEY (id) 
				)";
		$update['ok']="Table ".$_PRE."mailing cr��e";
		$update['nok']="Table ".$_PRE."mailing non cr��e";		
		
		exec_request();
		
		affseparate();

		$update['sql']="UPDATE ".$_PRE."config SET valeur='0.7 beta' WHERE options='ForumDBVersion'";
		$update['ok']="Version de DB mise � jour";
		$update['nok']="Version de DB non mise � jour";		
		
		exec_request();

		next_step();
		break;	
		
	case 10:
		end_maj();
		break;							
}
