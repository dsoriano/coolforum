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
	
	$query = $sql->query("SELECT ordersmile FROM "._PRE_."smileys WHERE idsmile='".$Id."'");
	$test = mysql_num_rows($query);
	
	if($test>0 && $To>0)
	{
		list($OrderNow) = mysql_fetch_array($query);
		
		$query = $sql->query("UPDATE "._PRE_."smileys SET ordersmile='".$OrderNow."' WHERE ordersmile='".$To."'");
		$nb = mysql_affected_rows();
		
		if($nb>0)
			$query = $sql->query("UPDATE "._PRE_."smileys SET ordersmile='".$To."' WHERE idsmile='".$Id."'");
	}
	
	$_REQUEST['action'] = NULLSTR;;
}


if($_REQUEST['action']=="delete")
{
	$id		=	intval($_POST['id']);
	$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE idsmile='$id'");
	$i=mysql_fetch_array($query);



	$query=$sql->query("DELETE FROM "._PRE_."smileys WHERE idsmile='$id'");
	if($query)
	{
		$tpl->box['isupdated'] = $tpl->attlang("smdeleted");
		
		$query=$sql->query("UPDATE "._PRE_."smileys SET ordersmile=ordersmile-1 WHERE ordersmile>'".$i['ordersmile']."'");
		
		$query=$sql->query("UPDATE "._PRE_."posts SET msg = REPLACE (msg,' ".$i['codesmile']." ',' ') WHERE msg LIKE \"%".$i['codesmile']."%\" AND smiles='Y'");
		$nbmsg = mysql_affected_rows();
	
		$query=$sql->query("UPDATE "._PRE_."privatemsg SET msg = REPLACE (msg,' ".$i['codesmile']." ',' ') WHERE msg LIKE \"%".$i['codesmile']."%\" AND smiles='Y'");
		$nbpm = mysql_affected_rows();
	
		$query=$sql->query("UPDATE "._PRE_."user SET usersign = REPLACE (usersign,' ".$i['codesmile']." ',' ') WHERE usersign LIKE \"%".$i['codesmile']."%\"");
		$nbcit = mysql_affected_rows();
		
		$query=$sql->query("OPTIMIZE TABLE "._PRE_."smileys");
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
		
		$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE (imgsmile='$img' OR codesmile='$code') AND idsmile!='$id'");
		$nb=mysql_num_rows($query);
		
		if($nb>0)
		{
			$error2 = $tpl->attlang("err2");
			$_REQUEST['action'] = NULLSTR;
		}
		else
		{
			$query=$sql->query("UPDATE "._PRE_."smileys SET imgsmile='$img', codesmile='$code' WHERE idsmile='$id'");
			if(!$query)
				$tpl->box['isupdated'] = $tpl->attlang("err4");
			else
			{
				$tpl->box['isupdated'] = $tpl->attlang("updok");
				if($_POST['oldcode']!=$_POST['code'])
				{
					$query=$sql->query("UPDATE "._PRE_."posts SET msg = REPLACE (msg,' $oldcode ',' $code ') WHERE msg LIKE \"%$oldcode%\" AND smiles='Y'");
					$nbmsg = mysql_affected_rows();
		
					$query=$sql->query("UPDATE "._PRE_."privatemsg SET msg = REPLACE (msg,' $oldcode ',' $code ') WHERE msg LIKE \"%$oldcode%\" AND smiles='Y'");
					$nbpm = mysql_affected_rows();

					
					$query=$sql->query("UPDATE "._PRE_."user SET usersign = REPLACE (usersign,' $oldcode ',' $code ') WHERE usersign LIKE \"%$oldcode%\"");
					$nbcit = mysql_affected_rows();
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
	
	$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE idsmile='$id'");
	if(!$query)
	{
		$error2 = $tpl->attlang("err3");
		$_REQUEST['action'] = NULLSTR;
	}
	else
	{
		$Sm=mysql_fetch_array($query);
		$tpl->box['admcontent']=$tpl->gettemplate("adm_smileys","modify");	
	}
}

if($_REQUEST['action']=="addsmiley")
{
	if(strlen($_POST['img'])>0 && strlen($_POST['code'])>0)
	{
		$img = getformathtml($_POST['img']);
		$code = getformathtml($_POST['code']);
		
		$query=$sql->query("SELECT * FROM "._PRE_."smileys WHERE imgsmile='$img' OR codesmile='$code'");
		$nb=mysql_num_rows($query);
		
		if($nb>0)
			$error1 = $tpl->attlang("err2");
		else
		{
			$code = getformatmsg($_POST['code']);
			
			$query = $sql->query("SELECT ordersmile FROM "._PRE_."smileys ORDER BY ordersmile DESC LIMIT 0,1");
			$nb = mysql_num_rows($query);
			if($nb>0)	list($Last) = mysql_fetch_array($query);
			else		$Last = 0;
			
			$Last++;
			
			$query=$sql->query("INSERT INTO "._PRE_."smileys (imgsmile,codesmile,ordersmile) VALUES ('$img','$code',$Last)");
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
	
	$query = $sql->query("SELECT * FROM "._PRE_."smileys ORDER BY ordersmile");
	while($Sml = mysql_fetch_array($query))
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

