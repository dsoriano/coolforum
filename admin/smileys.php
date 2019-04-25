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

require("entete.php");
getlangage("adm_smileys");

$error1 = NULLSTR;
$error2 = NULLSTR;
$tpl->box['error1'] = NULLSTR;
$tpl->box['error2'] = NULLSTR;

if($_REQUEST['action']=="changeorder")
{
	$Id = intval($_POST['id']);
	$To = intval($_POST['to']);

	$query = $sql->query("SELECT ordersmile FROM "._PRE_."smileys WHERE idsmile=%d", $Id)->execute();
	$test = $query->num_rows();

	if($test>0 && $To>0)
	{
		list($OrderNow) = $query->fetch_array();

		$query = $sql->query("UPDATE "._PRE_."smileys SET ordersmile=%d WHERE ordersmile=%d", array($OrderNow, $To))->execute();
		$nb = $sql->affectedRows();

		if($nb>0)
			$query = $sql->query("UPDATE "._PRE_."smileys SET ordersmile=%d WHERE idsmile=%d", array($To, $Id))->execute();
	}

	$_REQUEST['action'] = NULLSTR;;
}


if($_REQUEST['action']=="delete")
{
	$id		=	intval($_POST['id']);
	$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE idsmile=%d", $id)->execute();
	$i=$query->fetch_array();



	$query=$sql->query("DELETE FROM "._PRE_."smileys WHERE idsmile=%d", $id)->execute();
	if($query)
	{
		$tpl->box['isupdated'] = $tpl->attlang("smdeleted");

		$query=$sql->query("UPDATE "._PRE_."smileys SET ordersmile=ordersmile-1 WHERE ordersmile>%d", $i['ordersmile'])->execute();

		$query=$sql->query("UPDATE "._PRE_."posts SET msg = REPLACE (msg,' %s ',' ') WHERE msg LIKE \"%%%s%%\" AND smiles='Y'", array($i['codesmile'], $i['codesmile']))->execute();
		$nbmsg = $sql->affectedRows();

		$query=$sql->query("UPDATE "._PRE_."privatemsg SET msg = REPLACE (msg,' %s ',' ') WHERE msg LIKE \"%%%s%%\" AND smiles='Y'", array($i['codesmile'], $i['codesmile']))->execute();
		$nbpm = $sql->affectedRows();

		$query=$sql->query("UPDATE "._PRE_."user SET usersign = REPLACE (usersign,' %s ',' ') WHERE usersign LIKE \"%%%s%%\"", array($i['codesmile'], $i['codesmile']))->execute();
		$nbcit = $sql->affectedRows();

		$query=$sql->query("OPTIMIZE TABLE "._PRE_."smileys")->execute();
	}
	else
	{
		$tpl->box['isupdated'] = $tpl->attlang("err5");
		$nbmsg = 0;
		$nbpm = 0;
		$nbcit = 0;
	}

	$tpl->box['admcontent']=$tpl->gettemplate("adm_smileys","update");

}

if($_REQUEST['action']=="avert")
	$tpl->box['admcontent']=$tpl->gettemplate("adm_smileys","avert");

if($_REQUEST['action']=="update")
{
	if(strlen($_POST['img'])>0 && strlen($_POST['code'])>0)
	{
		$id		=	intval($_POST['id']);
		$img	=	getformathtml($_POST['img']);
		$code	=	getformathtml($_POST['code']);
		$oldcode=   getformathtml($_POST['oldcode']);

		$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE (imgsmile='%s' OR codesmile='%s') AND idsmile!=%d", array($img, $code, $id))->execute();
		$nb=$query->num_rows();

		if($nb>0)
		{
			$error2 = $tpl->attlang("err2");
			$_REQUEST['action'] = NULLSTR;
		}
		else
		{
			$query=$sql->query("UPDATE "._PRE_."smileys SET imgsmile='%s', codesmile='%s' WHERE idsmile=%d", array($img, $code, $id))->execute();
			if(!$query)
				$tpl->box['isupdated'] = $tpl->attlang("err4");
			else
			{
				$tpl->box['isupdated'] = $tpl->attlang("updok");
				if($_POST['oldcode']!=$_POST['code'])
				{
					$query=$sql->query("UPDATE "._PRE_."posts SET msg = REPLACE (msg,' %s ',' %s ') WHERE msg LIKE \"%%%s%%\" AND smiles='Y'", array($oldcode, $code, $oldcode))->execute();
					$nbmsg = $sql->affectedRows();

					$query=$sql->query("UPDATE "._PRE_."privatemsg SET msg = REPLACE (msg,' %s ',' %s ') WHERE msg LIKE \"%%%s%%\" AND smiles='Y'", array($oldcode, $code, $oldcode))->execute();
					$nbpm = $sql->affectedRows();


					$query=$sql->query("UPDATE "._PRE_."user SET usersign = REPLACE (usersign,' %s ',' %s ') WHERE usersign LIKE \"%%%s%%\"", array($oldcode, $code, $oldcode))->execute();
					$nbcit = $sql->affectedRows();
				}
				else
				{
					$nbmsg = 0;
					$nbpm = 0;
					$nbcit = 0;
				}
				$tpl->box['admcontent']=$tpl->gettemplate("adm_smileys","update");
			}
		}
	}
	else
	{
		$error2 = $tpl->attlang("err1");
		$_REQUEST['action'] = NULLSTR;
	}

}

if($_REQUEST['action']=="modify")
{
	$id		=	intval($_GET['id']);

	$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE idsmile=%d", $id)->execute();
	if(!$query)
	{
		$error2 = $tpl->attlang("err3");
		$_REQUEST['action'] = NULLSTR;
	}
	else
	{
		$Sm=$query->fetch_array();
		$tpl->box['admcontent']=$tpl->gettemplate("adm_smileys","modify");
	}
}

if($_REQUEST['action']=="addsmiley")
{
	if(strlen($_POST['img'])>0 && strlen($_POST['code'])>0)
	{
		$img = getformathtml($_POST['img']);
		$code = getformathtml($_POST['code']);

		$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE imgsmile='%s' OR codesmile='%s'", array($img, $code))->execute();
		$nb=$query->num_rows();

		if($nb>0)
			$error1 = $tpl->attlang("err2");
		else
		{
			$code = getformatmsg($_POST['code']);

			$query = $sql->query("SELECT ordersmile FROM "._PRE_."smileys ORDER BY ordersmile DESC LIMIT 0,1")->execute();
			$nb = $query->num_rows();
			if($nb>0)	list($Last) = $query->fetch_array();
			else		$Last = 0;

			$Last++;

			$query=$sql->query("INSERT INTO "._PRE_."smileys (imgsmile,codesmile,ordersmile) VALUES ('%s','%s',%d)", array($img, $code, $Last))->execute();
		}
	}
	else
		$error1 = $tpl->attlang("err1");

	$_REQUEST['action'] = NULLSTR;
}

if(empty($_REQUEST['action']))
{
	if(strlen($error1)>0)
	{
		$error = $error1;
		$tpl->box['error1'] = $tpl->gettemplate("adm_smileys","error");
	}

	if(strlen($error2)>0)
	{
		$error = $error2;
		$tpl->box['error2'] = $tpl->gettemplate("adm_smileys","error");
	}

	$tpl->box['smilelist']="";

	$query = $sql->query("SELECT * FROM "._PRE_."smileys ORDER BY ordersmile")->execute();
	while($Sml = $query->fetch_array())
	{
		$checked	=	array(	NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR,
								NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, NULLSTR, );
		$checked[$Sml['ordersmile']] = " SELECTED";
		$tpl->box['changeorder']=$tpl->gettemplate("adm_smileys","changeorder");
		$tpl->box['smilelist'].=$tpl->gettemplate("adm_smileys","smileligne");
	}

	$tpl->box['admcontent']=$tpl->gettemplate("adm_smileys","smileyslist");


}

$cache.=$tpl->gettemplate("adm_smileys","content");
require("bas.php");

