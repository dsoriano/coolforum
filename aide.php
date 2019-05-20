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

use Database\Database_MySQLi;

require("admin/functions.php");

// #### définition du lieu ###
$_SESSION['SessLieu']	=	_LOCATION_HELP_;
$_SESSION['SessForum']	=	0;
$_SESSION['SessTopic']	=	0;
//////////////////////////////

require("entete.php");

getlangage("aide");

$cache .= $tpl->gettemplate("aide","aidegenerale");

session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start,4);

$cache .= $tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
