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
getlangage("adm_campagne");

function ReturnMonth($mois)
{
	switch($mois)
	{
		case "01":
		  return("Janvier");
		  break;
		case "02":
		  return("Février");
		  break;
		case "03":
		  return("Mars");
		  break;
		case "04":
		  return("Avril");
		  break;
		case "05":
		  return("Mai");
		  break;
		case "06":
		  return("Juin");
		  break;
		case "07":
		  return("Juillet");
		  break;
		case "08":
		  return("Août");
		  break;
		case "09":
		  return("Septembre");
		  break;
		case "10":
		  return("Octobre");
		  break;
		case "11":
		  return("Novembre");
		  break;
		case "12":
		  return("Décembre");
		  break;
	}
}

function DayInMonth($month,$year)
{
	$daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	if ($month != 2) return $daysInMonth[$month - 1];
	return (checkdate($month, 29, $year)) ? 29 : 28;
}

if($_REQUEST['action']=="del")
{
	$query = $sql->query("DELETE FROM "._PRE_."statcamp WHERE iddate LIKE \"".$_GET['idvalue']."-%\"");
	$query = $sql->query("DELETE FROM "._PRE_."campagnes WHERE id=".$_GET['idvalue']);
	
	$_REQUEST['action'] = NULLSTR;
}

if($_REQUEST['action']=="stats")
{
	// **** Définition des variables ****
	$id=$_REQUEST['id'];
	$date=time();
	$actualmonth=strftime("%m",$date);
	$actualyear=strftime("%Y",$date);

	if(!isset($_POST['idvalue']))
	{
		$mois=$actualmonth;
		$annee=$actualyear;
		$dateencours=strftime("%Y%m",$date);
	}
	else
	{
		$mois=substr($_POST['idvalue'],-2);
		$annee=substr($_POST['idvalue'],0,4);
		$dateencours=$_POST['idvalue'];
	}
	
	$MoisLitteral=ReturnMonth($mois)." ".$annee;
	$nbdays=DayInMonth($mois,$annee);
	$defdate="$id-$dateencours";
	
	// *********************************************************************************
	$query=$sql->query("SELECT * FROM "._PRE_."statcamp WHERE iddate LIKE \"$defdate%\" ORDER BY iddate");
	$nb=mysql_num_rows($query);
	
	$recap=array();
	for($i=1;$i<($nbdays+1);$i++)
	{
		$recap[$i]['vu']="&nbsp;";
		$recap[$i]['click']="&nbsp;";
		$recap[$i]['percent']="&nbsp;";
	}
			
	$moyennestats=array(	'nbdays' => 0,
				'nbvues' => 0,
				'nbclicks' => 0,
				'percent' => 0);
	$totalstats=array(	'nbdays' => 0,
				'nbvues' => 0,
				'nbclicks' => 0,
				'percent' => 0);
		
	$maxvue=0;
	if($nb>0)
		while($j=mysql_fetch_array($query))
		{
			$day= 0 + substr($j['iddate'],-2);
			$recap[$day]['vu']=$j['vu'];
			$recap[$day]['click']=$j['clicks'];
			if($recap[$day]['vu']==0)
				$recap[$day]['percent']=0;
			else
			{
				$recap[$day]['percent']=number_format(($recap[$day]['click']*100)/$recap[$day]['vu'],2);
				$totalstats['nbdays']++;
				$totalstats['nbvues']+=$j['vu'];
				$totalstats['nbclicks']+=$j['clicks'];
			}			
			if($recap[$day]['vu']>$maxvue)
				$maxvue=$recap[$day]['vu'];
		}
	if($totalstats['nbvues']>0)
	{
		$totalstats['percent']=number_format(($totalstats['nbclicks']*100)/$totalstats['nbvues'],2);
		$moyennestats['nbvues']=number_format($totalstats['nbvues']/$totalstats['nbdays'],2);
		$moyennestats['nbclicks']=number_format($totalstats['nbclicks']/$totalstats['nbdays'],2);
		$moyennestats['percent']=number_format(($moyennestats['nbclicks']*100)/$moyennestats['nbvues'],2);
	}
	
	$rapport=$maxvue/250;
	
	// **** Affichage des colonnes de statistiques ****
	$tpl->box['colonstat'] = "";
	for($i=1;$i<($nbdays+1);$i++)
	{
		if($rapport==0)
			$result_recap=0;
		else
			$result_recap=round($recap[$i]['vu']/$rapport);
		$tpl->box['colonstat'] .= $tpl->gettemplate("adm_campagne","colonstat");	
	}
	
	// **** Affichage de la barre abscisse ****
	$tpl->box['abscisse'] = "";
	for($i=1;$i<($nbdays+1);$i++)
		$tpl->box['abscisse'] .= $tpl->gettemplate("adm_campagne","abscisse");	

	// **** Affichage des lignes de stats ****
	$tpl->box['lignestats'] = "";
	
	for($i=1;$i<($nbdays+1);$i++)
	{
		$StDay = $i;
		$StVu = $recap[$i]['vu'];
		$StClick = $recap[$i]['click'];
		$StPercent = $recap[$i]['percent'];
		
		$tpl->box['lignestats'] .= $tpl->gettemplate("adm_campagne","lignestat");
	}

	// **** Afichage des 12 derniers mois ****
	
	$tpl->box['othermonth'] = "";
	
	for($i=($actualmonth-11);$i<($actualmonth+1);$i++)
	{
		if($i<1)
		{
			$showmonth=ReturnMonth($i+12);
			$transdate=strftime("%Y%m",mktime(0,0,0,$i+12,1,$actualyear-1));
			$showyear=$actualyear-1;
		}
		else
		{
			$showmonth=ReturnMonth($i);
			$transdate=strftime("%Y%m",mktime(0,0,0,$i,1,$actualyear));
			$showyear=$actualyear;
		}
		
		$tpl->box['othermonth'] .= $tpl->gettemplate("adm_campagne","othermonth");						
	}	

	$tpl->box['admcontent']=$tpl->gettemplate("adm_campagne","statistiques");

	

}

if($_REQUEST['action']=="savecamp")
{
	// **** test du nom de campagne ****
	$testchain=preg_replace("/([\s]{1,})/","",$_POST['CampName']);
	if(strlen($testchain)==0)
		$error=$tpl->attlang("errcampname");
		
	// **** test du ratio ****
	$CampRatio = intval($_POST['CampRatio']);
	if($CampRatio<0 || $CampRatio>9)
		$error=$tpl->attlang("errcampratio");
		
	// **** test de la date de début ****
	if(preg_match("|[0-9]{2}/[0-9]{2}/[0-9]{4}|",$_POST['CampStart']) == 0)
		$error=$tpl->attlang("errcampdtestart");
		
	// **** test des conditions de fin de campagne ****
	switch ($_POST['CampType'])
	{
		case "aff":
			$CampEnd = intval($_POST['CampEnd']);
			if($CampEnd < 1)
				$error=$tpl->attlang("errcampend");
			break;
			
		case "click":
			$CampEnd = intval($_POST['CampEnd']);
			if($CampEnd < 1)
				$error=$tpl->attlang("errcampend");
			break;	
		case "date":
			if(preg_match("|[0-9]{2}/[0-9]{2}/[0-9]{4}|",$_POST['CampEnd']) == 0)
				$error=$tpl->attlang("errcampend");
			break;
		default:
			$error=$tpl->attlang("errcampend");
	}
	
	// **** test des urls de site et bannière si code régie vide ****
	if(strlen($CampRegie)==0)
	{
		if(testurl($_POST['CampUrl']))
			$error=$tpl->attlang("errcampurlsite");
		if(testurl($_POST['CampBan']))
			$error=$tpl->attlang("errcampurlban");
	}
	
	if(strlen($error)==0)
	{
		// **** formattage nom de campagne ****
		$CampName = getformatmsg($_POST['CampName']);

		// **** formattage date de début ****
		$Temp=explode("/",$_POST['CampStart']);
		$CampStart=mktime(0,0,0,$Temp[1],$Temp[0],$Temp[2]);
		
		$CampType = $_POST['CampType'];
		
		if($CampType == "date")
		{
			$Temp=explode("/",$_POST['CampEnd']);
			$CampEnd=mktime(0,0,0,$Temp[1],$Temp[0],$Temp[2]);			
		}
		
		// **** formattage code régie ****
		$CampRegie = addslashes($_POST['CampRegie']);
		
		if($_POST['idvalue']==0)
		{
			$query = $sql->query("INSERT INTO "._PRE_."campagnes (
						nom,
						url,
						banniere,
						typefin,
						dtedebut,
						fincamp,
						ratio,
						regie)
					VALUES	(
						'$CampName',
						'$CampUrl',
						'$CampBan',
						'$CampType',
						'$CampStart',
						'$CampEnd',
						'$CampRatio',
						'$CampRegie')");
		}
		else
		{
			$query = $sql->query("UPDATE "._PRE_."campagnes SET
						nom='$CampName',
						url='$CampUrl',
						banniere='$CampBan',
						typefin='$CampType',
						dtedebut='$CampStart',
						fincamp='$CampEnd',
						ratio='$CampRatio',
						regie='$CampRegie'
					WHERE id=".$_POST['idvalue']);
		}
		$_REQUEST['action'] = NULLSTR;
	}
	else
	{
		$CampName = getrecupforform($_POST['CampName']);
		$CampStart = getrecupforform($_POST['CampStart']);
		$CampEnd = getrecupforform($_POST['CampEnd']);
		$CampUrl = getrecupforform($_POST['CampUrl']);
		$CampBan = getrecupforform($_POST['CampBan']);
		$CampRegie = getrecupforform($_POST['CampRegie']);
		
		$TypeEnd = array();		
		switch ($_POST['CampType'])
		{
			case "aff":
				$TypeEnd[1] = " CHECKED";
				break;
				
			case "click":
				$TypeEnd[2] = " CHECKED";
				break;	
			case "date":
				$TypeEnd[3] = " CHECKED";
				break;
			default:
				$TypeEnd[1] = " CHECKED";
		}		
		$_REQUEST['action'] = "campform";
		
		$tpl->box['error']=$tpl->gettemplate("adm_campagne","error");
	}
				
}

if($_REQUEST['action']=="campform")
{
	$tpl->box['statslink'] = NULLSTR;
	$tpl->box['error'] = NULLSTR;
	
	if(!isset($_REQUEST['idvalue']))
	{
		$tpl->box['ttcampagne'] = $tpl->attlang("addnewcamp");
		$idvalue = 0;
	}
	else
	{
		$idvalue = $_REQUEST['idvalue'];
		
		if(strlen($error)==0)
		{
			$tpl->box['ttcampagne'] = $tpl->attlang("editcamp");
			
			$query = $sql->query("SELECT * FROM "._PRE_."campagnes WHERE id=".$_REQUEST['idvalue']);
			$Camp = mysql_fetch_array($query);
			
			$CampName = getformatrecup($Camp['nom']);
			$CampRatio = $Camp['ratio'];
			$CampStart = strftime("%d/%m/%Y",$Camp['dtedebut']);
			
			if($Camp['typefin']=="date")
				$CampEnd = strftime("%d/%m/%Y",$Camp['fincamp']);
			else
				$CampEnd = $Camp['fincamp'];

			$TypeEnd = array();		
			switch ($Camp['typefin'])
			{
				case "aff":
					$TypeEnd[1] = " CHECKED";
					break;
					
				case "click":
					$TypeEnd[2] = " CHECKED";
					break;	
				case "date":
					$TypeEnd[3] = " CHECKED";
					break;
				default:
					$TypeEnd[1] = " CHECKED";
			}
			
			$CampUrl = $Camp['url'];
			$CampBan = $Camp['banniere'];
			$CampRegie = stripslashes($Camp['regie']);				
		}
		
		$tpl->box['statslink'] = $tpl->gettemplate("adm_campagne","statsform");
	}
		
	if(empty($CampStart))
		$CampStart = strftime("%d/%m/%Y",time());
	$tpl->box['admcontent']=$tpl->gettemplate("adm_campagne","campform");
}

if(empty($_REQUEST['action']))
{
	$tpl->box['camplist'] = NULLSTR;
	
	$query=$sql->query("SELECT * FROM "._PRE_."campagnes ORDER BY id");
        $nb=mysql_num_rows($query);
        
        if($nb>0)
        {
        	while($Camp = mysql_fetch_array($query))
        	{
        		// **** formattage du nom de campagne ****
        		$CampName = getformatrecup($Camp['nom']);
        		
        		// **** Date de début ****
        		$DteDebut=strftime("%d/%m/%Y",$Camp['dtedebut']);
        		
        		// **** Définition fin de campagne ****
        		if($Camp['typefin']=="aff")
        			$DteFin=$Camp['fincamp']." <small>Aff.</small>";
        		elseif($Camp['typefin']=="click")
        			$DteFin=$Camp['fincamp']." <small>Clicks</small>";
        		else
        			$DteFin=strftime("%d/%m/%Y",$Camp['fincamp']);
        		
        		// **** Calcul affichages par jour ****	
        		$date=time();
        		$nbjours=floor(($date-$Camp['dtedebut'])/(3600*24));
        		if($nbjours>0)
        			$AffPerDay=number_format($Camp['nbaffichages']/$nbjours,2);
        		else
        			$AffPerDay=number_format(0,2);
        			
        		// **** Calcul du pourcentage ****
        		if($Camp['nbaffichages']>0)
        			$clickratio=number_format(($Camp['clicks']*100)/$Camp['nbaffichages'],2);
        		else
        			$clickratio=number_format(0,2);
        			
        		$tpl->box['camplist'] .= $tpl->gettemplate("adm_campagne","lignecamp");     		
        	}
        }
        else
        	$tpl->box['camplist'] .= $tpl->gettemplate("adm_campagne","nocamp");
        
	$tpl->box['admcontent']=$tpl->gettemplate("adm_campagne","accueilpub");	
}

$cache.=$tpl->gettemplate("adm_campagne","content");
require("bas.php");
