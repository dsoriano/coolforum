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

$update = [];

switch($_GET['steps']) {
    case 1:
        $update['sql']="TRUNCATE " . _PRE_ . "session";
        $update['ok']="Table "._PRE_."session vidée";
        $update['nok']="Problème lors du vidage de la table "._PRE_."session";

        exec_request();
        affseparate();

        $update['sql']="ALTER TABLE `" . _PRE_ . "session` 
            CHANGE `sessionID` `id` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
            CHANGE `time` `access` DATETIME NOT NULL,
            ADD `bot` TINYINT NOT NULL DEFAULT '0' AFTER `id`,
            ADD `data` TEXT NOT NULL AFTER `topicid`,
            ADD INDEX (`access`),
            ADD INDEX (`bot`)";
        $update['ok']="Table "._PRE_."session modifiée";
        $update['nok']="Problème lors de la modification de la table "._PRE_."session";

        exec_request();
        next_step();
        break;

    case 2:
        $next_gc = date('Y-m-d H:i:s', strtotime( 'now + 1 hour'));

        $update['sql']="INSERT INTO "._PRE_."config VALUES ('next_session_gc', '$next_gc')";
        $update['ok']="Valeur <i>next_session_gc</i> insérée dans table configuration";
        $update['nok']="Valeur <i>next_session_gc</i> non insérée dans table configuration";

        exec_request();
        affseparate();

        // #####################################
        // #### MODIFICATIONS VERSION DE DB ####
        // #####################################

        $update['sql']="UPDATE "._PRE_."config SET valeur='0.9 beta' WHERE options='ForumDBVersion'";
        $update['ok']="Version de DB mise à jour";
        $update['nok']="Version de DB non mise à jour";

        exec_request();
        next_step();
        break;

    case 3:
        end_maj();
        break;
}
