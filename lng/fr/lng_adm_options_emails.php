<?php
$tpl->LNG['titlesection']="Emails";
$tpl->LNG['titlepart1']="Fonctions de mail";

$tpl->LNG['valid']="Soumettre les modifications";

$tpl->LNG['confmail']="Confirmation des inscriptions";
$tpl->LNG['confmail_cmt']="La confirmation des inscriptions peut se faire par mail (si votre serveur peut utiliser la fonction mail(), le cas �ch�ant, 
				n'utilisez pas la confirmation par mail), par l'administrateur, ou automatiquement. Cette option agit �galement sur 
				le renvoi d'un mot de passe en cas de perte. Si vous choisissez la confirmation par mail ou par l'administrateur avec 
				renvoi du mot de passe par mail, il sera renvoy� par mail. Dans le cas de la confirmation automatique ou par 
				l'administrateur avec Question/R�ponse, un syst�me de protection par Question/R�ponse est mis en place automatiquement. 
				Attention cependant si vous choisissez une de ces deux options alors que plusieurs membre se sont d�j� inscris sur 
				votre forum, il leur faudra configurer leur Question/R�ponse dans leur profil pour r�cup�rer leur mot de passe.";
$tpl->LNG['confmail_chx1']="Confirmation par mail";
$tpl->LNG['confmail_chx2']="Confirmation par l'administrateur avec renvoi du mot de passe par mail";
$tpl->LNG['confmail_chx3']="Confirmation par l'administrateur avec syst�me Question/R�ponse";
$tpl->LNG['confmail_chx4']="Ne pas demander confirmation";

$tpl->LNG['notmail']="Activer la notification par email";
$tpl->LNG['notmail_cmt']="Si votre serveur peut utiliser la fonction mail(), vous pouvez permettre � vos membre de recevoir, s'il le d�sirent, 
				un email les pr�venant d'une r�ponse � un sujet auquel ils ont particip� et un email lorsqu'ils re�oivent un message priv�.";
$tpl->LNG['notmail_chx1']="Activer la notification";
$tpl->LNG['notmail_chx2']="D�sactiver la notification";

$tpl->LNG['sendpmbymail']="Autoriser la sauvegarde des messages priv�s par mail";
$tpl->LNG['sendpmbymail_cmt']="Cette option permet � vos membres de s'envoyer l'int�gralit� de leurs messages priv�s par mail, 
				ce qui leur permet par exemple de les consulter en �tant hors ligne � partir de leur client messagerie.";
$tpl->LNG['sendpmbymail_chx1']="Activer";
$tpl->LNG['sendpmbymail_chx2']="D�sactiver";

$tpl->LNG['titlepart2']="Anti-Spam";
$tpl->LNG['mask']="Masque anti-spam";
$tpl->LNG['mask_cmt']="Le masque anti-spam permet de remplacer le <b>@</b> de tous les emails affich�s sur le site, �vitant ainsi aux robots
		de r�colter les adresses emails sur votre forum. Il est conseill� d'utiliser des caract�res que l'on ne peut pas retrouver
		dans une adresse email afin de ne pas cr�er de conflit";

$tpl->LNG['mailfunction']="Fonction de mail � utiliser";
$tpl->LNG['mailfunction_cmt']="Selon les h�bergeurs, la fonction <b>mail()</b> classique de PHP ne fonctionne pas de la m�me mani�re ou alors utilisent 
				une autre fonction. Vous pouvez r�gler cette fonction ici.";
				
$tpl->LNG['mails1']="mail() - Fonction normale";
$tpl->LNG['mails2']="email() - Online";
$tpl->LNG['mails3']="email() - Nexen";

$tpl->LNG['usemails']="Utiliser les fonctions d'emails";
$tpl->LNG['usemails_cmt']="Active ou non l'utilisation des fonctions d'emails du forum. Si vous d�sactivez cette fonction, celles qui en d�coulent
				comme la confirmation d'inscription par mail ou la notification seront �galement automatiquement d�sactiv�es.";
$tpl->LNG['usemails_chx1']="Utiliser les emails";
$tpl->LNG['usemails_chx2']="Ne pas utiliser les emails";

