<?php
$tpl->LNG['addmsgtitle']="Ajouter Un Message";
$tpl->LNG['pstr_pseudo']="Pseudo";
$tpl->LNG['pstr_sujet']="Sujet";
$tpl->LNG['pstr_icon']="Ic�ne";
$tpl->LNG['pstr_redirect']="Redirection";
$tpl->LNG['pseudo_guest']="Votre pseudo :";

$tpl->LNG['redirect_msg']="Vers le message post�";
$tpl->LNG['redirect_for']="Vers le forum : {%::ForumInfo[forumtitle]%}";
$tpl->LNG['redirect_cat']="Vers la cat�gorie : {%::ForumInfo[cattitle]%}";
$tpl->LNG['redirect_acc']="Vers la page d'accueil du Forum";

$tpl->LNG['form_valid']="Ajouter votre r�ponse";
$tpl->LNG['form_visu']="Visualiser avant envoi";
$tpl->LNG['form_cancel']="Retour au forum";

$tpl->LNG['sondage']="Ins�rer un nouveau sondage";
$tpl->LNG['maxsond']="Nombre de choix (maximum {%::_FORUMCFG[limitpoll]%})";

$tpl->LNG['facultatif']="(facultatif)";
$tpl->LNG['newrep']="Ajouter votre r�ponse";
$tpl->LNG['newtopic']="Ajouter votre message";

$tpl->LNG['msgcache'] = "Message cach�";
$tpl->LNG['origmsg'] = "Message original";


$tpl->LNG['mailsujet']="\"R�ponse � votre message sur \".\$_FORUMCFG['mailforumname']";
$tpl->LNG['mailmsg']="\"Vous avez configur� votre compte pour recevoir une notification par email en cas de r�ponse � un sujet auquel vous avez particip�.\n
Nous vous informons qu'une r�ponse a �t� ajout�e au sujet \\\"\$mailsujet\\\".\n
Pour allez directement lire cette r�ponse, veuillez rejoindre le lien ci-dessous:
\".\$url.\"\n\n
Merci de ne pas r�pondre � cet email\"";

$tpl->LNG['badsujet']="Veuillez saisir un sujet valide";
$tpl->LNG['badpseudo1']="Veuillez saisir un pseudonyme valide";
$tpl->LNG['badmembre']="Membre invalide";
$tpl->LNG['badpseudo2']="Pseudonyme d�j� utilis� par un membre";
$tpl->LNG['badmsg']="Veuillez saisir un message valide";
$tpl->LNG['badquestpoll']="Veuillez saisir une question de sondage valide";
$tpl->LNG['badreppoll']="Pas assez de propositions d�tect�es";

$tpl->LNG['sondagett']="Nouveau Sondage";
$tpl->LNG['question']="Votre Question";
$tpl->LNG['sondhelp']="Aide sondage";
$tpl->LNG['sondhelp_cmt']="Saisissez le th�me de votre sondage ainsi que vos diff�rentes propositions.
            Vous n'�tes pas oblig� de remplir toutes les propositions, saisissez vos choix dans l'ordre et laissez vides les champs en trop.
            Ne laissez pas de choix vide entre deux propositions, celles se trouvant apr�s l'espace ne seront pas prises en compte. Veillez � saisir au moins
            deux propositions.";
$tpl->LNG['choix']="Choix";
