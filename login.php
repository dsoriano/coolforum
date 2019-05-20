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

require("admin/functions.php");

// #### définition du lieu ###
$_SESSION['SessLieu']				=	_LOCATION_HOME_;
$_SESSION['SessForum']				=	0;
$_SESSION['SessTopic']				=	0;
//////////////////////////////


$tablo = parse_url($_SERVER['HTTP_REFERER']);

$tablo['path'] = isset($tablo['path']) ? $tablo['path'] : NULLSTR;
$tablo['query'] = isset($tablo['query']) ? $tablo['query'] : NULLSTR;

$redirecturl = basename($tablo['path'])."?".$tablo['query'];


if (empty($_REQUEST['action'])) {
    $pseudo = getformatmsg($_POST['pseudo'], false);
    $query = $sql->query("SELECT 
            userid,
            login AS username,
            password,
            usermail,
            userstatus 
        FROM " . _PRE_ . "user WHERE login='%s'", $pseudo)->execute();
    $nb = $query->num_rows();

    if ($query->num_rows() === 0) {
        header("location: identify.php?error=1");
        die;
    }

    $j = $query->fetch_array(MYSQLI_ASSOC);
    if ($j['userstatus'] == 0) {
        require("entete.php");
        if ($_FORUMCFG['confirmparmail'] == 3)
            geterror("confirmregister");
        else
            geterror("waitforadmin");
    }
    $tmp = rawurldecode($j['password']);
    $passwd = getdecrypt($tmp, $_FORUMCFG['chainecodage']);

    if($passwd !== $_POST['password']) {
        header("location: identify.php?error=1");
        die;
    }

    // Set session
    Session::set('user', $j);

    // Set Cookie
    $send['userid']     = $j['userid'];
    $send['username']   = $j['login'];
    $send['userpass']   = $tmp;

    switch ($_POST['duree']) {
        case "0":
            sendcookie("CoolForumID", urlencode(serialize($send)), time() + 3600 * 24);
            break;
        case "1":
            sendcookie("CoolForumID", urlencode(serialize($send)), time() + 86400 * 30);
            break;
        case "2":
            sendcookie("CoolForumID", urlencode(serialize($send)), time() + 86400 * 365);
            break;
        case "3":
            sendcookie("CoolForumID", urlencode(serialize($send)), mktime(0, 0, 0, 1, 1, 2010));
            break;
    }

    if (!isset($_COOKIE['CF_LastINI'])) {
        sendcookie("CF_LastINI", time(), -1);
    }

    if (isset($_POST['backurl'])) {
        $tablo = parse_url($_POST['referrer']);
        $redirecturl = basename($tablo['path']) . "?" . $tablo['query'];
        header("location: $redirecturl");
        die;
    }

    header("location: index.php");
    die;
}

if($_REQUEST['action']=="savepoll")
{

	$_USER = array(); // renseignement du membre
	$_USER=getuserid();

	if(isset($_POST['choixpoll']))
	{
		$choixpoll 	= 	intval($_POST['choixpoll']);
		$idpoll		=	intval($_POST['idpoll']);
		$id		=	intval($_POST['id']);
		$forumid	=	intval($_POST['forumid']);

		$query	=	$sql->query("SELECT * FROM "._PRE_."poll WHERE id='%d'", $idpoll)->execute();
		$nb	=	$query->num_rows();

		if($nb==1)
		{
			$j	=	$query->fetch_array();

			$nbrep	=	explode(" >> ",$j['rep']);

			if($choixpoll >= 0 && $choixpoll < count($nbrep) && preg_match("|-".$_USER['userid']."-|",$j['votants']) == 0)
			{
				$nbrep[$choixpoll]++;

				$chainerep 	= 	implode(" >> ",$nbrep);
				$participant	=	$j['votants'].$_USER['userid']."-";
				$query 		= 	$sql->query("UPDATE "._PRE_."poll SET rep='%s', votants='%d' WHERE id=%d", array($chainerep, $participant, $j['id']))->execute();
			}
		}
	}

	header("location: $redirecturl");
}
