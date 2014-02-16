<?php
$tpl->LNG['errorpseudo1']="Veuillez saisir un pseudonyme valide";
$tpl->LNG['errorpseudo2']="Ce pseudo est déjà utilisé, veuillez en choisir un autre";
$tpl->LNG['errormdp1']="Mot de passe non valide";
$tpl->LNG['errormdp2']="Confirmation de mot de passe non valide";
$tpl->LNG['errormail1']="L'adresse email n'est pas valide";
$tpl->LNG['errormail2']="Adresse email déjà connue";
$tpl->LNG['errorquest']="Champ 'Question' non valide";
$tpl->LNG['errorrep']="Champ 'Réponse' non valide";

$tpl->LNG['titlecharte']="Avant de vous inscrire, vous devez accepter la charte suivante";
$tpl->LNG['charteok']="J'accepte la charte et je veux m'inscrire!";

$tpl->LNG['cantsendmail']="Email non envoyé! Veuillez réessayer plus tard!";
$tpl->LNG['mailsent']="Email envoyé! Veuillez l'ouvrir et confirmer votre inscription.";
$tpl->LNG['registerok']="Votre inscription est réussie. Vous pouvez dès à présent éditer votre profil et poster des messages sur les forums réservés aux utilisateurs.";
$tpl->LNG['waitforadmin']="Votre inscription est réussie. Vous devez maintenant attendre que l'administrateur de ce forum la confirme avant de pouvoir vous identifier.";
$tpl->LNG['alreadylogged']="Vous êtes déjà inscris et identifié. Veuillez rejoindre le forum en cliquant <a href=\\\"index.php\\\" class=men>ici</a>.";

$tpl->LNG['confirmok']="Votre inscription est maintenant confirmée.<p>Vous pouvez dès à présent éditer votre profil et poster des messages sur les forums réservés aux utilisateurs.";
$tpl->LNG['confirmnotok']="Votre confirmation n'est pas valide, vérifiez d'avoir confirmé à partir de l'adresse se trouvant dans l'email reçu.";
$tpl->LNG['alreadyconfirm']="Opération impossible, votre inscription a déjà été confirmée!";

$tpl->LNG['registertt']="Informations obligatoires pour inscription";
$tpl->LNG['pseudo']="Choisissez un pseudo";
$tpl->LNG['pseudo_cmt']="Ce sera votre identifiant sur le forum";
$tpl->LNG['mdp']="Choisissez un mot de passe";
$tpl->LNG['mdp_cmt']="Il vous permettra de protéger votre compte";
$tpl->LNG['pass']="Retapez votre mot de passe";
$tpl->LNG['pass_cmt']="Préviens les fautes de frappe";
$tpl->LNG['mail']="Saisissez votre email";
$tpl->LNG['mail_cmt']="Vous recevrez un email pour confimer votre inscription";
$tpl->LNG['question']="Saisissez une question";
$tpl->LNG['question_cmt']="elle vous sera posée si vous perdez votre mot de passe";
$tpl->LNG['reponse']="Réponse correspondante";
$tpl->LNG['reponse_cmt']="ce que vous devrez répondre à votre question";


$tpl->LNG['mailmsg']="\"Votre inscription sur \".\$forumname.\" a bien été prise en compte.\n\n
Afin de pouvoir utiliser le forum, vous devez confirmer votre inscription en cliquant sur le lien ci-dessous:\n
\".\$_FORUMCFG['urlforum'].\"register.php?action=confirm&login=\".\$mailpseudo.\"&s=\".\$password.\"\n\n

ATTENTION! Si vous utilisez une messagerie telle que Caramail, il se peut que le lien indiqué ci-dessus ne fonctionne pas en cliquant dessus car mal interprété par la messagerie.
 Dans ce cas, ouvrez une fenêtre de votre navigateur et copiez-y l'adresse ci-dessus pour vous y rendre et confirmer votre inscription!\n\n\"";
 
$tpl->LNG['mailsujet']="\"Votre inscription sur \".\$forumname";

//////////////////////   DEFINITION DE LA CHARTE   ////////////////////////////////

$tpl->LNG['charte']="                  <b><u>Respect</u></b><p>

                  Vous acceptez d'être polis et courtois, vous êtes dans un espace public. Tout message vulgaire, agressif ou 
                  contenant des insultes pourra être immédiatement supprimé sans préavis et pourra entrainer l'exclusion du 
                  forum du membre responsable.<p>
                  
                  <hr color=\\\"{%::_SKIN[textcol1]%}\\\">
                  <b><u>Contenus illicites, choquants</u></b><p>

                  Les membres s'engagent à ne pas diffuser ni permettre la diffusion de contenus (propos, liens, informations 
                  quelle que soit leur nature) : 
		  
		  <ul>
                    <li> violents, incitant à la haine raciale, religieuse ou ethnique
                    <li> à caractère discriminatoire, xénophobe, révisionniste, diffamatoire ou injurieux 
                    <li> à caractère obscène, pédophile ou pornographique 
                    <li> enfreignant les droits d'autrui, ne respectant pas les marques déposées, les droits d'auteurs et droits voisins ou connexes, menaçant, portant atteinte au droit des biens et des personnes
                    <li> relatif à des activités illégales [ piratage, chevaux de Troie, virus, drogues...]
                    <li> et plus généralement tout comportement impliquant le non respect de la loi, des bonnes manières et/ou des convenances.
                  </ul> 

                  Cette liste n'est pas limitative.<p> 
                  
                  <hr color=\\\"{%::_SKIN[textcol1]%}\\\">
                  <b><u>Rupture de contrat</b></u><p>

                  Le non respect de la présente charte entraîne, selon la gravité du manquement, un rappel à 
                  l'ordre à l'adresse du membre en infraction, et/ou la suppression de tous ses messages, ainsi que son 
                  exclusion du forum, voir la résiliation pure et simple du compte du membre.<p>

                  L'administrateur du forum pourra, en cas de manquement, en référer au fournisseur d'accés du membre 
                  qui pourra prendre des sanctions plus sévères, voir entammer des poursuites judiciaires envers le membre 
                  fautif si lui-même est poursuivit par un tiers suite à un message de ce membre.<p>

                  Le cas échéant, des poursuites et recherches en responsabilité suivront.<p>
                  
                  <hr color=\\\"{%::_SKIN[textcol1]%}\\\"> 
                  <b><u>L'administrateur s'engage à</u></b><p>

                  Ne pas diffuser d'informations personnelles s'il n'en a pas l'autorisation, conformément à la loi en vigueur 
                  sur les droits informatiques.<p>

                  Ne pas utiliser abusivement votre adresse email pour spammer votre messagerie. Il pourra cependant vous envoyer 
                  des emails dans le cadre de lettres d'informations sur le forum, vous communiquer des informations telles que 
                  votre mot-de-passe en cas de perte etc...<p>";


