<?php
$tpl->LNG['titlesection']="Emails";
$tpl->LNG['titlepart1']="Fonctions de mail";

$tpl->LNG['valid']="Soumettre les modifications";

$tpl->LNG['confmail']="Confirmation des inscriptions";
$tpl->LNG['confmail_cmt']="La confirmation des inscriptions peut se faire par mail (si votre serveur peut utiliser la fonction mail(), le cas échéant, 
				n'utilisez pas la confirmation par mail), par l'administrateur, ou automatiquement. Cette option agit également sur 
				le renvoi d'un mot de passe en cas de perte. Si vous choisissez la confirmation par mail ou par l'administrateur avec 
				renvoi du mot de passe par mail, il sera renvoyé par mail. Dans le cas de la confirmation automatique ou par 
				l'administrateur avec Question/Réponse, un système de protection par Question/Réponse est mis en place automatiquement. 
				Attention cependant si vous choisissez une de ces deux options alors que plusieurs membre se sont déjà inscris sur 
				votre forum, il leur faudra configurer leur Question/Réponse dans leur profil pour récupérer leur mot de passe.";
$tpl->LNG['confmail_chx1']="Confirmation par mail";
$tpl->LNG['confmail_chx2']="Confirmation par l'administrateur avec renvoi du mot de passe par mail";
$tpl->LNG['confmail_chx3']="Confirmation par l'administrateur avec système Question/Réponse";
$tpl->LNG['confmail_chx4']="Ne pas demander confirmation";

$tpl->LNG['notmail']="Activer la notification par email";
$tpl->LNG['notmail_cmt']="Si votre serveur peut utiliser la fonction mail(), vous pouvez permettre à vos membre de recevoir, s'il le désirent, 
				un email les prévenant d'une réponse à un sujet auquel ils ont participé et un email lorsqu'ils reçoivent un message privé.";
$tpl->LNG['notmail_chx1']="Activer la notification";
$tpl->LNG['notmail_chx2']="Désactiver la notification";

$tpl->LNG['sendpmbymail']="Autoriser la sauvegarde des messages privés par mail";
$tpl->LNG['sendpmbymail_cmt']="Cette option permet à vos membres de s'envoyer l'intégralité de leurs messages privés par mail, 
				ce qui leur permet par exemple de les consulter en étant hors ligne à partir de leur client messagerie.";
$tpl->LNG['sendpmbymail_chx1']="Activer";
$tpl->LNG['sendpmbymail_chx2']="Désactiver";

$tpl->LNG['titlepart2']="Anti-Spam";
$tpl->LNG['mask']="Masque anti-spam";
$tpl->LNG['mask_cmt']="Le masque anti-spam permet de remplacer le <b>@</b> de tous les emails affichés sur le site, évitant ainsi aux robots
		de récolter les adresses emails sur votre forum. Il est conseillé d'utiliser des caractères que l'on ne peut pas retrouver
		dans une adresse email afin de ne pas créer de conflit";

$tpl->LNG['mailfunction']="Fonction de mail à utiliser";
$tpl->LNG['mailfunction_cmt']="Selon les hébergeurs, la fonction <b>mail()</b> classique de PHP ne fonctionne pas de la même manière ou alors utilisent 
				une autre fonction. Vous pouvez régler cette fonction ici.";
				
$tpl->LNG['mails1']="mail() - Fonction normale";
$tpl->LNG['mails2']="email() - Online";
$tpl->LNG['mails3']="email() - Nexen";

$tpl->LNG['usemails']="Utiliser les fonctions d'emails";
$tpl->LNG['usemails_cmt']="Active ou non l'utilisation des fonctions d'emails du forum. Si vous désactivez cette fonction, celles qui en découlent
				comme la confirmation d'inscription par mail ou la notification seront également automatiquement désactivées.";
$tpl->LNG['usemails_chx1']="Utiliser les emails";
$tpl->LNG['usemails_chx2']="Ne pas utiliser les emails";

