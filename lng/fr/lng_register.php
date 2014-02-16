<?php
$tpl->LNG['errorpseudo1']="Veuillez saisir un pseudonyme valide";
$tpl->LNG['errorpseudo2']="Ce pseudo est d�j� utilis�, veuillez en choisir un autre";
$tpl->LNG['errormdp1']="Mot de passe non valide";
$tpl->LNG['errormdp2']="Confirmation de mot de passe non valide";
$tpl->LNG['errormail1']="L'adresse email n'est pas valide";
$tpl->LNG['errormail2']="Adresse email d�j� connue";
$tpl->LNG['errorquest']="Champ 'Question' non valide";
$tpl->LNG['errorrep']="Champ 'R�ponse' non valide";

$tpl->LNG['titlecharte']="Avant de vous inscrire, vous devez accepter la charte suivante";
$tpl->LNG['charteok']="J'accepte la charte et je veux m'inscrire!";

$tpl->LNG['cantsendmail']="Email non envoy�! Veuillez r�essayer plus tard!";
$tpl->LNG['mailsent']="Email envoy�! Veuillez l'ouvrir et confirmer votre inscription.";
$tpl->LNG['registerok']="Votre inscription est r�ussie. Vous pouvez d�s � pr�sent �diter votre profil et poster des messages sur les forums r�serv�s aux utilisateurs.";
$tpl->LNG['waitforadmin']="Votre inscription est r�ussie. Vous devez maintenant attendre que l'administrateur de ce forum la confirme avant de pouvoir vous identifier.";
$tpl->LNG['alreadylogged']="Vous �tes d�j� inscris et identifi�. Veuillez rejoindre le forum en cliquant <a href=\\\"index.php\\\" class=men>ici</a>.";

$tpl->LNG['confirmok']="Votre inscription est maintenant confirm�e.<p>Vous pouvez d�s � pr�sent �diter votre profil et poster des messages sur les forums r�serv�s aux utilisateurs.";
$tpl->LNG['confirmnotok']="Votre confirmation n'est pas valide, v�rifiez d'avoir confirm� � partir de l'adresse se trouvant dans l'email re�u.";
$tpl->LNG['alreadyconfirm']="Op�ration impossible, votre inscription a d�j� �t� confirm�e!";

$tpl->LNG['registertt']="Informations obligatoires pour inscription";
$tpl->LNG['pseudo']="Choisissez un pseudo";
$tpl->LNG['pseudo_cmt']="Ce sera votre identifiant sur le forum";
$tpl->LNG['mdp']="Choisissez un mot de passe";
$tpl->LNG['mdp_cmt']="Il vous permettra de prot�ger votre compte";
$tpl->LNG['pass']="Retapez votre mot de passe";
$tpl->LNG['pass_cmt']="Pr�viens les fautes de frappe";
$tpl->LNG['mail']="Saisissez votre email";
$tpl->LNG['mail_cmt']="Vous recevrez un email pour confimer votre inscription";
$tpl->LNG['question']="Saisissez une question";
$tpl->LNG['question_cmt']="elle vous sera pos�e si vous perdez votre mot de passe";
$tpl->LNG['reponse']="R�ponse correspondante";
$tpl->LNG['reponse_cmt']="ce que vous devrez r�pondre � votre question";


$tpl->LNG['mailmsg']="\"Votre inscription sur \".\$forumname.\" a bien �t� prise en compte.\n\n
Afin de pouvoir utiliser le forum, vous devez confirmer votre inscription en cliquant sur le lien ci-dessous:\n
\".\$_FORUMCFG['urlforum'].\"register.php?action=confirm&login=\".\$mailpseudo.\"&s=\".\$password.\"\n\n

ATTENTION! Si vous utilisez une messagerie telle que Caramail, il se peut que le lien indiqu� ci-dessus ne fonctionne pas en cliquant dessus car mal interpr�t� par la messagerie.
 Dans ce cas, ouvrez une fen�tre de votre navigateur et copiez-y l'adresse ci-dessus pour vous y rendre et confirmer votre inscription!\n\n\"";
 
$tpl->LNG['mailsujet']="\"Votre inscription sur \".\$forumname";

//////////////////////   DEFINITION DE LA CHARTE   ////////////////////////////////

$tpl->LNG['charte']="                  <b><u>Respect</u></b><p>

                  Vous acceptez d'�tre polis et courtois, vous �tes dans un espace public. Tout message vulgaire, agressif ou 
                  contenant des insultes pourra �tre imm�diatement supprim� sans pr�avis et pourra entrainer l'exclusion du 
                  forum du membre responsable.<p>
                  
                  <hr color=\\\"{%::_SKIN[textcol1]%}\\\">
                  <b><u>Contenus illicites, choquants</u></b><p>

                  Les membres s'engagent � ne pas diffuser ni permettre la diffusion de contenus (propos, liens, informations 
                  quelle que soit leur nature) : 
		  
		  <ul>
                    <li> violents, incitant � la haine raciale, religieuse ou ethnique
                    <li> � caract�re discriminatoire, x�nophobe, r�visionniste, diffamatoire ou injurieux 
                    <li> � caract�re obsc�ne, p�dophile ou pornographique 
                    <li> enfreignant les droits d'autrui, ne respectant pas les marques d�pos�es, les droits d'auteurs et droits voisins ou connexes, mena�ant, portant atteinte au droit des biens et des personnes
                    <li> relatif � des activit�s ill�gales [ piratage, chevaux de Troie, virus, drogues...]
                    <li> et plus g�n�ralement tout comportement impliquant le non respect de la loi, des bonnes mani�res et/ou des convenances.
                  </ul> 

                  Cette liste n'est pas limitative.<p> 
                  
                  <hr color=\\\"{%::_SKIN[textcol1]%}\\\">
                  <b><u>Rupture de contrat</b></u><p>

                  Le non respect de la pr�sente charte entra�ne, selon la gravit� du manquement, un rappel � 
                  l'ordre � l'adresse du membre en infraction, et/ou la suppression de tous ses messages, ainsi que son 
                  exclusion du forum, voir la r�siliation pure et simple du compte du membre.<p>

                  L'administrateur du forum pourra, en cas de manquement, en r�f�rer au fournisseur d'acc�s du membre 
                  qui pourra prendre des sanctions plus s�v�res, voir entammer des poursuites judiciaires envers le membre 
                  fautif si lui-m�me est poursuivit par un tiers suite � un message de ce membre.<p>

                  Le cas �ch�ant, des poursuites et recherches en responsabilit� suivront.<p>
                  
                  <hr color=\\\"{%::_SKIN[textcol1]%}\\\"> 
                  <b><u>L'administrateur s'engage �</u></b><p>

                  Ne pas diffuser d'informations personnelles s'il n'en a pas l'autorisation, conform�ment � la loi en vigueur 
                  sur les droits informatiques.<p>

                  Ne pas utiliser abusivement votre adresse email pour spammer votre messagerie. Il pourra cependant vous envoyer 
                  des emails dans le cadre de lettres d'informations sur le forum, vous communiquer des informations telles que 
                  votre mot-de-passe en cas de perte etc...<p>";


