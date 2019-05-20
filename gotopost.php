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

if (empty($_GET['id']))
	header("Location: index.php");

require("admin/functions.php");


if(!isset($_GET['action']))
{
	$id 			= 	intval($_GET['id']);

	$query			=	$sql->query("SELECT idpost,parent,idforum FROM "._PRE_."posts WHERE idpost = %d", $id)->execute();
	if($query->num_rows() > 0)
	{
		$j			=	$query->fetch_array();

		$parent		=	$j['parent'];

		$query		=	$sql->query("SELECT idpost FROM "._PRE_."posts WHERE parent=%d", $parent)->execute();
		$nb			=	$query->num_rows();

		$page		=	1;
		$compteur	=	1;

		while($i = $query->fetch_array())
		{
			if($i['idpost']==$id)
				break;
			else
			{
				if ($compteur==$_FORUMCFG['msgparpage'])
				{
					$compteur=1;
					$page++;
				}
				else
					$compteur++;

			}

		}
		header("location: detail.php?forumid=".$j['idforum']."&id=$parent&p=$page#$id");
	}
	else
		header("location: index.php");
}

if($_GET['action']=="prec" || $_GET['action']=="suiv")
{
	$id = intval ($_GET['id']);
	$forumid = intval ($_GET['forumid']);

	// #### Définition du lieu #### ///////////////////////////////////////////////
	$_SESSION['SessLieu']	=	_LOCATION_FORUM_;
	$_SESSION['SessForum']	=	$forumid;
	$_SESSION['SessTopic']	=	0;
	////////////////////////////////////////////////////////////////////////////////

	$query=$sql->query("SELECT datederrep FROM "._PRE_."topics WHERE idtopic=%d", $id)->execute();
	$i=$query->fetch_array();

	if($_GET['action']=="prec")
		$query=$sql->query("SELECT idtopic FROM "._PRE_."topics WHERE idforum=%d AND datederrep < %d ORDER BY datederrep DESC LIMIT 0,1", array($forumid, $i['datederrep']))->execute();
	elseif($_GET['action']=="suiv")
		$query=$sql->query("SELECT idtopic FROM "._PRE_."topics WHERE idforum=%d AND datederrep > %d ORDER BY datederrep LIMIT 0,1", array($forumid, $i['datederrep']))->execute();

	$nb = $query->num_rows();

	if ($nb==0) {
		require("entete.php");
		geterror("novalidlink");
	} else {
		$j=$query->fetch_array();
		header("location: detail.php?forumid=$forumid&id=".$j['idtopic']);
	}

}

if($_GET['action']=="msglus")
{
	$query = $sql->query("SELECT idtopic,idforum,nbrep FROM "._PRE_."topics ORDER BY datederrep DESC LIMIT 0,200")->execute();

	$TempMsg = array();
	$TempFrm = array();
	$TempForums = array();

	$nb = $query->num_rows();
	if($nb>0)
	{
		while($j=$query->fetch_array())
		{
			$IdString = $j['idtopic'];
			settype($IdString,"string");
			$TempMsg["$IdString"] = $j['nbrep']+1;
			$TempForums[$j['idforum']] = "true";
		}

		$query = $sql->query("SELECT forumid,forumtopic,forumposts FROM "._PRE_."forums")->execute();

		while($i = $query->fetch_array())
		{
			if($TempForums[$i['forumid']]=="true")
				$TempFrm[$i['forumid']] = $i['forumtopic']+$i['forumposts'];
		}

		sendcookie("listeforum_coolforum",cookencode($TempFrm),-1);
		sendcookie("CoolForumDetails",cookencode($TempMsg,true),-1);
	}


	header("location: index.php");
}
