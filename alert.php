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

$idpost	= isset($_REQUEST['idpost']) ? (int)$_REQUEST['idpost'] : 0;

if ($idpost > 0) {
	$query = $sql->query("SELECT parent, idforum FROM "._PRE_."posts WHERE idpost = %d", $idpost)->execute();
	list($parent, $idforum) = $query->fetch_array();
} else {
	$parent = 0;
	$idforum = 0;
}


// #### définition du lieu ###
$_SESSION['SessLieu']				=	_LOCATION_TOPIC_;
$_SESSION['SessForum']				=	$idforum;
$_SESSION['SessTopic']				=	$parent;
//////////////////////////////

require("entete.php");

getlangage("alert");

if (isset($_REQUEST['url'])) {
	$tablurl	=	explode("&",$_REQUEST['url']);
	$tablurl	=	array_map("getformatmsg",$tablurl);
	$url		=	implode("&",$tablurl);
}

if ($_USER['userstatus'] > 1) {
	if ($_REQUEST['action'] == "sendmail") {
		$url2 =	$_FORUMCFG['urlforum']."gotopost.php?id=$idpost";
		$username = formatstrformail($_USER['username']);

		eval("\$subject = ".$tpl->attlang("mailsujet").";");
		eval("\$mesg = ".$tpl->attlang("mailmsg").";");

		if (sendmail(inversemail($_FORUMCFG['contactmail']),$subject,$mesg)) {
            $tpl->box['alertcontent'] = $tpl->attlang("emailok");
        } else {
            $tpl->box['alertcontent'] = $tpl->attlang("emailnotok");
        }

		$go = 1;
	}

	if (empty($_REQUEST['action'])) {
		$tpl->box['idpost']	=	intval($_GET['idpost']);
		$tpl->box['HTTP_REFERER']	=	$_SERVER['HTTP_REFERER'];
		$tpl->box['alertcontent']	=	$tpl->gettemplate("alert","msgalert");
	}

	$cache .= $tpl->gettemplate("alert","accueilalert");

} else {
	geterror("call_loginbox");
}

if (isset($go) && $go == 1) {
	$cache.=getjsredirect($url."#".$idpost,4000);
}

session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start,4);

$cache .= $tpl->gettemplate("baspage","endhtml");
$tpl->output($cache);
