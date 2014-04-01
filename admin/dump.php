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

    header("Content-disposition: filename=".date("d_m_Y").".sql");
    header("Content-type: application/octetstream");
    header("Pragma: no-cache");
    header("Expires: 0");
   
$table		= 	$sql->query("SHOW TABLES")->execute();
$nb_table	=	$table->num_rows();

$chaine		=	"";

$chaine		.=	"# ****************************\n";
$chaine		.=	"# *   CoolForum Dump Table   *\n";
$chaine		.=	"# *   Compatibilité assurée  *\n";
$chaine		.=	"# *     phpMyAdmin 2.2.x     *\n";
$chaine		.=	"# *                          *\n";
$chaine		.=	"# *                          *\n";
$chaine		.=	"# * Dump réalisé le :        *\n";
$chaine		.=	"# * ".strftime("%d/%m/%Y",time())."               *\n";
$chaine		.=	"# ****************************\n\n";

if($nb_table>0)
{
	while($i = $table->fetch_row())
	{
		if(preg_match("|^"._PRE_."|",$i[0]) > 0)
		{
			$chaine		.=	"# ---------- TABLE ".$i[0]." --------------\n";
			
			$chaine 	.=	"DROP TABLE ".$i[0].";\n";
			$chaine		.=	"CREATE TABLE ".$i[0]." (";
			
			$query		=	mysql_query("SHOW FIELDS FROM ".$i[0]);
			
			// Définition des colonnes
			$field		=	"";
			while($j = mysql_fetch_array($query))
			{
				$field 		.= 	$j['Field']." ".$j['Type'];
				
				if(strlen($j['Default'])>0)
					$field 	.= 	" DEFAULT '".$j['Default']."'";
					
				if(strlen($j['Null'])==0)
					$field 	.= 	" NOT NULL";
					
				if(!empty($j['Extra']))
					$field 	.=	" ".$j['Extra'];
					
				$field 		.=	", ";
			}

			// Définition des index
			$key			=	array();
			$cpt			=	0;
			$query			=	mysql_query("SHOW INDEX FROM ".$i[0]);

			while($j = mysql_fetch_array($query))
			{
				if($j['Index_type'] == "BTREE")
					$Index_type			=	"";
				else
					$Index_type			=	$j['Index_type']." ";
				
				if($j['Key_name']=="PRIMARY")
				{
					$key[$j['Key_name']]['type']	=	$Index_type."PRIMARY KEY";
					$key[$j['Key_name']]['cols'][]	=	$j['Column_name'];
				}
				elseif($j['Non_unique']==0)
				{
					$key[$j['Key_name']]['type']	=	$Index_type."UNIQUE KEY ".$j['Key_name'];
					$key[$j['Key_name']]['cols'][]	=	$j['Column_name'];
				}
				else
				{
					$key[$j['Key_name']]['type']	=	$Index_type."KEY ".$j['Key_name'];
					$key[$j['Key_name']]['cols'][]	=	$j['Column_name'];
				}
				
				$cpt++;
			}
			
			$key_line	=	"";
			
			foreach($key AS $key_type => $cols)
				$key_line	.=	" ".$cols['type']." (".implode(", ",$cols['cols'])."),";

			$chaine.=substr($field,0,-2).substr($key_line,0,-1).");\n\n";
			
			//
			echo($chaine);
			$chaine = "";
			//
			
			$query=mysql_query("SELECT * FROM ".$i[0]);
			$tot_request=mysql_num_rows($query);
			
			if($tot_request>0)
			{
				while($req=mysql_fetch_row($query))
				{
				   for($z=0;$z<count($req);$z++)
				   {
				   	$type = mysql_field_type($query, $z);
				   	
				   	if ($type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'int' || $type == 'bigint'  ||$type == 'timestamp')
				   		$req[$z]="'".$req[$z]."'";
				   		
				   	else
				   	{
						if(get_magic_quotes_runtime()==0)
							$req[$z]="'".addslashes($req[$z])."'";
					   	$req[$z]=str_replace("\n","\\n",$req[$z]);
					   	$req[$z]=str_replace("\r","\\r",$req[$z]);
						$req[$z]=str_replace("\t","\\t",$req[$z]);
					}
				   }
				   echo("INSERT INTO ".$i[0]." VALUES (".implode(", ",$req).");\n");
				}
			}
			echo("\n");
			
			unset($field);
			unset($key);
		}
	}
}
