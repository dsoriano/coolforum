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

if(preg_match('|profile.php|',$_SERVER['PHP_SELF']) == 0)
{
	header('location: profile.php');
	exit;
}

getlangage("profile_pm");

// ###### Navigation ######
$tpl->treenavs=$tpl->gettemplate("treenav","treeprofil");
$cache.=$tpl->gettemplate("treenav","hierarchy");

if($_REQUEST['action']=="delmsg")
{
	if (count($_REQUEST['del']) === 0) {
        $tpl->box['infomsg'] = $tpl->attlang("ifnopmtodel");
    } else {
		$ok = true;
		foreach ($_REQUEST['del'] as $value) {
			$query = $sql->query("DELETE FROM "._PRE_."privatemsg WHERE id=%d AND iddest=%d", array($value, $_USER['userid']))->execute();
			if (!$query) {
                $ok = false;
            }
		}

		if($ok)
			$tpl->box['infomsg']=$tpl->attlang("ifpmdeleted");
		else
			$tpl->box['infomsg']=$tpl->attlang("ifpmnotdel");
	}
	updatepmstats($_USER['userid']);

	$tpl->box['profilcontent']=$tpl->gettemplate("profil_pm","infobox");
	$tpl->box['profilcontent'].=getjsredirect("profile.php?p=pm",3000);
}

if($_REQUEST['action']=="delallmsg")
{
	$query = $sql->query("DELETE FROM "._PRE_."privatemsg WHERE iddest=%d", $_USER['userid'])->execute();

	if($query)	$tpl->box['infomsg']=$tpl->attlang("ifpmdeleted");
	else		$tpl->box['infomsg']=$tpl->attlang("ifpmnotdel");

	updatepmstats($_USER['userid']);

	$tpl->box['profilcontent']=$tpl->gettemplate("profil_pm","infobox");
	$tpl->box['profilcontent'].=getjsredirect("profile.php?p=pm",3000);
}


if($_REQUEST['action']=="readmsg")
{
	$Id = intval($_REQUEST['id']);

	$query		=	$sql->query("SELECT * FROM "._PRE_."privatemsg WHERE id=%d", $Id)->execute();
	$tpl->tmp	=	$query->fetch_array();
	if($tpl->tmp['iddest']!= $_USER['userid'])
		geterror("notyours");

	$query		=	$sql->query("UPDATE "._PRE_."privatemsg SET vu=1 WHERE id=%d", $Id)->execute();
	updatepmstats($_USER['userid']);

	$tpl->tmp['sujet']	=	getformatrecup($tpl->tmp['sujet']);
	$tpl->tmp['date']	=	getlocaltime($tpl->tmp['date']);
	$tpl->tmp['msg']	=	getformatrecup($tpl->tmp['msg']);

	if($tpl->tmp['smiles']=="Y")
	{
		$table_smileys=getloadsmileys();
		$tpl->tmp['msg']=getreturnsmilies($tpl->tmp['msg']);
	}

	if($tpl->tmp['bbcode']=="Y")
	{
		InitBBcode();
		$tpl->tmp['msg']=getreturnbbcode($tpl->tmp['msg']);
	}

	$tpl->box['profilcontent']=$tpl->gettemplate("profil_pm","readpmbox");

}

if($_REQUEST['action']=="sendmsg")
{
	$error="";

	//**** test du sujet ****
	$testchain=preg_replace("/([\s]{1,})/","",$_POST['sujet']);
	if(strlen($testchain)==0)
		$error=$tpl->attlang("errorsujet");

	//**** test du message ****
	$testchain=preg_replace("/([\s]{1,})/","",$_POST['msg']);
	if(strlen($testchain)==0)
		$error=$tpl->attlang("errormsg");

	//**** test du destinataire ****
	$testdest=intval($_POST['dest']);
	if($testdest < 1)
		$error=$tpl->attlang("errordest");

	if(strlen($error)==0)
	{
		//**** formattage des données ****
		$date	=	time();
		$dest	=	intval($_POST['dest']);
		$sujet	=	getformatmsg($_POST['sujet'],false);

		if($_USER['wysiwyg']=="Y")
		{
			$msg		=	convert_html_to_bbcode($_POST['msg']);
			$msg		=	getformatmsghtml($msg);
		}
		else
			$msg	=	getformatmsg($_POST['msg']);

		$msg		=	test_max_length($msg,$_USER['Max_Pm']);
		$sujet		=	test_max_length($sujet,$_FORUMCFG['limittopiclength']);

		if(isset($_POST['smilecode']) && $_POST['smilecode']=="non")	$smiles	=	"N";
			else			$smiles	=	"Y";

		if(isset($_POST['bbcode']) && $_POST['bbcode']=="non")	$nobb	=	"N";
			else			$nobb	=	"Y";

		$username = getformatdbtodb($_USER['username']);

		$query = $sql->query("INSERT INTO "._PRE_."privatemsg (iddest,idexp,date,pseudo,sujet,msg,smiles,bbcode) VALUES (%d,%d,'%s','%s','%s','%s','%s','%s')", array($dest, $_USER['userid'], $date, $username, $sujet, $msg, $smiles, $nobb))->execute();
		updatepmstats($dest);

		if($query)
		{
			if($_FORUMCFG['mailnotify']=="Y")
			{
				$quest = $sql->query("SELECT usermail, notifypm FROM "._PRE_."user WHERE userid=%d", $dest)->execute();
				$zz=$quest->fetch_array();
				if($zz['notifypm']=="Y")
				{
					$forumname	=	$_FORUMCFG['mailforumname'];
					$username	=	formatstrformail($_USER['username']);

					eval("\$subject = ".$tpl->attlang("mailsujet").";");
					eval("\$mesg = ".$tpl->attlang("mailmsg").";");

					@sendmail($zz['usermail'],$subject,$mesg);
				}
			}
			$tpl->box['infomsg']	=	$tpl->attlang("msgsent");
		}
		else
			$tpl->box['infomsg']	=	$tpl->attlang("msgnotsent");

		$tpl->box['profilcontent']	=	$tpl->gettemplate("profil_pm","infobox");
		$tpl->box['profilcontent']       .=	getjsredirect("profile.php?p=pm",3000);
	}
	else
	{
		$tpl->box['errorbox']	=	$tpl->gettemplate("profil_pm","errorbox");
		$pm=$_POST;
		$_REQUEST['action']	=	"writemsg";
	}

}
if($_REQUEST['action']=="writemsg")
{
	getlangage("writebox");

	$tpl->box['smilechecked'] 			=		NULLSTR;
	$tpl->box['bbcodechecked'] 			=		NULLSTR;
	$tpl->box['mailnotify'] 			=		NULLSTR;
	$tpl->box['sondage'] 				=		NULLSTR;
	$tpl->box['errorbox'] 				=		NULLSTR;

	$table_smileys						=		getloadsmileys();
	$posteurpseudo						=		getformatrecup($_USER['username']);

	if(isset($_POST['idpm']))
	{
		$idpm							=		intval($_POST['idpm']);
		$getpm							=		$sql->query("SELECT * FROM "._PRE_."privatemsg WHERE id=%d", $idpm)->execute();
		$pm								=		$getpm->fetch_array();

		//**** formattage du sujet ****
		$prefixsujet					=		$tpl->attlang("prefsujet");
		$tpl->box['subject']			=		"";

		if(substr($pm['sujet'],0,strlen($prefixsujet))!=$prefixsujet)
			$tpl->box['subject']		.=		$prefixsujet;

		$tpl->box['subject']			.=		getformatrecup($pm['sujet']);

		//**** formattage du message ****
		$pm['msg']						=		preg_replace("/\[quote\](.*?)\[\/quote\]/si","",$pm['msg']);
		$tpl->box['quotemsg']			=		"[quote]".getformatrecup($pm['msg'])."[/quote]";
	}
	elseif(!empty($pm) && count($pm)>0)
	{
		$tpl->box['subject']			=		getrecupforform($pm['sujet']);
		$tpl->box['quotemsg']			=		getrecupforform($pm['msg']);
	}
	else
		$tpl->box['subject']			=		"";

	$LimiteLength 						= 		$_USER['Max_Pm'];

	if($LimiteLength > 0)
		$tpl->box['limitmsgdef']		=		$LimiteLength;
	else
		$tpl->box['limitmsgdef']		=		$tpl->attlang("unlimited");
	//$tpl->box['profilcontent']	=	$tpl->gettemplate("javascript","compter");

	//**** sélection des pseudos ****
	if(isset($_GET['pseudosearch']) && strlen($_GET['pseudosearch'])>0)
	{
		$pseudosearch	=	getformatmsg($_GET['pseudosearch'],false);
		$query		=	$sql->query("SELECT userid,login FROM "._PRE_."user WHERE login LIKE \"%%%s%%\" AND userstatus > 0 ORDER BY login", $pseudosearch)->execute();
	}
	else
		$query		=	$sql->query("SELECT userid,login FROM "._PRE_."user WHERE userstatus > 0 ORDER BY login")->execute();

	if($query->num_rows()>0)
	{
		$tpl->box['loginlist']="";
		while($j=$query->fetch_array())
		{
			$selected="";
			if((isset($pm['idexp']) && $pm['idexp']==$j['userid']) || (isset($_GET['idexp']) && $_GET['idexp']==$j['userid']) || (isset($_POST['dest']) && $_POST['dest']==$j['userid']))
				$selected=" SELECTED";
			$j['login'] = getformatrecup($j['login']);
			$tpl->box['loginlist'].=$tpl->gettemplate("profil_pm","loginoption");
		}
		$tpl->box['loginform']=$tpl->gettemplate("profil_pm","loginselect");
	}
	else
		$tpl->box['loginform']=$tpl->attlang("usernotfound");

	//$tpl->box['limitmsgdef'] = $_USER['Grp_Pm'];

	if($_USER['wysiwyg'] == "Y")
		$tpl->box['javascript']=$tpl->gettemplate("writebox_wysiwyg","wysiwygjs");
	else
		$tpl->box['javascript']=$tpl->gettemplate("entete","getjscompter");

	$tpl->box['boxwritepage']=affwritebox("N");

	$tpl->box['profilcontent']=$tpl->gettemplate("profil_pm","sendmessagebox");
}

if($_REQUEST['action']=="sendpmbymail" && $_FORUMCFG['sendpmbymail']=="Y" && $_FORUMCFG['usemails']=="Y")
{
	$query		= $sql->query("SELECT usermail FROM "._PRE_."user WHERE userid=%d", $_USER['userid'])->execute();
	list($usermail)	= $query->fetch_array();

	$forumname	=	formatstrformail(stripslashes(recupDBforMail($_FORUMCFG['forumname'])));

	$query = $sql->query("SELECT * FROM "._PRE_."privatemsg WHERE iddest=%d", $_USER['userid'])->execute();

	$ok = true;
	while($Pm=$query->fetch_array())
	{
		$Pm['sujet']	= formatstrformail(recupDBforMail($Pm['sujet']));
		$Pm['msg']	= strip_tags(formatstrformail(recupDBforMail($Pm['msg'])));
		$Pm['pseudo']	= formatstrformail(recupDBforMail($Pm['pseudo']));
		$Pm['date']	= getlocaltime($Pm['date']);

		eval("\$subject = ".$tpl->attlang("archivemailsujet").";");
		eval("\$mesg = ".$tpl->attlang("archivemailmsg").";");

		if(!@sendmail($usermail,$subject,$mesg))
			$ok = false;
	}

	if($ok==true)	$tpl->box['infomsg']=$tpl->attlang("mailsent");
	else		$tpl->box['infomsg']=$tpl->attlang("mailnotsent");

	$tpl->box['profilcontent']=$tpl->gettemplate("profil_pm","infobox");
	$tpl->box['profilcontent'].=getjsredirect("profile.php?p=pm",3000);
}

if(empty($_REQUEST['action']))
{
	if($_USER['nbpmvu']==0)
		$tpl->box['nbnewpm']=$tpl->attlang("nonewpm");
	elseif($_USER['nbpmvu']==1)
		$tpl->box['nbnewpm']=$tpl->attlang("onenewpm");
	else
		eval("\$tpl->box['nbnewpm']=\"".$tpl->attlang("multinewpm")."\";");

	$pm_db=$sql->query("SELECT * FROM "._PRE_."privatemsg WHERE iddest=%d ORDER BY date DESC", $_USER['userid'])->execute();
	$nb=$pm_db->num_rows();

	if($nb==0)
		$tpl->box['pmcontent']=$tpl->gettemplate("profil_pm","nonewpm");
	else
	{
		$tpl->box['pmcontent']="";
		while($Respm=$pm_db->fetch_array())
		{
			if($Respm['vu']==0)
				$Respm['imgpm']="nonlu";
			elseif($Respm['vu']==1)
				$Respm['imgpm']="lu";
			$Respm['date']=getlocaltime($Respm['date']);
			$Respm['sujet']=getformatrecup($Respm['sujet']);
			$tpl->box['pmcontent'].=$tpl->gettemplate("profil_pm","viewpms");
		}
	}

	if($_FORUMCFG['sendpmbymail']=="Y" && $_FORUMCFG['usemails']=="Y")
		$tpl->box['sendpmbymail']=$tpl->gettemplate("profil_pm","sendpmbymail");
    else
        $tpl->box['sendpmbymail']=NULLSTR;

	$tpl->box['profilcontent']=$tpl->gettemplate("profil_pm","interfaceaccueil");
}
