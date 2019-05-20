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

require_once 'admin/functions.php';






// #### Extraction du cookie #### //////////////////////////////////////////////
if (isset($_COOKIE['listeforum_coolforum'])) {
    $zecook = cookdecode($_COOKIE['listeforum_coolforum']);
}
////////////////////////////////////////////////////////////////////////////////


// #### définition du lieu ###
$_SESSION['SessLieu'] = _LOCATION_HOME_;
$_SESSION['SessForum'] = 0;
$_SESSION['SessTopic'] = 0;
//////////////////////////////

require("entete.php");
getlangage("index");






// #### Boîte d'accueil #### ///////////////////////////////////////////////////
if ($_USER['userid'] > 0) {
    $tpl->box['pseudovisit'] = $tpl->gettemplate("index", "ifloggued");

    if ($_USER['lastpost'] > 0) {
        $_USER['lastpost'] = getlocaltime($_USER['lastpost']);
        $tpl->box['pseudovisit'] .= $tpl->gettemplate("index", "ifmsgposted");
    } else {
        $tpl->box['pseudovisit'] .= $tpl->gettemplate("index", "ifnomsgposted");
    }
} else {
    $tpl->box['pseudovisit'] = $tpl->gettemplate("index", "ifnotloggued");
}

if (!empty($_FORUMCFG['indexnews'])) {
    $_FORUMCFG['indexnews'] = getformatrecup($_FORUMCFG['indexnews']);
    $tpl->box['boxnews'] = $tpl->gettemplate("index", "newsbox");
} else {
    $tpl->box['boxnews'] = NULLSTR;
}
////////////////////////////////////////////////////////////////////////////////






// #### gestion des connectés #### /////////////////////////////////////////////

$InfoMember = get_connected();

if ($_FORUMCFG['conn_accueil'] === "Y") {
    $tpl->box['statsconnectes'] = NULLSTR;
    $tpl->box['nb_connected'] = $tpl->attlang("board_connected");
    if (!empty($InfoMember['listconnected']) && strlen($InfoMember['listconnected']) > 0) {
        if ($_GENERAL[0]) {
            $tpl->box['statsconnectes'] = $tpl->gettemplate("entete", "statsconnectes");
        }
        $tpl->box['listconnected'] = $tpl->gettemplate("entete", "listconnectes");
    } else {
        $tpl->box['listconnected'] = NULLSTR;
    }

    $tpl->box['boxconnected'] = $tpl->gettemplate("entete", "boxconnectes");
} else {
    $tpl->box['boxconnected'] = "";
}

$cache .= $tpl->gettemplate("index", "boxaccueil");
////////////////////////////////////////////////////////////////////////////////






// #### stats maximum connectés ////////////////////////////////////////////////
$_FORUMCFG['timetopmembers'] = getlocaltime($_FORUMCFG['timetopmembers']);

if ($InfoMember['nbmembres'] > $_FORUMCFG['topmembers']) {
    $updusers = $sql->query("UPDATE " . _PRE_ . "config SET valeur='%s' WHERE options='topmembers'", $InfoMember['nbmembres'])->execute();
    $updusers = $sql->query("UPDATE " . _PRE_ . "config SET valeur='%s' WHERE options='timetopmembers'", time())->execute();
}
/////////////////////////////////////////////////////////////////////////////////






// #### Affichage catégories + forums + modos #### //////////////////////////////

$query = $sql->query("SELECT * FROM " . _PRE_ . "categorie ORDER BY catorder")->execute();
$nb = $query->num_rows();

$tpl->box['affforumcontent'] = NULLSTR;
$tpl->box['linknewmsg'] = NULLSTR;

if ($nb === 0) {
    $tpl->box['affforumcontent'] .= $tpl->gettemplate("index", "nocat");
} else {
    $TabForum = array();
    $TabModos = array();

    $sqlforums = $sql->query("SELECT * FROM " . _PRE_ . "forums ORDER BY forumcat,forumorder")->execute();
    $nbforums = $sqlforums->num_rows();

    if ($nbforums > 0) {
        while ($TabForum[] = $sqlforums->fetch_array()) ;
    }

    $sqlmodo = $sql->query("SELECT * FROM " . _PRE_ . "moderateur ORDER BY forumident,modoorder")->execute();
    $nbmodos = $sqlmodo->num_rows();

    if ($nbmodos > 0) {
        while ($TabModos[] = $sqlmodo->fetch_array()) ;
    }

    while ($Cats = $query->fetch_array()) {
        $tpl->box['forumlist'] = affforumlist($Cats['catid']);

        if (strlen($tpl->box['forumlist']) > 0) {
            $Cats['cattitle'] = getformatrecup($Cats['cattitle']);

            if (strlen($Cats['catcoment']) > 0) {
                $Cats['catcoment'] = getformatrecup($Cats['catcoment']);
                $tpl->box['catcoment'] = $tpl->gettemplate("index", "catcoment");
            } else {
                $tpl->box['catcoment'] = "";
            }


            $tpl->box['affforumcontent'] .= $tpl->gettemplate("index", "affcategorie");
            $tpl->box['affforumcontent'] .= $tpl->box['forumlist'];
        } else {
            $tpl->box['affforumcontent'] .= "";
        }
    }

    if (strlen($tpl->box['affforumcontent']) > 0 && $_USER['userid'] > 0) {
        $tpl->box['linknewmsg'] = $tpl->gettemplate("index", "linknewmsg");
    } else {
        $tpl->box['linknewmsg'] = "&nbsp";
    }
}
/////////////////////////////////////////////////////////////////////////////////






// #### Recherche et affichage des messages privés #### /////////////////////////
if ($_USER['userstatus'] > 1) {
    if ($_USER['nbpmvu'] == 0) {
        $PrivateMsg['imgnewpm'] = "off";
        $tpl->box['totalmsg'] = $tpl->attlang("nonewpm");
    } else {
        $PrivateMsg['imgnewpm'] = "on";

        if ($_USER['nbpmvu'] == 1) {
            $tpl->box['totalmsg'] = $tpl->attlang("onepm");
        } else {
            eval("\$tpl->box['totalmsg']=\"" . $tpl->attlang("multipm") . "\";");
        }
    }
    $tpl->box['affforumcontent'] .= $tpl->gettemplate("index", "boxpm");
}
/////////////////////////////////////////////////////////////////////////////////






// #### Gestion anniversaire #### ///////////////////////////////////////////////
$now = time();

if ($_FORUMCFG['nextdailyupdate'] < $now) {
    updatebirth();
    $sql->query("UPDATE " . _PRE_ . "config SET valeur='%s' WHERE options='nextdailyupdate'", mktime(0, 0, 0, strftime("%m", $now), strftime("%d", $now), strftime("%Y", $now)) + 86400)->execute();
}

if (strlen($_FORUMCFG['birth']) > 0) {
    $tpl->box['birth'] = $tpl->gettemplate("index", "ifbirth");
} else {
    $tpl->box['birth'] = $tpl->attlang("ifnobirth");
}
/////////////////////////////////////////////////////////////////////////////////






// #### Affichage de la page #### ///////////////////////////////////////////////
$tpl->box['fuseaux'] = getfuseauhoraire();
$_FORUMCFG['statlastmember'] = getformatrecup($_FORUMCFG['statlastmember']);

$cache .= $tpl->gettemplate("index", "accueilgeneral");

session_write_close();
$NBRequest = Database_MySQLi::getNbRequests();
$tps = number_format(get_microtime() - $tps_start, 4);

$cache .= $tpl->gettemplate("baspage", "endhtml");
$tpl->output($cache);
/////////////////////////////////////////////////////////////////////////////////
