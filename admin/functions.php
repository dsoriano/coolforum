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
use Session\Session;

require_once __DIR__ . '/../include/autoload.php';
require_once __DIR__ . '/../secret/config.inc.php';

define('_LOCATION_FORUM_', 'FOR');
define('_LOCATION_HOME_', 'ACC');
define('_LOCATION_SEARCH_', 'SEA');
define('_LOCATION_ADMIN_', 'ADM');
define('_LOCATION_STATS_', 'STA');
define('_LOCATION_HELP_', 'HLP');
define('_LOCATION_PROFILE_', 'PRO');
define('_LOCATION_TOPIC_', 'TOP');



// ********************************************************
// *                 CLASSE DE TEMPLATES                  *
// ********************************************************

class Template
{
	var $isloaded	=	array();
	var $tplcode	=	array();
	var $temp	=	array();
	var $tmp	=	array();
	var $box	=	array();
	var $infomember	=	array();
	var $globales 	= 	array(); // chaines des globables pour eval()
	var $LNG 	= 	array(); //tableau langages

	function evalue($name)
	{
		$glob = "";

		if(!empty($this->globales[$name]))
			$glob 	= 	"global \$".$this->globales[$name].";";

		$templ		=	$this->tplcode[$name];
		eval("$glob \$templ=\"$templ\";");
		return($templ);
	}

	function forme($templ)
	{
		$templ		=	preg_replace("'\/\*(.*?)\*\/'si","",$templ); // On supprimer les commentaires
		$templ		=	addslashes(addslashes($templ)); // double backslash


		// **** evaluation des variables de langages ****

		if(get_magic_quotes_runtime()==0)
			$templ		=	addslashes($templ); // 1 backslash
		$templ		=	str_replace("\$","\\\\\\\\\\\\\\$",$templ);//\\\$
		$templ		=	preg_replace("/{%LNG\[([^\[]+)\]%}/","\".\$this->LNG['\\1'].\"",$templ); // On transforme les variables de langage

		eval("\$templ=\"$templ\";");

		// **** éclatement du fichier ****

		$tpleclate	=	explode("<!--********** TPL NAME = ",$templ); // Eclatement général des templates

		// **** traitement bloc par bloc ****

		for($i=1;$i<count($tpleclate);$i++)
		{
			$temp		=	explode(" **********-->",$tpleclate[$i]);
			$tplname	=	$temp[0];

			$this->tplcode[$tplname]=$temp[1];
			$this->tplcode[$tplname]=preg_replace("/^(\r\n)+/","",$this->tplcode[$tplname]);

			preg_match_all("/{%::(.*?)%}/",$this->tplcode[$tplname],$out,PREG_PATTERN_ORDER);
			if(count($out[1])>0)
			{
				for($j=0;$j<(count($out[1]));$j++)
				{
					$out[1][$j]		=	preg_replace("'\[(.*?)\]'","",$out[1][$j]);
					$tabl[$out[1][$j]]	=	$out[1][$j];
				}

				$this->globales[$tplname]	=	implode(",\$",$tabl);

			}
			$this->tplcode[$tplname]	=	preg_replace("/{%([^\[]+)\[([^\]]+)\]%}/","{%\\1['\\2']%}", $this->tplcode[$tplname]);
			$this->tplcode[$tplname]	=	preg_replace("/{%::(.*?)%}/","\".\$\\1.\"",$this->tplcode[$tplname]); // On transforme les variables globales

			$this->tplcode[$tplname]	=	preg_replace("/{%(.*?)%}/","\".\$this->\\1.\"",$this->tplcode[$tplname]); // On transforme les variables de classe
			unset($out);
		}

	}

	function gettemplate($filename,$tpl)
	{
		if(empty($this->isloaded[$filename]))
			$this->loadfile($filename);

		$chaine		=	$this->evalue($tpl);
		return($chaine);
	}

	function loadfile($file)
	{
		global $_SKIN,$_SERVER;

		if(preg_match("|admin/|",$_SERVER['REQUEST_URI']) > 0)	$prefix="../";
		else						$prefix="";

		$temp			=	implode("",file($prefix."templates/".$_SKIN['reptpl']."/tpl_".$file.".html"));
		$this->forme($temp);
		$this->isloaded[$file]	=	true;
	}

	function attlang($param)
	{
		return($this->LNG[$param]);
	}

	function output($tmpl)
	{
		echo(stripslashes(stripslashes($tmpl)));
	}
}

// ********************************************************
// *                 FONCTIONS SYSTEMES                   *
// ********************************************************
function get_microtime()
{
	list($tps_usec, $tps_sec) = explode(" ",microtime());
	return ((float)$tps_usec + (float)$tps_sec);
}

function get_rightfromint($tableau,$int)
{
	$i 	= 	20;
	$Mask 	= 	1 << $i;

	while($Mask>0)
	{
		if($int >= $Mask)
		{
			$tableau[$i] 	= 	true;
			$int 	       -= 	$Mask;
		} else {
            $tableau[$i] = false;
        }
		$Mask 	= $Mask >> 1;
		$i--;
	}
	return($tableau);
}

function get_intfromright($tableau)
{
	$i 	= 	1;
	$int 	= 	0;

	foreach($tableau as $key => $value)
	{
		if($value == 'true')
			$int += $i << $key;
	}
	return($int);
}

function addToArray(&$array, $key, $val)
{
   $tempArray = array($key => $val);
   $array = array_merge ($array, $tempArray);
}


function array_rempl(&$array, $start, $end, $value)
{
	for($i = $start; $i < $end+1; $i++)
		$array[$i] = $value;
}

function stopaction($msg='')
{
	if(strlen($msg) > 0)
		echo("&gt; $msg");
	else
		echo("script stoppé");
	die;
}
//********** FONCTION GESTION MESSAGES D'ERREUR **********
function geterror($error)
{
	global $tpl,$cache,$_FORUMCFG,$tps,$tps_start, $sql,$NBRequest;

	getlangage("error");

	if ($error == "closedforum") {
        $tpl->box['errorcontent'] = $_FORUMCFG['closeforummsg'];
    } elseif($error == "initcookie") {
		sendcookie("CoolForumID","",0);
		sendcookie("listeforum_coolforum","",0);
		sendcookie("nbpmvu","0",-1);

		$forums_id	=	$sql->query("SELECT forumid FROM "._PRE_."forums")->execute();
		$nb	=	$forums_id->num_rows();

		if ($nb>0) {
			while ($j = $forums_id->fetch_array()) {
				$name	=	"CoolForumDetails".$j['forumid'];
				sendcookie($name,"",0);
			}
		}

		sendcookie("CF_LastINI",time(),-1);
		$tpl->box['errorcontent']	=	$tpl->gettemplate("error","initcookie");
	} else {
        $tpl->box['errorcontent']	=	$tpl->gettemplate("error",$error);
    }

	$cache .=	$tpl->gettemplate("error","errorbox");

    session_write_close();
    $NBRequest = Database_MySQLi::getNbRequests();
	$tps 	= 	number_format(get_microtime() - $tps_start,4);

	$cache .=	$tpl->gettemplate("baspage","endhtml");
	$tpl->output($cache);
	exit;

}

//********** FONCTION D'ENVOI DE COOKIES **********
function sendcookie($name,$value,$expire)
{
	global $_FORUMCFG;

	if($expire==-1)
		$expire=mktime(0,0,0,1,1,2010);

	if(empty($_FORUMCFG['cookiedomain']))	setcookie($name,$value,$expire,$_FORUMCFG['cookierep']);
	else					setcookie($name,$value,$expire,$_FORUMCFG['cookierep'],".".$_FORUMCFG['cookiedomain']);
}

//********** FONCTION D'ENVOI DE MAILS **********
function sendmail($To,$Subject,$Msg)
{
	global $_FORUMCFG;

	switch ($_FORUMCFG['mailfunction'])
	{
		case "normal":
			if(!mail($To,$Subject,$Msg,"From: ".inversemail($_FORUMCFG['contactmail'])."\nReply-To: ".inversemail($_FORUMCFG['contactmail'])."\nX-Priority: 1"))
				return false;
			break;

		case "online":
			if(!email("webmaster",$To,$Subject,$Msg))
				return false;
			break;

		case "nexen":
			if(!email($To,$Subject,$Msg,"From: ".inversemail($_FORUMCFG['contactmail'])))
				return false;
			break;
	}

	return true;
}

//********** FONCTIONS ENCODAGE/DECODAGE **********
function getencrypt($txt,$cle)
{
	srand((double)microtime()*1000000);
	$getencrypt_key = md5(rand(0,32000));

	$ctr=0;
	$tmp = "";
	for ($i=0;$i<strlen($txt);$i++)
	{
		if ($ctr==strlen($getencrypt_key))
			$ctr=0;
		$aff1 = substr($getencrypt_key,$ctr,1);

		$tmp.= $aff1.(substr($txt,$i,1) ^ $aff1);
		$ctr++;
	}

	$ctr=0;
	$code="";
	for ($i=0;$i<strlen($tmp);$i++)
	{
		if ($ctr==strlen($cle))
			$ctr=0;
		$code.=(substr($tmp,$i,1)) ^ (substr($cle,$ctr,1));
		$ctr++;
	}
return($code);
}

function getdecrypt($tplxt,$cle)
{
	$ctr=0;
	$decode="";
	for ($i=0;$i<strlen($tplxt);$i++)
	{
		if ($ctr==strlen($cle))
			$ctr=0;
		$decode.=(substr($tplxt,$i,1)) ^ (substr($cle,$ctr,1));
		$ctr++;
	}
	$tmp = "";
	for ($i=0;$i<strlen($decode);$i+=2)
	{
		$tmp.= (substr($decode,$i,1))^(substr($decode,$i+1,1));
	}
	return($tmp);
}

function getconfig()
{
	global $sql;

	$result=$sql->query("SELECT * FROM "._PRE_."config")->execute();
	while($j=$result->fetch_array())
	{
		$tableau[$j['options']]=$j['valeur'];
	}

	$tableau['catseparate']=htmlentities($tableau['catseparate'], ENT_COMPAT,'ISO-8859-1', true);
	return($tableau);
}

function getlangage($file)
{
	global $_USER,$_FORUMCFG,$tpl,$_SERVER;

	if(preg_match("|admin/|",$_SERVER['REQUEST_URI']) > 0)
		$prefix="../";
	else
		$prefix="";

	if(!isset($_USER['lng']))
		$lng=$_FORUMCFG['defaultlangage'];
	else
		$lng=$_USER['lng'];

	include($prefix."lng/" . $lng . "/lng_" . $file . ".php");
}

function getforumname($id)
{
	global $_USER, $sql, $_GENERAL;

	$id = intval($id);

	$query 	= 	$sql->query("SELECT "._PRE_."forums.forumid,"._PRE_."forums.forumtitle,"._PRE_."forums.forumcat,"._PRE_."forums.openforum,"._PRE_."forums.forumtopic,"._PRE_."forums.forumposts,"._PRE_."categorie.cattitle,"._PRE_."categorie.catid FROM "._PRE_."forums,"._PRE_."categorie WHERE "._PRE_."forums.forumid='$id' AND "._PRE_."categorie.catid="._PRE_."forums.forumcat")->execute();
	$nb 	= 	$query->num_rows();

	if ($nb==0) {
        geterror("novalidlink");
    } else {
		$j = $query->fetch_array();
		if ($j['openforum']=="N" && !$_GENERAL[20]) {
            geterror("forumclosed");
        } else {
            return($j);
        }
	}
}

function getnumberpages($nb,$template,$nbmax,&$page)
{
	global $_GET,$nbpages,$_FORUMCFG,$tpl;

	$chaine="";
	/*if (empty($_GET['page']))
		$page=1;
	else
		$page=$_GET['page'];*/

	$nbpages=Ceil($nb/$nbmax);
	if($nbpages>1)
	{
		if($page>1)
		{
			$tpl->box['pagebefore']=$page-1;
			$chaine.=$tpl->gettemplate($template,"leftpagearrowlink");
		}
		else	$chaine.=$tpl->gettemplate($template,"leftpagearrow");

		if($page<6)	$debut=1;
		else	$debut=$page-4;

		if($nbpages-$page<5)	$fin=$nbpages;
		else	$fin=$page+5;

		for($i=$debut; $i<($fin+1); $i++)
		{
			$tpl->box['pagenumber']=$i;
			if ($i==$page)
				$chaine.=$tpl->gettemplate($template,"ourpage");
			else
				$chaine.=$tpl->gettemplate($template,"otherpage");
			if ($i<$fin)
				$chaine.=" | ";
		}
		if($page<$nbpages)
		{
			$tpl->box['pageafter']=$page+1;
			$chaine.=$tpl->gettemplate($template,"rightpagearrowlink");
		}
		else
			$chaine.=$tpl->gettemplate($template,"rightpagearrow");
	}
	return($chaine);
}

function getpagestopic($nb,$id,$page)
{
	global $forumid,$nbpagepost,$_FORUMCFG,$tpl;
	$chaine="";
	$nb++;

	if(empty($forumid))
		$forumid=$tpl->tmp['forumid'];

	$nbpagepost=Ceil($nb/$_FORUMCFG['msgparpage']);

	if($nbpagepost>1)
	{
		$flagpoint=0;
		//$chaine.=" (aller page: ";
		$tpl->box['cachepages'] = "";

		for($i=1; $i<($nbpagepost+1); $i++)
		{
			if($i>3 && $i<$nbpagepost && $nbpagepost>4)
			{
				if($flagpoint==0)
				{
					$tpl->box['cachepages'] .=" ...";
					$flagpoint=1;
				}
			}
			else
			{
				$tpl->tmp['i'] = $i;
				$tpl->tmp['id'] = $id;
				$tpl->tmp['page'] = $page;
				//$chaine.=" <a href=\"detail.php?forumid=$forumid&id=$id&page=$i&p=$page\" class=men>$i</A>";
				$tpl->box['cachepages'] .= $tpl->gettemplate("entete","topicgotopagelink");
			}
		}
		//$chaine.=")";
		$chaine = $tpl->gettemplate("entete","topicgotopage");
	}
	return($chaine);
}

// #### CHARGEMENT IDENTIFICATION MEMBRE ####
function getuserid()
{
    global $_COOKIE, $sql, $_FORUMCFG, $_PERMFORUM, $_FORUMINTER, $_GENERAL;

    try {
        $user = Session::get('user', []);


        if (isset($user['userid'])) { // If user exists in session
            $query = $sql->query("SELECT 
                    " . _PRE_ . "user.userstatus,
                    " . _PRE_ . "user.skin AS userskin,
                    " . _PRE_ . "user.timezone,
                    " . _PRE_ . "user.lng,
                    " . _PRE_ . "user.popuppm,
                    " . _PRE_ . "user.lastpost,
                    " . _PRE_ . "user.nbpmtot,
                    " . _PRE_ . "user.nbpmvu,
                    " . _PRE_ . "groups.*
                FROM " . _PRE_ . "user
                LEFT JOIN " . _PRE_ . "groups ON " . _PRE_ . "groups.id_group = " . _PRE_ . "user.userstatus 
                WHERE " . _PRE_ . "user.userid=%d", $user['userid'])->execute();

            if ($query->num_rows() === 0) {
                throw new Exception();
            }

            $user = array_merge($user, $query->fetch_array(MYSQLI_ASSOC));
        } elseif (isset($_COOKIE['CoolForumID'])) { // If user cookie exists
            $cookie = unserialize(urldecode($_COOKIE['CoolForumID']));
            $cookie['userpass'] = getdecrypt(rawurldecode($cookie['userpass']), $_FORUMCFG['chainecodage']);
            $cookie['userid'] = (int)$cookie['userid'];

            $query = $sql->query("SELECT 
                    " . _PRE_ . "user.userid, 
                    " . _PRE_ . "user.login AS username,
                    " . _PRE_ . "user.password,
                    " . _PRE_ . "user.usermail,
                    " . _PRE_ . "user.userstatus,
                    " . _PRE_ . "user.skin AS userskin,
                    " . _PRE_ . "user.timezone,
                    " . _PRE_ . "user.lng,
                    " . _PRE_ . "user.popuppm,
                    " . _PRE_ . "user.lastpost,
                    " . _PRE_ . "user.lastvisit,
                    " . _PRE_ . "user.nbpmtot,
                    " . _PRE_ . "user.nbpmvu,
                    " . _PRE_ . "user.wysiwyg,
                    " . _PRE_ . "groups.*
                FROM " . _PRE_ . "user
                LEFT JOIN " . _PRE_ . "groups ON " . _PRE_ . "groups.id_group = " . _PRE_ . "user.userstatus 
                WHERE " . _PRE_ . "user.userid=%d", $cookie['userid'])->execute();

            if ($query->num_rows() === 0) {
                throw new Exception();
            }

            $tempuser = $query->fetch_array(MYSQLI_ASSOC);
            $temppass = getdecrypt(rawurldecode($tempuser['password']), $_FORUMCFG['chainecodage']);

            if ($cookie['userpass'] !== $temppass) {
                throw new Exception();
            }

            unset ($tempuser['password']);
            $user = array_merge($user, $query->fetch_array(MYSQLI_ASSOC));
        } else {
            throw new Exception();
        }
    } catch (Exception $e) {
        $query = $sql->query("SELECT * FROM " . _PRE_ . "groups WHERE id_group = 1")->execute();
        $user = $query->fetch_array(MYSQLI_ASSOC);

        $user['username']       = NULLSTR;
        $user['userstatus']     = 1;
        $user['userid']         = 0;
        $user['lastvisit']      = 0;
        $user['userskin']       = $_FORUMCFG['defaultskin'];
        $user['timezone']       = $_FORUMCFG['defaulttimezone'];
        $user['popuppm']        = "N";
        $user['lastpost']       = 0;
        $user['wysiwyg']        = "N";
    }

    // Chargement droits généraux
    $_GENERAL = get_rightfromint($_GENERAL, $user['Droits_generaux']);

    // Chargement droits des forums
    $request = $sql->query("SELECT * FROM " . _PRE_ . "groups_perm WHERE id_group = %d", $user['userstatus'])->execute();

    if ($request->num_rows() > 0) {
        while ($i = $request->fetch_array()) {
            $temp_array = array();
            $temp_array = get_rightfromint($temp_array, $i['droits']);

            $_PERMFORUM[$i['id_forum']] = $temp_array;
            $_PERMFORUM[$i['id_forum']]['MaxChar'] = $i['MaxChar'];
        }
    }

    Session::set('user', $user);
    return ($user);
}


// #### LE MEMBRE EST-IL MODERATEUR POUR CE FORUM? ####
function getismodo($forum)
{
	global $_MODORIGHTS, $_USER, $sql, $_GENERAL;

	array_rempl($_MODORIGHTS,0,8,false);

	if($_GENERAL[19])
	{
		if($_GENERAL[20])
		{
			$_MODORIGHTS 		=		get_rightfromint($_MODORIGHTS,511);
			return true;
		}
		else
		{
			$query				=		$sql->query("SELECT idusermodo, modorights FROM "._PRE_."moderateur WHERE idusermodo=%d AND forumident='%s'", array($_USER['userid'],$forum))->execute();
			$nb					=		$query->num_rows();

			if($nb>0)
			{
				$j				=		$query->fetch_array();
				$_MODORIGHTS	=		get_rightfromint($_MODORIGHTS,$j['modorights']);
				return true;
			}
			else	return false;
		}
	}
	else			return false;
}

// #### CHARGEMENT DES DROITS POUR L'EDITION D'UN MESSAGE ####
function getrightedit($idpost,$forumid)
{
	global $_MODORIGHTS, $sql, $_USER, $_FORUMCFG, $_GENERAL, $_PERMFORUM;

	$query					=	$sql->query("SELECT idforum,idmembre,parent FROM "._PRE_."posts WHERE idpost=%d", $idpost)->execute();
	$j						=	$query->fetch_array();

	$ismodo					=	false;
	$ismodo					=	getismodo($forumid);

	if ($_GENERAL[20]) {
        return true;
    } elseif ($ismodo && $forumid==$j['idforum']) {
        return true;
    } elseif($_USER['userstatus']>1) {
		$parent				=	$j['parent'];

		if($j['idmembre']==$_USER['userid'])
		{
			if(!isset($_PERMFORUM[$forumid][6]) || !$_PERMFORUM[$forumid][6])
			{
				$query		=	$sql->query("SELECT idpost FROM "._PRE_."posts WHERE parent=%d ORDER BY date DESC", $parent)->execute();
				$i			=	$query->fetch_array();

				if($i['idpost']==$idpost)		return true;
				else						return false;
			}
			else							return true;
		}
		else								return false;
	}
	else									return false;
}

function init_session()
{
	srand((double)microtime()*1000000);
	$session = md5(rand(0,32000));
	return($session);
}

// #### ENREGISTRMEMENT DE LA SESSION POUR CHAQUE MEMBRE / INVITE ####
//function getsession()
//{
//	global $_COOKIE, $_USER, $sql, $SessLieu, $SessForum, $SessTopic;
//	$tablename								=		array();
//
//	if(!empty($SessLieu))
//	{
//		$pseudo 								= 		getformatdbtodb($_USER['username']);
//
//		if(!isset($_COOKIE['CF_sessionID']))
//	   		$_COOKIE['CF_sessionID'] 			= 		init_session();
//
//		$now									=		time();
//		$perim									=		$now - 300;
//
//		$delsql									=		$sql->query("DELETE FROM "._PRE_."session WHERE time<%d", $perim)->execute();
//
//		$query									=		$sql->query("SELECT sessionID, username, userid, userstatus, typelieu, forumid, topicid FROM "._PRE_."session")->execute();
//		$nb										=		$query->num_rows();
//
//		$found 									= 		false;
//		$i										=		0;
//
//		if($nb>0)
//		{
//			while($j=$query->fetch_array())
//			{
//				if($_USER['userid']>0 && $_USER['username']==$j['username']) // si trouvé membre
//				{
//					$updsess					=	$sql->query("UPDATE "._PRE_."session SET sessionID='%s', time=%d, typelieu='%s', forumid=%d, topicid=%d WHERE username='%s'", array($_COOKIE['CF_sessionID'], $now, $SessLieu, $SessForum, $SessTopic, $pseudo))->execute();
//					$tablename[$i]['name']		=	$j['username'];
//					$tablename[$i]['status']	=	$j['userstatus'];
//					$tablename[$i]['userid']	=	$j['userid'];
//					$found 						= 	true;
//					$tablename[$i]['typelieu']	=	$SessLieu;
//					$tablename[$i]['forumid']	=	$SessForum;
//					$tablename[$i]['topicid']	=	$SessTopic;
//				}
//				elseif($j['sessionID']==$_COOKIE['CF_sessionID']) // sinon si les sessions concordent => soit invité soit vient de se logguer
//				{
//					$updsess					=	$sql->query("UPDATE "._PRE_."session SET username='%s', userid=%d, userstatus=%d, time=%d, typelieu='%s', forumid=%d, topicid=%d  WHERE sessionID='%s'", array($pseudo, $_USER['userid'], $_USER['userstatus'], $now, $SessLieu, $SessForum, $SessTopic, $_COOKIE['CF_sessionID']))->execute();
//					$tablename[$i]['name']		=	$_USER['username'];
//					$tablename[$i]['status']	=	$_USER['userstatus'];
//					$tablename[$i]['userid']	=	$_USER['userid'];
//					$found 						= 	true;
//					$tablename[$i]['typelieu']	=	$SessLieu;
//					$tablename[$i]['forumid']	=	$SessForum;
//					$tablename[$i]['topicid']	=	$SessTopic;
//				}
//				else //sinon ce n'est pas moi
//				{
//					$tablename[$i]['name']		=	$j['username'];
//					$tablename[$i]['status']	=	$j['userstatus'];
//					$tablename[$i]['userid']	=	$j['userid'];
//					$tablename[$i]['typelieu']	=	$j['typelieu'];
//					$tablename[$i]['forumid']	=	$j['forumid'];
//					$tablename[$i]['topicid']	=	$j['topicid'];
//				}
//				$i++;
//			}
//
//		}
//
//		if(!$found)
//		{
//			$query								=	$sql->query("INSERT into "._PRE_."session (sessionID, username, userid, userstatus, time, typelieu, forumid, topicid) VALUES ('%s','%s', %d,'%s','%s','%s','%s','%s')", array($_COOKIE['CF_sessionID'], $pseudo, $_USER['userid'], $_USER['userstatus'], $now, $SessLieu, $SessForum, $SessTopic))->execute();
//			$tablename[$i]['name']				=	$_USER['username'];
//			$tablename[$i]['status']			=	$_USER['userstatus'];
//			$tablename[$i]['userid']			=	$_USER['userid'];
//			$tablename[$i]['typelieu']			=	$SessLieu;
//			$tablename[$i]['forumid']			=	$SessForum;
//			$tablename[$i]['topicid']			=	$SessTopic;
//
//			$LastINI							=	isset($_COOKIE['CF_LastINI']) ? (int)$_COOKIE['CF_LastINI'] : 0;
//			if($_USER['userid'] > 0)
//				$query							=	$sql->query("UPDATE "._PRE_."user SET lastvisit = %d WHERE userid = %d", array($LastINI, $_USER['userid']))->execute();
//		}
//
//		sendcookie("CF_sessionID",$_COOKIE['CF_sessionID'],-1);
//		sendcookie("CF_LastINI",$now,-1);
//	}
//	return($tablename);
//
//}

function get_connected()
{
    global $NombreConnectes;

    $InfoMember = [
        'nbmembres' => 0,
        'nbguests' => 0,
        'nbtotalvisit' => 0,
        'listconnected' => '',
    ];


    $ListMembres = array_filter($NombreConnectes, function ($membre) use (&$InfoMember) {
        if ($membre['typelieu'] === $_SESSION['SessLieu'] && $membre['forumid'] === $_SESSION['SessForum'] && $membre['topicid'] === $_SESSION['SessTopic']) {
            if ($membre['userid'] > 0) {
                $InfoMember['nbmembres']++;
                return true;
            } else {
                $InfoMember['nbguests']++;
            }
        }

        return false;
    });

    $InfoMember['nbtotalvisit'] = $InfoMember['nbmembres'] + $InfoMember['nbguests'];

    // Formattage des membres pour affichage
    if ($InfoMember['nbmembres'] > 0) {
        $nameconnect = [];
        foreach ($ListMembres as $membre) {
            $nameconnect[] = "<b>" . getformatpseudo($membre['name'], $membre['status'], $membre['userid']) . "</b>";
        }

        $InfoMember['listconnected'] = implode(", ", $nameconnect);
    }

    return ($InfoMember);
}

function getjumpforum($template="entete")
{
	global $_USER, $tpl, $sql, $_PERMFORUM;

	$query					=		$sql->query("SELECT * FROM "._PRE_."categorie ORDER BY catorder")->execute();
	$nb						=		$query->num_rows();

	$tpl->box['forumlist']	=		"";

	if($nb>0)
	{
		$sqlforums			=		$sql->query("SELECT forumid,forumcat,forumtitle FROM "._PRE_."forums ORDER BY forumcat,forumorder")->execute();
		$nbforums			=		$sqlforums->num_rows();

		if ($nbforums>0) {
            while($TabForum[] = 	$sqlforums->fetch_array());
        }

		$i = 0;
		while($Cats = $query->fetch_array())
		{
			$chaine			=		"";
			for($i = 0; $i < count($TabForum); $i++)
			{
				if($Cats['catid'] == $TabForum[$i]['forumcat'] && !empty($_PERMFORUM[$TabForum[$i]['forumid']][0]) && $_PERMFORUM[$TabForum[$i]['forumid']][0])
				{

					$tpl->box['FJforumname']=getformatrecup($TabForum[$i]['forumtitle']);
					$tpl->box['FJforumid']=$TabForum[$i]['forumid'];
					$chaine.=$tpl->gettemplate($template,"forumjumpfor");
				}
			}

			if(strlen($chaine)>0)
			{
				$tpl->box['FJcatname']=getformatrecup($Cats['cattitle']);
				$tpl->box['forumlist'].=$tpl->gettemplate($template,"forumjumpcat").$chaine;
			}
		}
		$tpl->box['forumjump']=$tpl->gettemplate($template,"structforumjump");
	}
}

function getskin()
{
	global $_SKIN, $_USER, $sql, $_POST, $_FORUMCFG, $ListColorGroup;

	if($_FORUMCFG['defaultskin']<$_USER['userskin'])
		$order="DESC";
	else
		$order="ASC";

	$query=$sql->query("SELECT propriete,valeur FROM "._PRE_."skins WHERE id=%d OR id=%d ORDER BY id %s", array($_USER['userskin'],$_FORUMCFG['defaultskin'],$order))->execute();

	while (list($skcle,$skvalue) = $query->fetch_array()) {
		if (empty($_SKIN[$skcle])) {
            addToArray($_SKIN,$skcle,$skvalue);
        }
			//$_SKIN[$skcle]=$skvalue;

		if (preg_match("|^grp|",$skcle) > 0) {
            $ListColorGroup[] = intval(str_replace("grp","",$skcle));
        }
	}

	if (!empty($_POST['skins']) && count($_POST['skins'])>0 && isset($_POST['act']) && $_POST['act']=="preview") {
		foreach ($_POST['slins'] as $key => $value) {
			if (strlen($value)>0) {
				if (empty($_SKIN[$skcle])) {
                    addToArray($_SKIN,$key,$value);
                } else {
                    $_SKIN[$key]=$value;
                }
            }
		}
	}
}

function getloadsmileys()
{
	global $sql;

	$query = $sql->query("SELECT imgsmile,codesmile FROM "._PRE_."smileys ORDER BY ordersmile ASC")->execute();

	$i=0;
	while ($j = $query->fetch_array()) {
		$tplable_smileys[$i]['code']=getformatrecup($j['codesmile']);
		$tplable_smileys[$i]['img']=$j['imgsmile'];
		$i++;
	}

	return($tplable_smileys);
}

function getreturnsmilies($msg)
{

	global $table_smileys,$_SERVER;

	if (preg_match("|admin/|",$_SERVER['REQUEST_URI']) > 0) {
        $prefix="../";
    } else {
        $prefix="";
    }

	for ($i=0;$i<count($table_smileys);$i++) {
		$msg = str_replace($table_smileys[$i]['code'],"<img src=\"".$prefix."smileys/".$table_smileys[$i]['img']."\" align=absmiddle>", $msg);
	}

	return($msg);
}

function InitBBcode()
{
	global $BBcodeHTML,$tpl, $_FORUMCFG, $ListWords, $ListWordsRpl;

	$BBcodeHTML['codeopen']		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbcodeopen"))));
	$BBcodeHTML['codeclose']	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbcodeclose"))));
	$BBcodeHTML['urlauto1'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bburlauto1"))));
	$BBcodeHTML['urlauto2'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bburlauto2"))));
	$BBcodeHTML['swf'] 			= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbswf"))));
	$BBcodeHTML['mail'] 		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbmail"))));
	$BBcodeHTML['img'] 			= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbimg"))));
	$BBcodeHTML['bold'] 		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbbold"))));
	$BBcodeHTML['ita'] 			= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbita"))));
	$BBcodeHTML['under'] 		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbunder"))));
	$BBcodeHTML['center'] 		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbcenter"))));
	$BBcodeHTML['left'] 		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbleft"))));
	$BBcodeHTML['right'] 		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbright"))));
	$BBcodeHTML['fontopen']		= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbfontopen"))));
	$BBcodeHTML['fontclose']	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbfontclose"))));
	$BBcodeHTML['sizeopen'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbsizeopen"))));
	$BBcodeHTML['sizeclose'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbsizeclose"))));
	$BBcodeHTML['coloropen'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbcoloropen"))));
	$BBcodeHTML['colorclose'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbcolorclose"))));
	$BBcodeHTML['url'] 			= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bburl"))));
	$BBcodeHTML['quoteopen']	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbquoteopen"))));
	$BBcodeHTML['quoteclose']	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","bbquoteclose"))));
	$BBcodeHTML['msgcache1'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","msgcache1"))));
	$BBcodeHTML['msgcache2'] 	= 	trim(stripslashes(stripslashes($tpl->gettemplate("entete","msgcache2"))));

	if (strlen($_FORUMCFG['censuredwords'])>0) {
		$Rpl="**************************************************************************************************";
		$ListWords = explode(", ",$_FORUMCFG['censuredwords']);
		for ($i=0;$i<count($ListWords);$i++) {
			$ListWordsRpl[$i] = "\\1".getformatmsg(substr($ListWords[$i],0,1).substr($Rpl,0,strlen($ListWords[$i])-2).substr($ListWords[$i],-1))."\\2";
			$ListWords[$i] = "'([^a-zA-Z])".addslashes(getformatmsg($ListWords[$i]))."([^a-zA-Z]|\Z|\s)'si";
		}
	}

}

function getcolorsearch($chain)
{
	global $SearchOrig, $SearchReplace;

	$chain	=	stripslashes($chain);
	if (isset($SearchOrig) && count($SearchOrig)>0) {
        return(str_replace($SearchOrig,$SearchReplace,$chain));
    } else {
        return($chain);
    }
}

// *******************************************************
// *                 FONCTIONS WYSIWYG                   *
// *******************************************************

function get_htmlmoztocss($color)
{
	$color	=	strtolower($color);

	switch($color)
	{
		case 'aqua':
		  $rgb	=	'0, 255, 255';
		  break;
		case 'black':
		  $rgb	=	'0, 0, 0';
		  break;
		case 'blue':
		  $rgb	=	'0, 0, 255';
		  break;
		case 'fuchsia':
		  $rgb	=	'255, 0, 255';
		  break;
		case 'gray':
		  $rgb	=	'128, 128, 128';
		  break;
		case 'green':
		  $rgb	=	'0, 128, 0';
		  break;
		case 'lime':
		  $rgb	=	'0, 255, 0';
		  break;
		case 'maroon':
		  $rgb	=	'128, 0, 0';
		  break;
		case 'navy':
		  $rgb	=	'0, 0, 128';
		  break;
		case 'olive':
		  $rgb	=	'128, 128, 0';
		  break;
		case 'purple':
		  $rgb	=	'128, 0, 128';
		  break;
		case 'red':
		  $rgb	=	'255, 0, 0';
		  break;
		case 'silver':
		  $rgb	=	'192, 192, 192';
		  break;
		case 'teal':
		  $rgb	=	'0, 128, 128';
		  break;
		case 'white':
		  $rgb	=	'255, 255, 255';
		  break;
		case 'yellow':
		  $rgb	=	'255, 255, 0';
		  break;
		default:
		  $array_dec		=	array();

		  $color			=	substr($color,1,strlen($color));
		  $array_dec[0]		=	hexdec(substr($color,0,2));
		  $array_dec[1]		=	hexdec(substr($color,2,2));
		  $array_dec[2]		=	hexdec(substr($color,4,2));

		  $rgb			=	implode(", ",$array_dec);
		  break;
	}

	$text = "<span style=\"color: rgb(".$rgb.");\">";
	return($text);
}

// #### PARSER CSS ####
function css_process($chaine, $statut='')
{
	static $close_tag	=	"";
	$to_return		=	"";

	if ($statut == 'open') {
		if (NAVIGATEUR == "MOZILLA") {
			$css			=	preg_replace("/<(span|font|br) (.*?)>/si","\\2",$chaine);
			$css			=	str_replace("\" ","\"----",$css);
			$temp_options	=	explode("----",$css);

			foreach ($temp_options AS $html_options) {
				if (substr($html_options,0,6) == "style=") {
					$html_options	=	preg_replace("/style=\"(.*?);\"/si","\\1",$html_options);

					$temp_array = explode("; ",$html_options);

					foreach ($temp_array AS $value) {
						switch ($value)
						{
							case 'font-weight: bold' :
							  $to_return .= '[bold]';
							  $close_tag  = '[/bold]'.$close_tag;
							  break;
							case 'font-style: italic' :
							  $to_return .= '[ita]';
							  $close_tag  = '[/ita]'.$close_tag;
							  break;
							case 'text-decoration: underline' :
							  $to_return .= '[under]';
							  $close_tag  = '[/under]'.$close_tag;
							  break;
							default :
							  if (substr($value,0,11)=="font-family") {
							  	$font	=	preg_replace("/font-family: (.*?)/si","\\1",$value);
							  	$to_return .=	'[font='.$font.']';
							  	$close_tag  = '[/font]'.$close_tag;
							  }

                              if (substr($value,0,5)=="color") {
							  	$decstring	=	preg_replace("/color: rgb\((.*?)\)/","\\1",$value);
							  	$array_dec	=	explode(", ",$decstring);
							  	$hexstring	=	"";

							  	foreach ($array_dec AS $dec_value) {
							  		$tempvalue		=	dechex($dec_value);
							  		if (strlen($tempvalue)==1) {
                                        $tempvalue	=	"0".$tempvalue;
                                    }

							  		$hexstring		.=	$tempvalue;
							  	}

							  	$to_return .=	'[color=#'.$hexstring.']';
							  	$close_tag  = '[/color]'.$close_tag;
							  }
							  break;
						}
					}
				} elseif(substr($html_options,0,5) == "size=") {
					$html_options	=	preg_replace("/size=\"(.*?)\"/si","\\1",$html_options);
					$to_return .=	'[size='.$html_options.']';
					$close_tag  = 	'[/size]'.$close_tag;
				}
			}
		} elseif(NAVIGATEUR == "MSIE") {
			$css			=	preg_replace("/<font (.*?)>/si","\\1",$chaine);
			$temp_options	=	explode(" ",$css);

			foreach ($temp_options AS $html_options) {
				if (substr($html_options,0,5) == "face=") {
					$html_options	=	preg_replace("/face=(.*?)/si","\\1",$html_options);
					$to_return		.=	'[font='.$html_options.']';
					$close_tag		=	'[/font]'.$close_tag;
				} elseif (substr($html_options,0,5) == "size=") {
					$html_options	=	preg_replace("/size=(.*?)/si","\\1",$html_options);
					$to_return		.=	'[size='.$html_options.']';
					$close_tag		=	'[/size]'.$close_tag;
				} elseif(substr($html_options,0,6) == "color=") {
					$html_options	=	preg_replace("/color=(.*?)/si","\\1",$html_options);
					$to_return		.=	'[color='.$html_options.']';
					$close_tag		=	'[/color]'.$close_tag;
				}
			}
		}
	} elseif($statut == 'close') {
		$to_return	=	$close_tag;
		$close_tag	=	"";
	} else {
        $to_return	=	$chaine;
    }

	return($to_return);
}


// #### PARSER BALISE DIV ####
function div_process($chaine, $statut='')
{
	static $active_tag	=	"";

	if($statut == 'open')
	{
		$chaine		=	preg_replace("/<div (.*?)>/si","\\1",$chaine);
		$chaine		=	str_replace("\"","",$chaine);

		// **** si CODE ****
		if(preg_match("|class=code_class|",$chaine) > 0)
		{
			$active_tag	=	"code";
			return("[code]");
		}

		// **** si QUOTE ****
		elseif(preg_match("|class=quote_class|",$chaine) > 0)
		{
			$active_tag	=	"quote";
			return("[quote]");
		}

		// **** si ALIGN ****
		elseif(preg_match("|text-align|",$chaine) > 0)
		{
			$chaine		=	preg_replace("/style=text-align: (.*?);/si","\\1",$chaine);

			switch($chaine)
			{
				case 'left':
					$active_tag 	= 	"left";
					$to_return		=	"[left]";
					break;
				case 'center':
					$active_tag		=	"center";
					$to_return		=	"[center]";
					break;
				case 'right':
					$active_tag		=	"right";
					$to_return		=	"[right]";
					break;
			}

			return($to_return);
		}
	}
	elseif($statut == 'close')
	{
		$to_return			=	"";
		switch($active_tag)
		{
			case 'code':
				$to_return 	= 	"[/code]";
				break;
			case 'quote':
				$to_return	=	"[/quote]";
				break;
			case 'left':
				$to_return	=	"[/left]";
				break;
			case 'center':
				$to_return	=	"[/center]";
				break;
			case 'right':
				$to_return	=	"[/right]";
				break;
		}

		$active_tag		=	"";
		return($to_return);
	}
	else
		return($chaine);
}

function code_process($chaine, $statut='')
{
	global $BBcodeHTML;

	if($statut == 'open')
		return($BBcodeHTML['codeopen']);
	elseif($statut == 'close')
		return($BBcodeHTML['codeclose']);
	else
		return(strip_tags($chaine));
}

function quote_process($chaine, $statut='')
{
	global $BBcodeHTML;

	if($statut == 'open')
		return($BBcodeHTML['quoteopen']);
	elseif($statut == 'close')
		return($BBcodeHTML['quoteclose']);
	else
		return($chaine);
}

function color_process($chaine, $statut='')
{
	global $BBcodeHTML;

	if($statut == 'open')
	{
		$chaine		=	preg_replace("/\[color=(.*?)\]/si",$BBcodeHTML['coloropen'],$chaine);
		return($chaine);
	}
	elseif($statut == 'close')
		return($BBcodeHTML['colorclose']);
	else
		return($chaine);
}

function size_process($chaine, $statut='')
{
	global $BBcodeHTML;

	if($statut == 'open')
	{
		$chaine		=	preg_replace("/\[size=(.*?)\]/si",$BBcodeHTML['sizeopen'],$chaine);
		return($chaine);
	}
	elseif($statut == 'close')
		return($BBcodeHTML['sizeclose']);
	else
		return($chaine);
}

function font_process($chaine, $statut='')
{
	global $BBcodeHTML;

	if($statut == 'open')
	{
		$chaine		=	preg_replace("/\[font=(.*?)\]/si",$BBcodeHTML['fontopen'],$chaine);
		return($chaine);
	}
	elseif($statut == 'close')
		return($BBcodeHTML['fontclose']);
	else
		return($chaine);
}

function code_edit_process($chaine, $statut='')
{
	global $_SKIN;

	if($statut == 'open')
	{
		if(NAVIGATEUR == "MSIE")
			$chaine		=	"<DIV class=code_class>";
		elseif(NAVIGATEUR == "MOZILLA")
			$chaine		=	"<DIV class=\"code_class\" style=\"background: $_SKIN[bgtable2]; border: 1px solid $_SKIN[textcol1]; color: $_SKIN[textcol2]; font-size: 10px; margin: 8px auto 0 auto; padding: 3px;\">";
	}
	elseif($statut == 'close')
		$chaine		=	"</DIV><br>";
	else
	{
		if(NAVIGATEUR == "MSIE")
			$chaine	=	str_replace("<br />","<br>",$chaine);

		$chaine		=	str_replace(" ","&nbsp;",$chaine);
		$chaine		=	htmlentities($chaine, ENT_COMPAT,'ISO-8859-1', true);

		if(NAVIGATEUR == "MSIE")
			$chaine	=	str_replace("&lt;br&gt;","<br>",$chaine);
	}

	return($chaine);
}

function quote_edit_process($chaine, $statut='')
{
	global $_SKIN;

	if($statut == 'open')
	{
		if(NAVIGATEUR == "MSIE")
			$chaine		=	"<DIV class=quote_class>";
		elseif(NAVIGATEUR == "MOZILLA")
			$chaine		=	"<DIV class=\"quote_class\" style=\"background: $_SKIN[bgtable3]; border: 1px solid $_SKIN[textcol1]; color: $_SKIN[textcol1]; font-size: 10px; margin: 8px auto 0 auto; padding: 3px;\">";
	}
	elseif($statut == 'close')
		$chaine		=	"</DIV><br>";

	return($chaine);
}

function font_edit_process($chaine, $statut='')
{
	global $_SKIN;

	if($statut == 'open')
	{
		if(NAVIGATEUR == "MSIE")
			$chaine		=	preg_replace("/\[font=(.*?)\]/si","<FONT face=\\1>",$chaine);
		elseif(NAVIGATEUR == "MOZILLA")
			$chaine		=	preg_replace("/\[font=(.*?)\]/si","<font style=\"font-family: \\1;\">",$chaine);
	}
	elseif($statut == 'close')
		$chaine		=	"</font>";

	return($chaine);
}

function size_edit_process($chaine, $statut='')
{
	global $_SKIN;

	if($statut == 'open')
	{
		if(NAVIGATEUR == "MSIE")
			$chaine		=	preg_replace("/\[size=(.*?)\]/si","<FONT size=\\1>",$chaine);
		elseif(NAVIGATEUR == "MOZILLA")
			$chaine		=	preg_replace("/\[size=(.*?)\]/si","<FONT size=\\1>",$chaine);
	}
	elseif($statut == 'close')
		$chaine	=	"</font>";

	return($chaine);
}

function color_edit_process($chaine, $statut='')
{
	global $_SKIN;

	if($statut == 'open')
	{
		if(NAVIGATEUR == "MSIE")
			$chaine		=	preg_replace("/\[color=(.*?)\]/si","<FONT color=\\1>",$chaine);
		elseif(NAVIGATEUR == "MOZILLA")
			$chaine		=	preg_replace("/\[color=(.*?)\]/sie","get_htmlmoztocss('\\1')",$chaine);
	}
	elseif($statut == 'close')
	{
		if(NAVIGATEUR == "MSIE")
			$chaine		=	"</font>";
		elseif(NAVIGATEUR == "MOZILLA")
			$chaine		=	"</span>";
	}

	return($chaine);
}

// #### PARSER HTML ####
function parser($tableau, $chaine)
{
	$tabl			=	preg_split("/(".$tableau['open']."|".$tableau['close'].")/si", $chaine, -1, PREG_SPLIT_DELIM_CAPTURE);

	$process_list	=	array();
	$start 			= 	array();

	$niv			=	0;

	foreach($tabl AS $key => $value)
	{
		if(preg_match("/".$tableau['open']."/si",$value) == 1)
		{
			$niv++;
			$start[$niv]				= 	$key;
		}
		elseif(preg_match("/".$tableau['close']."/si",$value) == 1)
		{
			if(isset($start[$niv]))
			{
				$end						=	$key;

				$tabl[$start[$niv]]			=	$tableau['function']($tabl[$start[$niv]],'open');

				if(strlen($tableau['function']) > 0 && !in_array($key, $process_list))
				{
					for($i = $start[$niv]+1; $i < $end; $i++)
					{
						$tabl[$i]			=	$tableau['function']($tabl[$i]);
						$process_list[]		=	$i;
					}
				}

				$tabl[$end] 				=	$tableau['function']($tabl[$end],'close');

				$process_list[]				=	$start[$niv];
				$process_list[]				=	$end;

				unset($start[$niv]);
				$niv--;
			}
		}
	}
	return(implode("",$tabl));
}



// ###########################################################
// #### TRANSFORME LE CODE HTML ISSU DU WYSIWYG EN BBCODE ####
// ###########################################################

function change_smilies_to_code($msg)
{

	global $_SERVER, $_FORUMCFG;

	$table_smileys	=	getloadsmileys();

	for($i=0;$i<count($table_smileys);$i++)
		$msg = preg_replace("'<IMG src=\"".$_FORUMCFG['urlforum']."smileys/".$table_smileys[$i]['img']."\".*?>'si",$table_smileys[$i]['code'],$msg);

	return($msg);
}

function convert_html_to_bbcode($msg)
{

	// #### TRANSFORMATION DES SMILEYS ####

	$msg		=	change_smilies_to_code($msg);
	$msg		=	str_replace("&amp;","&",$msg);

	$regex		=	array();

	if(NAVIGATEUR == "MSIE")
	{
		$_parser[0]['open']			=	"<div .*?>";
		$_parser[0]['close']		=	"<\/div>";
		$_parser[0]['function']		=	"div_process";

		$_parser[1]['open']			=	"<font .*?>";
		$_parser[1]['close']		=	"<\/font>";
		$_parser[1]['function']		=	"css_process";

		$regex['bold']['mask']		=	"/<STRONG>(.*?)<\/STRONG>/si";
		$regex['bold']['replace']	=	"[bold]\\1[/bold]";

		$regex['ita']['mask']		=	"/<EM>(.*?)<\/EM>/si";
		$regex['ita']['replace']	=	"[ita]\\1[/ita]";

		$regex['under']['mask']		=	"/<U>(.*?)<\/U>/si";
		$regex['under']['replace']	=	"[under]\\1[/under]";

		$regex['left']['mask']		=	"/<P align=left>(.*?)<\/P>/si";
		$regex['left']['replace']	=	"[left]\\1[/left]";

		$regex['center']['mask']	=	"/<P align=center>(.*?)<\/P>/si";
		$regex['center']['replace']	=	"[center]\\1[/center]";

		$regex['right']['mask']		=	"/<P align=right>(.*?)<\/P>/si";
		$regex['right']['replace']	=	"[right]\\1[/right]";

		$regex['mail']['mask']		=	"/<A href=\"mailto:(.*?)\">(.*?)<\/A>/si";
		$regex['mail']['replace']	=	"[mail]\\1[/mail]";

		$regex['url']['mask']		=	"/<A href=\"(?!(?i)javascript:)(.*?)\">(.*?)<\/A>/si";
		$regex['url']['replace']	=	"[url=\\1]\\2[/url]";

		$regex['img']['mask']		=	"/<IMG .*src=\"(.*?)\".*?>/si";
		$regex['img']['replace']	=	"[img]\\1[/img]";

		$msg		=	parser($_parser[0],$msg);
		$msg		=	parser($_parser[1],$msg);
		$msg		=	preg_replace($regex['bold']['mask'],$regex['bold']['replace'],$msg);
		$msg		=	preg_replace($regex['ita']['mask'],$regex['ita']['replace'],$msg);
		$msg		=	preg_replace($regex['under']['mask'],$regex['under']['replace'],$msg);
		$msg		=	preg_replace($regex['left']['mask'],$regex['left']['replace'],$msg);
		$msg		=	preg_replace($regex['center']['mask'],$regex['center']['replace'],$msg);
		$msg		=	preg_replace($regex['right']['mask'],$regex['right']['replace'],$msg);
		$msg		=	preg_replace($regex['mail']['mask'],$regex['mail']['replace'],$msg);
		$msg		=	preg_replace($regex['url']['mask'],$regex['url']['replace'],$msg);
		$msg		=	preg_replace($regex['img']['mask'],$regex['img']['replace'],$msg);

		$msg		=	str_replace("<P>","",$msg);
		$msg		=	str_replace("</P>","\n",$msg);
		$msg		=	str_replace("<BR>","\n",$msg);
		$msg		=	str_replace("&nbsp;"," ",$msg);
		$msg		=	strip_tags($msg);

	}
	elseif(NAVIGATEUR == "MOZILLA")
	{
		 // #### OK pour le moment ####
		 // #### voir à débugguer si on met du texte en formattage + ensuite citation, le formattage disparaît ####

		$_parser[0]['open']			=	"<div .*?>";
		$_parser[0]['close']		=	"<\/div>";
		$_parser[0]['function']		=	"div_process";

		$_parser[1]['open']			=	"<font .*?>";
		$_parser[1]['close']		=	"<\/font>";
		$_parser[1]['function']		=	"css_process";

		$_parser[2]['open']			=	"<span .*?>";
		$_parser[2]['close']		=	"<\/span>";
		$_parser[2]['function']		=	"css_process";

		$_parser[3]['open']			=	"<br .*?>";
		$_parser[3]['close']		=	"<\/br>";
		$_parser[3]['function']		=	"css_process";

		$regex['mail']['mask']		=	"/<A href=\"mailto:(.*?)\">(.*?)<\/A>/si";
		$regex['mail']['replace']	=	"[mail]\\1[/mail]";

		$regex['url']['mask']		=	"/<A href=\"(?!(?i)javascript:)(.*?)\">(.*?)<\/A>/si";
		$regex['url']['replace']	=	"[url=\\1]\\2[/url]";

		$regex['img']['mask']		=	"/<IMG .*src=\"(.*?)\">/si";
		$regex['img']['replace']	=	"[img]\\1[/img]";

		$msg		=	parser($_parser[0],$msg);
		$msg		=	parser($_parser[1],$msg);
		$msg		=	parser($_parser[2],$msg);
		$msg		=	parser($_parser[3],$msg);

		$msg		=	preg_replace($regex['mail']['mask'],$regex['mail']['replace'],$msg);
		$msg		=	preg_replace($regex['url']['mask'],$regex['url']['replace'],$msg);
		$msg		=	preg_replace($regex['img']['mask'],$regex['img']['replace'],$msg);


		$msg		=	strip_tags($msg);
		$msg		=	str_replace("&nbsp;"," ",$msg);

	}

	return($msg);
}

// ################################################################################
// #### TRANSFORME DU BBCODE EN HTML SELON LE NAVIGATEUR POUR L'EDITION DE MSG ####
// ################################################################################


function change_code_to_smileys($msg)
{


	global $table_smileys, $_SERVER, $_FORUMCFG;

	for($i=0;$i<count($table_smileys);$i++)
	{
		$msg = str_replace($table_smileys[$i]['code'],"<img src=\"".$_FORUMCFG['urlforum']."smileys/".$table_smileys[$i]['img']."\">", $msg);
	}

	return($msg);
}

function get_html_from_bbcode($chaine)
{
	global $_SKIN;

	// #### TRANSFORMATION DES SMILEYS ####

	$chaine		=	change_code_to_smileys($chaine);

	$regex		=	array();

	$_parser[0]['open']			=	"\[code\]";
	$_parser[0]['close']		=	"\[\/code\]";
	$_parser[0]['function']		=	"code_edit_process";

	$_parser[1]['open']			=	"\[quote\]";
	$_parser[1]['close']		=	"\[\/quote\]";
	$_parser[1]['function']		=	"quote_edit_process";

	$_parser[2]['open']			=	"\[font=.*?\]";
	$_parser[2]['close']		=	"\[\/font\]";
	$_parser[2]['function']		=	"font_edit_process";

	$_parser[3]['open']			=	"\[size=.*?\]";
	$_parser[3]['close']		=	"\[\/size\]";
	$_parser[3]['function']		=	"size_edit_process";

	$_parser[4]['open']			=	"\[color=.*?\]";
	$_parser[4]['close']		=	"\[\/color\]";
	$_parser[4]['function']		=	"color_edit_process";

	if(NAVIGATEUR == "MSIE")
	{
		$search		=	array(	"/\[bold\](.*?)\[\/bold\]/si",
						"/\[ita\](.*?)\[\/ita\]/si",
						"/\[under\](.*?)\[\/under\]/si",
						"/\[left\](.*?)\[\/left\]/si",
						"/\[center\](.*?)\[\/center\]/si",
						"/\[right\](.*?)\[\/right\]/si",
						"/\[mail\](.*?)\[\/mail\]/si",
						"/\[url=(.*?)\](.*?)\[\/url\]/si",
						"/\[img\](.*?)\[\/img\]/si");

		$replace	=	array(	"<STRONG>\\1</STRONG>",
						"<EM>\\1</EM>",
						"<U>\\1</U>",
						"<P align=left>\\1</P>",
						"<P align=center>\\1</P>",
						"<P align=right>\\1</P>",
						"<A href=\"mailto:\\1\">\\1</A>",
						"<A href=\"\\1\">\\2</A>",
						"<IMG src=\"\\1\">");
	}
	elseif(NAVIGATEUR == "MOZILLA")
	{
		$search		=	array(	"/\[bold\](.*?)\[\/bold\]/si",
						"/\[ita\](.*?)\[\/ita\]/si",
						"/\[under\](.*?)\[\/under\]/si",
						"/\[left\](.*?)\[\/left\]/si",
						"/\[center\](.*?)\[\/center\]/si",
						"/\[right\](.*?)\[\/right\]/si",
						"/\[mail\](.*?)\[\/mail\]/si",
						"/\[url=(.*?)\](.*?)\[\/url\]/si",
						"/\[img\](.*?)\[\/img\]/si");

		$replace	=	array(	"<span style=\"font-weight: bold;\">\\1</span>",
						"<span style=\"font-style: italic;\">\\1</span>",
						"<span style=\"text-decoration: underline;\">\\1</span>",
						"<div style=\"text-align: left;\">\\1</div>",
						"<div style=\"text-align: center;\">\\1</div>",
						"<div style=\"text-align: rigth;\">\\1</div>",
						"<A href=\"mailto:\\1\">\\1</A>",
						"<A href=\"\\1\">\\2</A>",
						"<IMG src=\"\\1\">");

	}

	$chaine		=	parser($_parser[0],$chaine);
	$chaine		=	parser($_parser[1],$chaine);
	$chaine		=	parser($_parser[2],$chaine);
	$chaine		=	parser($_parser[3],$chaine);
	$chaine		=	parser($_parser[4],$chaine);

	$chaine		=	preg_replace($search,$replace,$chaine);

	return($chaine);
}

// ##################################################################
// #### TRANSFORME LE BBCODE EN HTML POUR AFFICHAGE SUR LE FORUM ####
// ##################################################################

function format_bbcode_to_html($chaine)
{
	global $BBcodeHTML;

	$chaine	=	trim($chaine);
	$chaine	=	str_replace('<br />','',$chaine);
	$chaine =	str_replace('\\\"','\"',$chaine);

	return $chaine;
}

function getreturnbbcode($msg,$topic=false)
{
	global $bground, $_SKIN, $_USER, $_FORUMCFG, $DetailMsg, $sql, $BBcodeHTML;
	$regex		=	array();

	$_parser[0]['open']			=	"\[code\]";
	$_parser[0]['close']		=	"\[\/code\]";
	$_parser[0]['function']		=	"code_process";

	$_parser[1]['open']			=	"\[quote\]";
	$_parser[1]['close']		=	"\[\/quote\]";
	$_parser[1]['function']		=	"quote_process";

	$_parser[2]['open']			=	"\[color=.*?\]";
	$_parser[2]['close']		=	"\[\/color\]";
	$_parser[2]['function']		=	"color_process";

	$_parser[3]['open']			=	"\[size=[\d]\]";
	$_parser[3]['close']		=	"\[\/size\]";
	$_parser[3]['function']		=	"size_process";

	$_parser[4]['open']			=	"\[font=.*?\]";
	$_parser[4]['close']		=	"\[\/font\]";
	$_parser[4]['function']		=	"font_process";

	$msg	=	parser($_parser[0],$msg);
	$msg	=	parser($_parser[1],$msg);
	$msg	=	parser($_parser[2],$msg);
	$msg	=	parser($_parser[3],$msg);
	$msg	=	parser($_parser[4],$msg);

	// **** transformation autres balises ****
	$search=array("/(?<!url:|url=|\])(http|ftp|https):\/\/(([a-zA-Z0-9.\/@:%=?~_#\-\+]|&amp;)+)(?<![\.:#%?])/",
		      "/(\Z|\s|^|[^a-zA-Z\/])www\\.(([a-zA-Z0-9.\/@:%=?~_#\-\+]|&amp;)+)(?<![\.:#%?])/",
		      "/\[swf\]url:((?:[a-zA-Z0-9.\/:%~_\-]+)\.swf) largeur:([0-9]+) hauteur:([0-9]+) \[\/swf\]/",
		      "/\[mail\]([a-zA-Z0-9-_\.]+)@([a-zA-Z0-9-_\.]+\.[a-zA-Z0-9]{2,4})\[\/mail\]/",
		      "/\[img\](?!(?i)javascript:)(.*?)\[\/img\]/",
		      "/\[bold\](.*?)\[\/bold\]/si",
		      "/\[ita\](.*?)\[\/ita\]/si",
		      "/\[under\](.*?)\[\/under\]/si",
		      "/\[center\](.*?)\[\/center\]/si",
		      "/\[left\](.*?)\[\/left\]/si",
		      "/\[right\](.*?)\[\/right\]/si",
		      "/\[url=(?!(?i)javascript:)(.*?)\](.*?)\[\/url\]/si",
		      "/\[\!([\/]{0,1})(code|quote|mail|img|bold|ita|under|center|font|size|color|url)\]/si");

	$replace=array($BBcodeHTML['urlauto1'],
		       $BBcodeHTML['urlauto2'],
		       $BBcodeHTML['swf'],
		       $BBcodeHTML['mail'],
		       $BBcodeHTML['img'],
		       $BBcodeHTML['bold'],
		       $BBcodeHTML['ita'],
		       $BBcodeHTML['under'],
		       $BBcodeHTML['center'],
		       $BBcodeHTML['left'],
		       $BBcodeHTML['right'],
		       $BBcodeHTML['url'],
		       "[\\1\\2]");

	$msg=preg_replace($search,$replace,$msg);

	// **** gestion de la balise des messages cachés ****
	if($_FORUMCFG['canpostmsgcache']=="Y" && preg_match("|\[cache\]|",$msg) > 0 && $topic==true)
	{
		$isposter=$sql->query("SELECT COUNT(*) AS present FROM "._PRE_."posts WHERE parent=%d AND idmembre=%d", [$DetailMsg['parent'], $_USER['userid']])->execute();
		$isrespond=$isposter->fetch_array();

		if($isrespond['present']>0)
			$msg=preg_replace("/\[cache\](.*?)\[\/cache\]/si",$BBcodeHTML['msgcache1'],$msg);
		else
			$msg=preg_replace("/\[cache\](.*?)\[\/cache\]/si",$BBcodeHTML['msgcache2'],$msg);
	}

	return($msg);
}

function censuredwords($msg)
{
	global $_FORUMCFG, $ListWords, $ListWordsRpl;

	if(strlen($_FORUMCFG['censuredwords'])>0)
		$msg=preg_replace($ListWords,$ListWordsRpl,$msg);
	return($msg);
}

function getquote($id)
{
	global $sql, $tpl, $QuoteName, $OrigMsg, $QuoteMsg, $_USER;

	$query 						= 	$sql->query("SELECT pseudo,msg FROM "._PRE_."posts WHERE idpost=%d", $id)->execute();
	list($QuoteName,$OrigMsg) 	= 	$query->fetch_array();

	$QuoteName 					= 	getformatrecup($QuoteName);
	$OrigMsg   					= 	getformatrecup($OrigMsg);

	$QuoteMsg					=	preg_replace("/\[quote\](.*?)\[\/quote\]/si","",$OrigMsg);
	$QuoteMsg					=	preg_replace("/\[cache\](.*?)\[\/cache\]/si",$tpl->gettemplate("repondre","msgcache"),$QuoteMsg);

	$msg						= 	$tpl->gettemplate("repondre","origmsg");

	return($msg);
}

function gettopictitle($id,$annonce=false)
{
	global $sql;
	if ($annonce) {
        $query = $sql->query("SELECT sujet FROM "._PRE_."annonces WHERE idpost=%d",$id)->execute();
    } else {
        $query = $sql->query("SELECT idforum,sujet,nbrep,opentopic,poll FROM "._PRE_."topics WHERE idtopic=%d",$id)->execute();
    }
	$nb = $query->num_rows();

	if($nb==0)
		return false;
	else
	{
		$j		=	$query->fetch_array();
		return($j);
	}
}

function updateforumlastposter($idforum)
{
	global $sql;

	$query = $sql->query("SELECT COUNT(*) AS nbtopic FROM "._PRE_."topics WHERE idforum=%d",$idforum)->execute();
	list($nbtopic)=$query->fetch_array();

	$query = $sql->query("SELECT COUNT(*) AS nbmsg FROM "._PRE_."posts WHERE idforum=%d",$idforum)->execute();
	list($nbmsg)=$query->fetch_array();
	$nbmsg = $nbmsg - $nbtopic;

	if($nbtopic>0)
	{
		$query = $sql->query("SELECT idpost,date,idmembre,pseudo FROM "._PRE_."posts WHERE idforum=%d ORDER BY date DESC", $idforum)->execute();
		$i=$query->fetch_array();
	}
	else
	{
		$i['pseudo']="";
		$i['date']=0;
		$i['idpost']=0;
	}

	$i['pseudo'] = getformatdbtodb($i['pseudo']);
	$query=$sql->query("UPDATE "._PRE_."forums SET forumtopic=%d ,forumposts=%d ,lastforumposter='%s', lastdatepost='%s', lastidpost=%d WHERE forumid=%d", array($nbtopic, $nbmsg, $i['pseudo'], $i['date'], $i['idpost'], $idforum))->execute();
}

function updatetopiclastposter($idpost)
{
	global $sql;

	$query = $sql->query("SELECT COUNT(*) AS nbpost FROM "._PRE_."posts WHERE parent=%d", $idpost)->execute();
	list($nbpost) = $query->fetch_array();
	$nbpost--;

	$query = $sql->query("SELECT idpost,date,idmembre,pseudo FROM "._PRE_."posts WHERE parent=%d ORDER BY date DESC", $idpost)->execute();
	$j = $query->fetch_array();

	//à voir !!

	$j['pseudo'] = getformatdbtodb($j['pseudo']);
	$query = $sql->query("UPDATE "._PRE_."topics SET nbrep=%d, derposter='%s', idderpost=%d, datederrep='%s', idderpost=%d WHERE idtopic=%d", array($nbpost, $j['pseudo'], $j['idmembre'], $j['date'], $j['idpost'], $idpost))->execute();
}

function updatenbtopics()
{
	global $sql, $_FORUMCFG;

	$query = $sql->query("SELECT COUNT(*) AS nbtopics FROM "._PRE_."topics")->execute();
	list($nbtopics) = $query->fetch_array();

	$query = $sql->query("UPDATE "._PRE_."config SET valeur=%d WHERE options='statnbtopics'", $nbtopics)->execute();
	$_FORUMCFG['statnbtopics'] = $nbtopics;
}

function updatenbposts()
{
	global $sql,$_FORUMCFG;

	$query = $sql->query("SELECT COUNT(*) AS nbposts FROM "._PRE_."posts")->execute();
	list($nbposts) = $query->fetch_array();

	$nbposts = $nbposts - $_FORUMCFG['statnbtopics'];
	$query = $sql->query("UPDATE "._PRE_."config SET valeur=%d WHERE options='statnbposts'", $nbposts)->execute();
}

function updatemembers()
{
	global $sql;

	$query = $sql->query("SELECT login FROM "._PRE_."user WHERE userstatus <> '0' ORDER BY registerdate DESC")->execute();
	$nbuser = $query->num_rows();
	list($lastmember) = $query->fetch_array();

	$lastmember = getformatdbtodb($lastmember);

	$query = $sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='statnbuser'", $nbuser)->execute();
	$query = $sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='statlastmember'", $lastmember)->execute();
}

// A supprimer ?????????????????????
function updatestatsmembers()
{
	global $sql;

	$query = $sql->query("SELECT COUNT(*) AS nbmb FROM "._PRE_."user")->execute();
	list($nbmb) = $query->fetch_array();

	$query = $sql->query("SELECT login FROM "._PRE_."user ORDER BY registerdate DESC LIMIT 0,1")->execute();
	list($lastpseudo) = $query->fetch_array();

	$query = $sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='statnbuser'", $nbmb)->execute();
	$query = $sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='statlastmember'", $lastpseudo)->execute();
}

function updatepmstats($user)
{
	global $sql,$_USER;

	$query = $sql->query("SELECT COUNT(*) AS nbpm,vu FROM "._PRE_."privatemsg WHERE iddest='%d' GROUP BY vu", $user)->execute();
	$nb = $query->num_rows();

	$nbpmtot=0;
	$nbpmnew=0;

	if ($nb>0) {
		while($j = $query->fetch_array()) {
			if ($j['vu']==0) {
                $nbpmnew=$j['nbpm'];
            }
			$nbpmtot+=$j['nbpm'];
		}
	}

	$query = $sql->query("UPDATE "._PRE_."user SET nbpmtot=%d, nbpmvu=%d WHERE userid=%d", array($nbpmtot, $nbpmnew, $user))->execute();

	if ($user == $_USER['userid'] && $nbpmnew < $_USER['nbpmvu']) {
        sendcookie("nbpmvu",$nbpmnew,-1);
    }
}

function getfuseauhoraire()
{
	global $_USER,$_FORUMCFG;

	$zone=$_USER['timezone'];

	$fuseaux = array();

	$fuseaux[-12] = "GMT -12:00, Eniwetok";
	$fuseaux[-11] = "GMT -11:00, Samoa";
	$fuseaux[-10] = "GMT -10:00, Hawaii";
	$fuseaux[-9]  = "GMT -9:00, Alaska";
	$fuseaux[-8]  = "GMT -8:00, PST, Pacific US";
	$fuseaux[-7]  = "GMT -7:00, MST, Mountain US";
	$fuseaux[-6]  = "GMT -6:00, CST, Central US";
	$fuseaux[-5]  = "GMT -5:00, EST, Eastern US";
	$fuseaux[-4]  = "GMT -4:00, Atlantic, Canada";
	$fuseaux[-3]  = "GMT -3:00, Brazilia, Buenos Aries";
	$fuseaux[-2]  = "GMT -2:00, Mid-Atlantic";
	$fuseaux[-1]  = "GMT -1:00, Cape Verdes";
	$fuseaux[0]   = "GMT, Greenwich, London, Lisbon, Casablanca";
	$fuseaux[1]   = "GMT +1:00, Berlin, Rome, Madrid, Paris";
	$fuseaux[2]   = "GMT +2:00, Israel, Cairo";
	$fuseaux[3]   = "GMT +3:00, Moscow, Kuwait, Baghdad";
	$fuseaux[4]   = "GMT +4:00, Abu Dhabi, Muscat";
	$fuseaux[5]   = "GMT +5:00, Islamabad, Karachi";
	$fuseaux[6]   = "GMT +6:00, Almaty, Dhaka";
	$fuseaux[7]   = "GMT +7:00, Bangkok, Jakarta";
	$fuseaux[8]   = "GMT +8:00, Hong Kong, Beijing";
	$fuseaux[9]   = "GMT +9:00, Tokyo, Osaka";
	$fuseaux[10]  = "GMT +10:00, Sydney, Melbourne, Guam";
	$fuseaux[11]  = "GMT +11:00, Magadan, Soloman Is.";
	$fuseaux[12]  = "GMT +12:00, Fiji, Wellington, Auckland";

	return($fuseaux[$zone]);
}

function updatebirth()
{
	global $sql,$tpl,$_FORUMCFG;

	$day	= gmstrftime("%d-%m",time()+(($_FORUMCFG['defaulttimezone'] + intval(date("I")))*3600));
	$year	= gmstrftime("%Y",time()+(($_FORUMCFG['defaulttimezone'] + intval(date("I")))*3600));


	$query	= $sql->query("SELECT 	"._PRE_."userplus.birth,
									"._PRE_."user.userid,
									"._PRE_."user.login,
									"._PRE_."user.userstatus
									 FROM "._PRE_."userplus 
									 LEFT JOIN "._PRE_."user ON "._PRE_."user.userid = "._PRE_."userplus.idplus 
									 WHERE "._PRE_."userplus.birth LIKE \"%s-%%\"", $day)->execute();
	$nb	= $query->num_rows();

	if ($nb>0) {
		while ($j = $query->fetch_array()) {
			$j['birth'] = explode("-",$j['birth']);
			$j['login'] = getformatrecup($j['login']);
			$tpl->box['age'] = $year-$j['birth'][2];

			$tpl->box['user'] = getformatpseudo($j['login'],$j['userstatus'],$j['userid']); // tpl: 2 x addslashes
			$birth[] = $tpl->gettemplate("entete","formatbirth");
		}

		$birth = implode(", ",$birth);
	} else {
        $birth = "";
    }

	$_FORUMCFG['birth'] = stripslashes(stripslashes($birth));
	$sql->query("UPDATE "._PRE_."config SET valeur='%s' WHERE options='birth'", stripslashes($birth))->execute();
}

// ********************************************************
// *         FONCTIONS DE FORMATTAGE DES DONNEES          *
// ********************************************************

function getformatpreview($msg)
{
	$msg = htmlentities($msg, ENT_COMPAT,'ISO-8859-1', true);


	$msg = addslashes(addslashes($msg));

	$msg = nl2br($msg);
	return($msg);
}

function getformathtml($msg)
{
	return($msg);
}

function getformatmsg($msg,$activenl2br=true)
{
	$msg = htmlentities($msg, ENT_COMPAT,'ISO-8859-1', true);

	if ($activenl2br) {
        $msg=nl2br($msg);
    }
	return($msg);
}

function getformatmsghtml($msg,$activenl2br=true)
{
	$msg = htmlentities($msg, ENT_COMPAT,'ISO-8859-1', true);
	$msg = ($msg);

	$msg = str_replace("&amp;lt;","&lt;",$msg);
	$msg = str_replace("&amp;gt;","&gt;",$msg);

	if ($activenl2br) {
        $msg=nl2br($msg);
    }
	return($msg);
}

function getformatrecup($msg,$strip=false)
{
	if ($strip) {
        $msg=strip_tags($msg);
    }
	if (get_magic_quotes_runtime()==0) {
        $msg=addslashes($msg);
    }

	$msg = addslashes($msg);
	return($msg);
}

function getformatdbtodb($msg)
{
	if (get_magic_quotes_runtime()==0) {
        $msg=addslashes($msg);
    }

	return($msg);
}

function getrecupforform($msg, $squote = false)
{
	if ($squote) {
        $msg = htmlentities($msg, ENT_COMPAT | ENT_QUOTES,'ISO-8859-1', true);
    } else {
        $msg = htmlentities($msg, ENT_COMPAT,'ISO-8859-1', true);
    }

	return($msg);
}

function getrecupfromcookie($cook)
{
	return($cook);
}

function getlocaltime($time,$format=0)
{
	global $_USER;

	$decalage		=	($_USER['timezone']+intval(date("I"))) * 3600;
	if ($format==1) {
        $result		=	gmstrftime("%d/%m/%Y",$time+$decalage);
    } else {
        $result		=	gmstrftime("%d/%m/%Y %H:%M",$time+$decalage);
    }
	return($result);
}

function getformatpseudo($pseudo,$status,$userid)
{
	global $tpl;

	if ($status<0) {
        $tpl->box['mbstatus']="ban";
    } else {
        $tpl->box['mbstatus']=$status;
    }
	$tpl->box['mbpseudo']=getformatrecup($pseudo);
	$tpl->box['mbuserid']=$userid;

	if ($status>1 || $status<0) {
        $chaine=$tpl->gettemplate("entete","mbpseudolink");
    } else {
        $chaine=$tpl->gettemplate("entete","mbpseudo");
    }
	return($chaine);
}

function cookdecode($tabl)
{
    $chaine = array();
	$transfert = explode("_",$tabl);
	for($i=0;$i<count($transfert);$i++) {
		$transitintestinal=explode("-",$transfert[$i]);
		$IdString = $transitintestinal[0];

		$chaine[$IdString."m"]=$transitintestinal[1];
	}
	return($chaine);
}

function cookencode($tabl,$reverse=false)
{
    $transfert = array();
    $chaine = '';

    foreach ($tabl as $key => $value) {
        $transfert[] = (string)(int)$key . "-" . $value;
    }

	if ($reverse) {
        $transfert = array_reverse($transfert);
    }

	if (count($tabl) > 0) {
        $chaine = implode("_",$transfert);
    }
	return($chaine);
}

function getemail($email)
{
	global $_FORUMCFG;

	$email = str_replace("@",$_FORUMCFG['emailmask'],$email);
	return($email);
}

function inversemail($email)
{
	global $_FORUMCFG;

	$email = str_replace($_FORUMCFG['emailmask'],"@",$email);
	return($email);
}

function formatstrformail($str)
{
  	$trans = get_html_translation_table(HTML_ENTITIES);
  	$trans = array_flip($trans);
  	$encoded = strtr($str, $trans);
  	return($encoded);
}

function recupDBforMail($msg)
{
	if (get_magic_quotes_runtime()==1) {
        $msg=stripslashes($msg);
    }

	//$msg=stripslashes($msg);
	return($msg);
}

function testemail($email)
{
    return( preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+'.
                 '@'.
                 '([-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]{2,}\.){1,3}'.
                 '[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]{2,4}$/',
                 $email) > 0 );
}

function testurl($url)
{
	if(preg_match("/^(http|ftp|https):\/\/([a-z0-9-\/\.@:%=?&;]+(?<![\.:%?&;]))$/",$url)==0)
		return false;
	else
		return true;
}

function testlength($var,$maxlength,$null="",$max="")
{
	global $tpl,$error,$_POST;

	if (strlen($null) > 0) {
		$testchain=preg_replace("/([\s]{1,})/","",$_POST[$var]);
		if(strlen($testchain)==0)
			$error=$tpl->attlang($null);
	}

	if ($maxlength > 0 && strlen($_POST[$var])>$maxlength) {
		if (strlen($max)>0) {
            $error=$tpl->attlang($max);
        } else {
            $_POST[$var]=substr($_POST[$var],0,$maxlength);
        }
	}
}

function test_max_length($msg, $maxlength)
{
	$chaine		=	strip_tags($msg);								// Supprime les balises HTML
	$chaine		=	preg_replace("/(\r\n|\n)/si","",$chaine);		// Supprime les retour à la ligne

	$trans		=	get_html_translation_table(HTML_ENTITIES);		// |
	$trans 		= 	array_flip($trans);								// > Remplace les entitées HTML par leur caractère équivalent
	$chaine		=	strtr($chaine,$trans);							// |

	$trop_plein	=	strlen($chaine) - $maxlength;

	if ($trop_plein > 0 && $maxlength > 0) {
        $msg	=	substr($msg,0,-($trop_plein));
    }

	return($msg);
}


// ********************************************************
// *         FORMATTAGE DE FORMULAIRES / TABLEAUX         *
// ********************************************************

function return_int($nombre)
{
	return(intval($nombre));
}

function Return_Checked($value)
{
	if($value)
		return(" CHECKED");
	else
		return("");
}


// ********************************************************
// *                 FONCTIONS D'AFFICHAGE                *
// ********************************************************

function affforumlist($cat)
{
global $zecook,$tpl,$Forums,$TabForum,$TabModos,$_USER,$_PERMFORUM,$_FORUMRIGHTS, $modoname;
	$chaine="";

	for($cpt=0;$cpt<count($TabForum);$cpt++)
		if($TabForum[$cpt]['forumcat']==$cat)
		{
			$Forums=$TabForum[$cpt];
			if(!empty($_PERMFORUM[$Forums['forumid']][0]))
			{
				$Forums['forumtitle']		=	getformatrecup($Forums['forumtitle']);
				$Forums['forumcomment']		=	getformatrecup($Forums['forumcomment']);
				if($Forums['openforum']=="Y")
				{
					if(empty($zecook[$Forums['forumid']."m"]))
						$zecook[$Forums['forumid']."m"] = 0;

					if ($zecook[$Forums['forumid']."m"] < ($Forums['forumtopic']+$Forums['forumposts']))
						$Forums['imgforum']="on";
					else
						$Forums['imgforum']="off";
				}
				else
					$Forums['imgforum']="closed";

				if($Forums['lastdatepost']>0)
				{
					$Forums['lastdatepost']=getlocaltime($Forums['lastdatepost']);
					$Forums['lastforumposter'] = getformatrecup($Forums['lastforumposter']);
					$tpl->box['infolastpost']=$tpl->gettemplate("forumlist","iflastpost");
				}
				else
					$tpl->box['infolastpost']=$tpl->gettemplate("forumlist","ifnolastpost");

				$modoname = "";
				$tpl->box['modonames'] = "";

				for($cpt2=0;$cpt2<count($TabModos);$cpt2++)
					if($TabModos[$cpt2]['forumident']==$Forums['forumid'])
					{
						$modoname=$TabModos[$cpt2]['modologin'];
						$tpl->box['modonames'] .= $tpl->gettemplate("forumlist","modonames");
					}
				if(strlen($modoname)>0)
				{
					$tpl->box['modolist'] = $tpl->gettemplate("forumlist","modolist");
				}
				else
					$tpl->box['modolist']="&nbsp;";

				$chaine.=$tpl->gettemplate("forumlist","ifforum");
			}
		}
return($chaine);
}


function affwritebox($cancache="Y")
{
global $table_smileys, $cachedir, $tpl, $_FORUMCFG, $Parent, $_USER;

	// On définit le template selon wysiwyg ou non
	if($_USER['wysiwyg']=="Y")		$tpl_writebox	=	"writebox_wysiwyg";
	else					$tpl_writebox	=	"writebox";

	if (!empty($tpl->box['quotemsg']) && strlen($tpl->box['quotemsg'])>0)
	{
		if($_USER['wysiwyg'] == "Y")
			$tpl->box['quotemsg']		=	get_html_from_bbcode($tpl->box['quotemsg']);
		else
			$tpl->box['quotemsg']=strip_tags($tpl->box['quotemsg']);
	}
	else
		$tpl->box['quotemsg']=NULLSTR;

	$compt=0;
	$tpl->box['smileybox']="";
	for($zz=0;$zz<18;$zz++)
	{
		$tpl->box['smileybox'].="\t\t";

		if($compt%3!=0)
			$tpl->box['smileybox'].="&nbsp; &nbsp;";

		if($_USER['wysiwyg']=="Y")
			$tpl->box['smileybox'].="<img src=\"".$cachedir."smileys/".$table_smileys[$zz]['img']."\" onClick=\"addsmile('".$table_smileys[$zz]['img']."');\" border=0>";
		else
			$tpl->box['smileybox'].="<a href=\"javascript:;\" onClick=\"addsmile(' ".$table_smileys[$zz]['code']." '); Compter(formulaire.msg,formulaire.limitchar)\"><img src=\"".$cachedir."smileys/".$table_smileys[$zz]['img']."\" border=0></A>";

		$compt++;
		if($compt%3==0)
			$tpl->box['smileybox'].="<P>\n\n";
		else
			$tpl->box['smileybox'].="\n";
	}


	if($_FORUMCFG['canpostmsgcache']=="Y" && $cancache=="Y" && $Parent==0)
		$tpl->box['cancache']=$tpl->gettemplate($tpl_writebox,"addcachebutton");
	else
		$tpl->box['cancache']=NULLSTR;

	$chaine=$tpl->gettemplate($tpl_writebox,"wrtboxaccueil");

return($chaine);
}


function afftopiclist($annonce=0,$template="list")
{
global $cookiespost,$_GET,$page,$tpl,$Topics;

	$chaine="";
	if($annonce==1)
		$Topics['icontopic']="annonce.gif";
	else
	{
		if(!empty($Topics['opentopic']) && $Topics['opentopic'] == 'N')
			$Topics['icontopic']="closed.gif";
		else
		{
			$IdString = $Topics['idtopic'];
			settype($IdString,"string");
			if (empty($cookiespost[$IdString."m"]) || $cookiespost[$IdString."m"]<($Topics['nbrep']+1))
				$Topics['icontopic']="on.gif";
			else
				$Topics['icontopic']="off.gif";
		}
	}
	$tpl->box['pretopic']="";

	if(!empty($Topics['postit']) && $Topics['postit']=="1")
		$tpl->box['pretopic'].=$tpl->gettemplate($template,"ifpostittopic");

	if(!empty($Topics['poll']) && $Topics['poll']>0)
		$tpl->box['pretopic'].=$tpl->gettemplate($template,"iftopicpoll");

	if($annonce==1)
	{
		$Topics['idtopic'] = $Topics['idpost'];
		$tpl->box['pretopic']=$tpl->gettemplate($template,"iftopicannonce");
		$tpl->box['linkpage']="readannonce";
	}
	else
		$tpl->box['linkpage']="detail";
	$Topics['sujet']=getformatrecup($Topics['sujet']);
	$tpl->box['topic']=$tpl->gettemplate($template,"topiclinktomsg");

	if(empty($Topics['nbrep']))			$Topics['nbrep'] = 0;
	$tpl->box['affichepages']=getpagestopic($Topics['nbrep'],$Topics['idtopic'],$page);

	if($Topics['userid']==0)
		$Topics['userstatus'] = 0;
	$Topics['loginposter']=getformatpseudo($Topics['pseudo'],$Topics['userstatus'],$Topics['userid']);
	$Topics['derposter'] = getformatrecup($Topics['derposter']);
	$Topics['datederrep']=getlocaltime($Topics['datederrep']);

	if($annonce==0)
		$tpl->box['gotobutton']=$tpl->gettemplate($template,"linklastmsg");
	else
		$tpl->box['gotobutton']= NULLSTR;

	$chaine=$tpl->gettemplate($template,"lignetopic");
	unset($tpl->box['pretopic'],$tpl->box['gotobutton']);

return($chaine);
}


// #### AFFICHAGE DU DETAIL D'UN MESSAGE ####
function affdetailtopic($annonce=0,$cit=true)
{
	global $tpl,$DetailMsg,$_FORUMCFG, $IdTopic, $NombreConnectes, $SearchOrig, $SearchReplace, $Grades;

	// **** Initialisation variables ****
	$chaine								=		NULLSTR;
	$tpl->box['affusergrade']			=		NULLSTR;

		$tpl->box['affpins']			=		NULLSTR;
		$tpl->box['affusergrade']		=		NULLSTR;
		$tpl->box['grade']				=		NULLSTR;
		$tpl->box['affusersign']		=		NULLSTR;
		$tpl->box['buttonmail']			=		NULLSTR;
		$tpl->box['affuserlogo']		=		NULLSTR;
		$tpl->box['affsujetpost']		=		NULLSTR;
		$tpl->box['buttonsearch']		=		NULLSTR;
		$tpl->box['affregisterdate']	=		NULLSTR;
		$tpl->box['isconnected']		=		NULLSTR;
		$tpl->box['buttonpm']			=		NULLSTR;

	// **** Si c'est un membre ****
	if($DetailMsg['posterid']>0)
	{
		// **** initialisation des variables ****
		$connected 					= 		false;
		$DetailMsg['pseudo']		=		$DetailMsg['login'];
		$Key_Grade					=		0;

		// **** gestion des grades ****
		if($_FORUMCFG['use_grades'] == "Y" && is_array($Grades))
		{
			foreach($Grades AS $key => $value)
			{
				$Key_Grade			=		$key;
				if($DetailMsg['usermsg'] < $value[1])
				{
					$Key_Grade --;
					break;
				}
			}
			$tpl->box['grade']		=		$Grades[$Key_Grade][0];

			for($i=0; $i< $Grades[$Key_Grade][2];$i++)
				$tpl->box['affpins'] 	.=		trim($tpl->gettemplate("detail", "imgpins"));

			$tpl->box['affusergrade']	=		$tpl->gettemplate("detail", "usergrade");
		}

		// **** connecté ou pas ? ****
		for($i = 0; $i < count($NombreConnectes); $i++)
			if($NombreConnectes[$i]['userid'] == $DetailMsg['posterid'])
				$connected 			= 		true;

		if($connected)		$tpl->box['isconnected'] 		= 		$tpl->gettemplate("detail","connecty");
		else				$tpl->box['isconnected'] 		= 		$tpl->gettemplate("detail","connectn");

		// **** gestion du logo ****
		if(preg_match("|^\"http://|",$DetailMsg['userlogo']) > 0)
		{
			if($_FORUMCFG['logos'][6] == "Y" && $_FORUMCFG['logos'][0] == "Y")
				$tpl->box['affuserlogo']	=		$tpl->gettemplate("detail","extuserlogo");
		}
		elseif(!empty($DetailMsg['userlogo']) && $_FORUMCFG['logos'][0] == "Y")
			$tpl->box['affuserlogo']		=		$tpl->gettemplate("detail","userlogo");

		// **** gestion date d'enregistrement ****
		if (!empty($DetailMsg['registerdate']))
		{
			$DetailMsg['registerdate']=getlocaltime($DetailMsg['registerdate'],1);
			$tpl->box['affregisterdate']=$tpl->gettemplate("detail","userinfo");
		}
	}
	else
		$DetailMsg['usercitation']=$tpl->attlang("guestcit");

	// **** gestion de la citation ****
	if (!empty($DetailMsg['usercitation']))
	{
		$DetailMsg['usercitation']		=		getformatrecup($DetailMsg['usercitation']);
		$tpl->box['affusercitation']	=		$tpl->gettemplate("detail","usercitation");
	}
	else
		$tpl->box['affusercitation']	=		NULLSTR;

	$DetailMsg['formatpseudo']=getformatpseudo($DetailMsg['pseudo'],$DetailMsg['userstatus'],$DetailMsg['userid']);

	if(!empty($DetailMsg['sujetpost']))
	{
		$DetailMsg['sujetpost']=getformatrecup($DetailMsg['sujetpost']);
		$tpl->box['affsujetpost']=$tpl->gettemplate("detail","msgsujet");
	}

	$DetailMsg['datepost']=getlocaltime($DetailMsg['datepost']);

	if($DetailMsg['smiles']=="Y")
		$DetailMsg['msgpost']=getreturnsmilies($DetailMsg['msgpost']);

	if($DetailMsg['afbbcode']=="Y")
	{
		if($DetailMsg['idpost']==$IdTopic)
			$DetailMsg['msgpost']=getreturnbbcode($DetailMsg['msgpost'],true);
		else
			$DetailMsg['msgpost']=getreturnbbcode($DetailMsg['msgpost']);
	}

	if(isset($SearchOrig) && count($SearchOrig)>0)
		$DetailMsg['msgpost'] = preg_replace("/(.+?)((<(.*?)>)|$)/sie","getcolorsearch('\\1').stripslashes('\\2')",$DetailMsg['msgpost']);

	$DetailMsg['msgpost'] = censuredwords($DetailMsg['msgpost']);

	$tpl->box['affmessage']=getformatrecup($DetailMsg['msgpost']);

	if(!empty($DetailMsg['usersign']))
	{
		$DetailMsg['usersign']=getformatrecup($DetailMsg['usersign']);
		if($_FORUMCFG['smileinsign']=="Y")
			$DetailMsg['usersign']=getreturnsmilies($DetailMsg['usersign']);
		if($_FORUMCFG['bbcodeinsign']=="Y")
			$DetailMsg['usersign']=getreturnbbcode($DetailMsg['usersign']);
		$tpl->box['affusersign']=$tpl->gettemplate("detail","usersignature");
	}


	if (!empty($DetailMsg['guestmail']))
		$DetailMsg['postermail']=getemail($DetailMsg['guestmail']);
	elseif(!empty($DetailMsg['usermail']) && $DetailMsg['showmail']=="Y")
		$DetailMsg['postermail']=getemail($DetailMsg['usermail']);
	if(!empty($DetailMsg['postermail']))
		$tpl->box['buttonmail']=$tpl->gettemplate("detail","buttonmail");

	if (!empty($DetailMsg['guestsite']))
		$DetailMsg['postersite']=$DetailMsg['guestsite'];
	elseif(!empty($DetailMsg['usersite']) && $DetailMsg['showusersite']=="Y")
		$DetailMsg['postersite']=$DetailMsg['usersite'];
	if(!empty($DetailMsg['postersite']))
		$tpl->box['buttonsite']=$tpl->gettemplate("detail","buttonweb");
	else
		$tpl->box['buttonsite']=NULLSTR;

	/*if (!empty($DetailMsg['usericq']) && $DetailMsg['showicq']=="Y")
		$tpl->box['buttonicq']=$tpl->gettemplate("detail","buttonicq");*/

	if($DetailMsg['posterid']>0)
	{
		$tpl->box['buttonpm']=$tpl->gettemplate("detail","buttonpm");
		$tpl->box['buttonsearch']=$tpl->gettemplate("detail","buttonsearch");
	}

	if($annonce==0)
	{
		if($cit == true)
			$tpl->box['buttonquote']=$tpl->gettemplate("detail","buttonquote");
		else
			$tpl->box['buttonquote']="";

		$tpl->box['buttonedit']=$tpl->gettemplate("detail","buttonedit");
		$tpl->box['buttonip']=$tpl->gettemplate("detail","buttonip");
		$tpl->box['buttonalert']=$tpl->gettemplate("detail","buttonalert");
	}

	$chaine=$tpl->gettemplate("detail","boxmsg");

	$tpl->box['affsujetpost']=$tpl->box['affusercitation']=$tpl->box['affuserlogo']=$tpl->box['affregisterdate']=$tpl->box['affsujetpost']=$tpl->box['affusersign']=$tpl->box['isconnected']="";
	$tpl->box['buttonmail']=$tpl->box['buttonsite']=$tpl->box['buttonicq']=$tpl->box['buttonpm']=$tpl->box['buttonsearch']=$tpl->box['buttonquote']=$tpl->box['buttonedit']=$tpl->box['buttonip']=$tpl->box['buttonalert']="";
	unset($DetailMsg);

	return($chaine);
}


// *****************************************************
// *               FONCTIONS JAVASCRIPT                *
// *****************************************************

function getjsredirect($url,$tplime)
{
	global $tpl;

	$tpl->box['urlredirect']	=	$url;
	$tpl->box['timeredirect']	=	$tplime;

	$chaine						=	$tpl->gettemplate("entete","getjsredirect");
	return($chaine);

}

// ********************************************************
// *               INITIALISATION DU FORUM                *
// ********************************************************

if (get_magic_quotes_gpc() !== false) {
    ini_set('magic_quotes_gpc', 0);
    ini_set('magic_quotes_runtime', 0);
}

$tps_start 					= 		get_microtime();
$NbRequest					=		0;

// Database connection
$sql = Database_MySQLi::getInstance(array(
    'hostname' => DB_HOST,
    'username' => DB_USER,
    'password' => DB_PASSWORD,
    'database' => DB_NAME
));

$sql->set_charset('latin1');

// Sessions init
$session = new Session($sql);

$tpl 						= 		new Template;

$_FORUMCFG					=		getconfig();
addToArray($_FORUMCFG, 'mailforumname', formatstrformail(recupDBforMail($_FORUMCFG['forumname'])));
$_FORUMCFG['contactmail']	=		getemail($_FORUMCFG['contactmail']);
$_FORUMCFG['forumname']		=		getformatrecup($_FORUMCFG['forumname']);
$_FORUMCFG['sitename']		=		getformatrecup($_FORUMCFG['sitename']);

$session->setGc($_FORUMCFG['next_session_gc']);

define("NULLSTR","");

$ForumVersion				=	"0.9 beta";
$ForumDBVersion				=	"0.9 beta";
