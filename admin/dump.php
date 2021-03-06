<?php
//*********************************************************************************
//*                                                                               *
//*                  CoolForum v.0.8.5 Beta : Forum de discussion                   *
//*              Copyright �2001-2014 SORIANO Denis alias Cool Coyote             *
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
//*       Forum Cr�� par SORIANO Denis (Cool Coyote)                              *
//*       contact : coyote@coolcoyote.net                                         *
//*       site web et t�l�chargement : http://www.coolforum.net                   *
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
$chaine		.=	"# *   Compatibilit� assur�e  *\n";
$chaine		.=	"# *     phpMyAdmin 2.2.x     *\n";
$chaine		.=	"# *                          *\n";
$chaine		.=	"# *                          *\n";
$chaine		.=	"# * Dump r�alis� le :        *\n";
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
			
			$query		=	$sql->query("SHOW FIELDS FROM ".$i[0])->execute();
			
			// D�finition des colonnes
			$field		=	"";
			while($j = $query->fetch_array())
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

			// D�finition des index
			$key			=	array();
			$cpt			=	0;
			$query			=	$sql->query("SHOW INDEX FROM ".$i[0])->execute();

			while($j = $query->fetch_array())
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
			
			$query=$sql->query("SELECT * FROM ".$i[0])->execute();
			$tot_request=$query->num_rows();
			
			if($tot_request>0)
			{
				while($req=$query->fetch_row())
				{
				   for($z=0;$z<count($req);$z++)
				   {
                       $type = $query->fetch_field_direct($z)->type;
				   	
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
