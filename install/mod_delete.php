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

////////////////////////////////////////////

function next_steps()
{
	global $_REQUEST;
	
	echo("<form action=\"install.php\" method=\"get\">
	<input type=\"hidden\" name=\"action\" value=\"delete\">
	<input type=\"hidden\" name=\"steps\" value=\"".($_REQUEST['steps']+1)."\">
	<input type=\"submit\" value=\"Continuer ->>\" class=\"form\">
	</form>");
}

////////////////////////////////////////////

if($_REQUEST['steps']==1)
{
	$query = $sql->query("DROP TABLE ".$_PRE."annonces");
	$query = $sql->query("DROP TABLE ".$_PRE."avatars");
	$query = $sql->query("DROP TABLE ".$_PRE."banlist");
	$query = $sql->query("DROP TABLE ".$_PRE."campagnes");
	$query = $sql->query("DROP TABLE ".$_PRE."categorie");
	$query = $sql->query("DROP TABLE ".$_PRE."config");
	$query = $sql->query("DROP TABLE ".$_PRE."forums");
	$query = $sql->query("DROP TABLE ".$_PRE."groups");
	$query = $sql->query("DROP TABLE ".$_PRE."groups_perm");
	$query = $sql->query("DROP TABLE ".$_PRE."language");
	$query = $sql->query("DROP TABLE ".$_PRE."mailing");
	$query = $sql->query("DROP TABLE ".$_PRE."moderateur");
	$query = $sql->query("DROP TABLE ".$_PRE."poll");
	$query = $sql->query("DROP TABLE ".$_PRE."posts");
	$query = $sql->query("DROP TABLE ".$_PRE."privatemsg");
	$query = $sql->query("DROP TABLE ".$_PRE."search");
	$query = $sql->query("DROP TABLE ".$_PRE."session");
	$query = $sql->query("DROP TABLE ".$_PRE."skins");
	$query = $sql->query("DROP TABLE ".$_PRE."smileys");
	$query = $sql->query("DROP TABLE ".$_PRE."statcamp");
	$query = $sql->query("DROP TABLE ".$_PRE."topics");
	$query = $sql->query("DROP TABLE ".$_PRE."user");
	$query = $sql->query("DROP TABLE ".$_PRE."userplus");

	echo("Les tables de votre forum sont maintenant supprimées...");		
}

if(!isset($_REQUEST['steps']))
{
	echo("Vous êtes sur le point de supprimer les tables MySQL de votre forum.<p>
	Etes-vous sûr de vouloir continuer?<p>");
	
	next_steps();	
}
