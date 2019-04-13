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
	$query = $sql->query("DROP TABLE "._PRE_."annonces")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."avatars")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."banlist")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."campagnes")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."categorie")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."config")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."forums")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."groups")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."groups_perm")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."language")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."mailing")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."moderateur")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."poll")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."posts")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."privatemsg")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."search")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."session")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."skins")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."smileys")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."statcamp")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."topics")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."user")->execute();
	$query = $sql->query("DROP TABLE "._PRE_."userplus")->execute();

	echo("Les tables de votre forum sont maintenant supprimées...");		
}

if(!isset($_REQUEST['steps']))
{
	echo("Vous êtes sur le point de supprimer les tables MySQL de votre forum.<p>
	Etes-vous sûr de vouloir continuer?<p>");
	
	next_steps();	
}
