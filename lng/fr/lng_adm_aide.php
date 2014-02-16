<?php
$tpl->LNG['titlesection'] = "Aide � l'administration";
$tpl->LNG['menuback'] = "Retour au menu";

$tpl->LNG['forumoptions'] = "Les options du forum";
$tpl->LNG['creatcat'] = "Cr�er une cat�gorie";
$tpl->LNG['creatforum'] = "Cr�er un forum";
$tpl->LNG['delcatforum'] = "Supprimer une cat�gorie ou un forum";
$tpl->LNG['modcatforum'] = "Modifier une cat�gorie ou un forum";
$tpl->LNG['modogest'] = "G�rer les mod�rateurs d'un forum";
$tpl->LNG['pvforum'] = "Les forums priv�s";
$tpl->LNG['annoncestt'] = "Les annonces";
$tpl->LNG['infomb'] = "Chercher un membre et modifier son profil";
$tpl->LNG['banmb'] = "Bannir / D�bannir un membre";
$tpl->LNG['mbwait'] = "Membres en attente";
$tpl->LNG['skingest'] = "Ajouter / modifier / supprimer un Skin";
$tpl->LNG['dbopt'] = "Optimisation des tables";
$tpl->LNG['mailing'] = "Mailing-List";
$tpl->LNG['pub'] = "Gestion publicitaire";

$tpl->LNG['forumoptions_cmt'] = "La premi�re chose qu'il convient de r�gler lors de la premi�re installation du forum sont les options g�n�rales. Ces options
			sont regroup�es sous divers parties que l'on retrouve dans le menu de gauche: <b>Configuration du board</b>, <b>Options g�n�rales</b>,
			<b>Gestion des limites</b>, <b>Avatars</b>, <b>Insertions HTML</b>, <b>Statistiques</b>, <b>Cookies</b> et <b>Emails</b>.<p>
			
			Il convient de r�gler soigneusement ces param�tres d�s l'installation de votre forum. Le r�glage de ce grand nombre de param�tres
			est facilit� par la description de chacune des options et vous ne devriez pas rencontrer de probl�mes � les assimiler.<p>
			
			Si certains param�tres ne vous semblent pas clairs, n'h�sitez pas � consulter la <a href=\\\"http://www.coolforum.net/doc/index.html\\\">documentation officielle</a> ou � demander de l'aide
			sur les forums support.";
			
$tpl->LNG['creatcat_cmt'] = "Une cat�gorie est une entit� qui contient un ou des forums. Un forum appartient donc forc�ment � une cat�gorie. Ce qui veut 
			dire que m�me si vous n'avez besoin que d'un seul forum, il vous faudra d'abord cr�er une cat�gorie pour l'h�berger.<p>

			Cr�er une cat�gorie ne prend quelques secondes. Cliquez sur le lien 'Cr�er une cat�gorie' dans le menu de gauche, ins�rez le 
			titre de votre cat�gorie et validez. Votre cat�gorie est maintenant cr��e. Le commentaire sur une cat�gorie est optionnel.<p>
			
			Notez que l'apparation d'une cat�gorie sur le forum ne se fait que si le membre pr�sent a au moins le droit de lecture sur 
			un de ses forums, sinon elle est invisible. Ce qui veut donc aussi dire qu'une cat�gorie qui ne contient pas de forum
			existera mais n'appara�tra jamais visuellement. Vous n'avez donc pas � vous pr�occuper de cacher les cat�gories contenant 
			des forums priv�s ou autre, cela se fait automatiquement. Le nombre de cat�gories n'est pas limit�.";
			
$tpl->LNG['creatforum_cmt'] = "Pour pouvoir cr�er un forum, il vous faudra d'abord cr�er au moins une cat�gorie. Cela �tant fait, cliquez sur le lien 
			de cr�ation de forum dans le menu de gauche. On vous demandera: 
			
			<ul>
			<li>Son titre 
			<li>La cat�gorie dans laquelle vous d�sirez qu'il apparaisse 
			<li>Un commentaire qui appara�tra sous le nom du forum et qui peut servir � d�tailler son th�me 
			<li>Si vous voulez que le forum soit ouvert aux membres ou ferm�.
			<li>Les droits des utilisateurs qui y auront acc�s.
			</ul>
			 
			Faites attention de toujours garder une certaine logique avec les droits de vos membres sur les forums. En effet, sachant 
			par exemple que pour ajouter un sondage il faut ajouter un nouveau sujet, il ne serait pas logique de donner les droits 
			d'ajouter un sondage � une cat�gorie de membres sans ajouter les droits d'ajouter un sujet. Les membres ne pourraient 
			toujours pas ajouter de sondage... Veillez donc � garder une certaine logique dans vos combinaisons.<p>
			
			En g�n�ral, vous fermerez rarement un forum, cependant, cela peut-�tre utile en cas de maintenance dans ce forum
			comme la suppression de sujets ou autre. Un forum ferm� emp�che qui que ce soit d'y p�n�trer, sauf pour le ou les 
			administrateurs.<p>
			
			La configuration termin�e, cliquez sur le bouton 'Valider'. Votre forum est enfin cr�� et est pr�t � recevoir des messages.";
			
$tpl->LNG['delcatforum_cmt'] = "Ces deux options pr�sentes dans le menu de gauche sont � manier avec pr�caution: 
			
			<ul>
			<li>Supprimer un forum revient � le retirer comp�tement. Tous les messages qui en font partie sont irr�m�diablement 
			effac�s lors de la suppression. Pour supprimer un forum, cliquez sur le lien concern� dans le menu et cliquez sur 'supprimer' 
			� c�t� du forum � effacer. 
			<li>Tout comme la suppression d'un forum, supprimer une cat�gorie revient �galement � supprimer tous les forums de cette 
			cat�gorie. La proc�dure est exactement la m�me que pour les forums et vous y avec acc�s gr�ce au menu de gauche.<p>
			</ul>";
			
$tpl->LNG['modcatforum_cmt'] = "A tout moment, vous avez la possibilit� de modifier les caract�ristiques d'une cat�gorie ou d'un forum. Ajout� � cela, vous pouvez 
			aussi choisir la position d'apparition des cat�gories et des forums afin d'organiser comme vous le souhaitez sans contraintes. 
			Pour modifier la position d'un forum ou d'une cat�gorie il vous suffit simplement de cliquer sur la position que vous voulez 
			lui donner � c�t� de son nom pour effectuer le changement. Cette op�ration s'effectue � partir des liens 'Modifier un forum' 
			ou 'Modifier une cat�gorie' dans le menu de gauche. Si vous cliquez sur un forum ou une cat�gorie, vous acc�derez � ses propri�t�s 
			et pourrez effectuer vos changement.<p>

			L'interface s'apparente � l'ajout d'une cat�gorie ou d'un forum et reprend donc les m�mes fonctions sauf qu'ici vous retrouverez 
			les param�tres d�finis pour le forum choisis.";
			
$tpl->LNG['modogest_cmt'] = "Les mod�rateurs sont des membres du forum qui jouent le r�le de \\\"surveillant\\\" sur un forum bien pr�cis. Selon vos d�sirs, il a 
			plus ou moins de droits d'action afin de faire appliquer les r�gles du forum.<p>

			En tout premier lieu, sachez que pour �tre mod�rateur d'un forum, un membre doit avant tout avoir le statut de mod�rateur 
			Pour cela vous devez modifier le statut du membre en question et le rendre mod�rateur. Si vous ne savez pas encore comment
			r�aliser cette op�ration, rendez-vous dans la partie \\\"Chercher un membre et modifier son profil\\\".<p>
			
			Une fois que votre membre a le niveau de mod�rateur, rendez-vous dans la section \\\"Modifier un forum\\\" et cliquez sur le forum 
			auquel vous voulez ajouter un mod�rateur.<p>
			
			Tout en bas, vous retrouverez l'interface qui permet de g�rer vos mod�rateurs.<br>
			Cette interface se pr�sente en deux colonnes:<br>
			- La liste des mod�rateurs disponibles : il s'agit de tous les membres qui ont le statut de mod�rateur mais qui ne sont pas s�lectionn�s pour mod�rer ce forum<br>
			- Mod�rateurs S�lectionn�s : Les mod�rateurs actuels qui ont �t� s�lectionn�s pour s'occuper du forum en question.<p>
			
			Tout d'abord, pour ajouter un mod�rateur dans la colonne des mod�rateurs s�lectionn�s, choisissez un membre dans la liste 
			des mod�rateurs disponibles et cliquez sur son pseudo. Il passera automatiquement du c�t� des mod�rateurs s�lectionn�s.<p>
			
			Si vous en ajoutez au moins deux, vous constaterez que vous avez la possibilit� de modifier leur position d'affichage. Cette 
			position repr�sente leur ordre d'apparition sur le forum.<p>
			
			Maintenant vous pouvez affiner les droits de chacun des mod�rateurs si vous le d�sirez. Cliquez sur un mod�rateur de la 
			colonne \\\"mod�rateurs s�lectionn�s\\\", vous entrerez dans le d�tail de ses droits. C'est ici que vous pourrez ajouter ou supprimer 
			des possibilit� � ce mod�rateur. Attention, cette configuration n'est valable que pour ce mod�rateur et pour ce forum. Vous 
			remarquerez que c'est ici aussi que vous pouvez supprimer ce mod�rateur et le replacer dans la colonne des mod�rateurs disponibles.";

$tpl->LNG['annoncestt_cmt'] = "Elles vous permettent de diffuser un message dans un ou plusieurs forums. Ce message apparait sous la forme d'un sujet classique 
			dont le sujet est pr�c�d� de Annonce:. Une annonce apparait toujours en t�te de page et sur chaque page du forum. Seuls les 
			administrateurs peuvent ajouter des annonces. Les membres peuvent la consulter mais ne peuvent y r�pondre, l'�diter ou 
			la supprimer. Seuls les administrateurs ont ces droits � partir de l'administration du forum.<p>

			L'interface pour g�rer les annonces est tr�s simple et s'apparente � l'�criture d'un simple message. Seule l' option d'application 
			sur les forums est rajout�e: il vous suffit de cliquer sur les forums dans lesquels vous voulez que l'annonce apparaisse.";

$tpl->LNG['infomb_cmt'] = "Vous avez la possibilit� de visionner et de modifier les informations relatives � un membre. Pour cela vous devez utiliser le
			lien \\\"Infos membre\\\". Vous avez � votre disposition deux m�thode de recherche: l'affichage des membre selon certains crit�res
			ou bien un moteur de recherche.<p>
			
			Dans les deux cas, lorsque vous avez trouv� votre membre, vous devez cliquer sur le <b>+</b> afin d'acc�der � son profil. De l�
			vous pouvez modifier les informations du profil de ce membre ou son groupe.<p>
			
			Vous avez �galement la possibilit� de supprimer le membre. Attention, la suppression du membre entra�ne la suppression de tous
			ses sujets et r�ponses du forum.<p>
			
			Si votre membre a perdu son mot de passe, ou s'il a un probl�me avec son mot de passe, vous pouvez �galement lui g�n�rer un
			nouveau mot-de-passe qui vous sera transmit. A vous de le lui communiquer ensuite.<p>
			
			C'est aussi par l� qu'il faudra passer pour supprimer un membre.";
			
$tpl->LNG['banmb_cmt'] = "Si un de vos membre n'a pas un comportement se conformant � vos r�gles (propos injurieux, racistes...), vous avez la possibilit� 
			de bannir ce membre du forum. Il ne pourra plus poster de message sous son pseudonyme et d�s son arriv�e sur le forum, il sera 
			refoul�. Bien entendu, rien n'emp�che ce membre de supprimer tous ses cookies et de revenir sur le forum en tant que simple 
			visiteur, ou m�me de se r�inscrire sous un autre pseudonyme. Cependant, lorsqu'un membre est banni, son adresse email est 
			<b>blacklist�e</b>, ce qui veut dire qu'elle sera reconnue comme appartenant � un membre banni et l'inscription sera refus�e.<p>

			Certains <b>flooders</b> ou <b>spammeurs</b> de forums s'amusent � s'enregistrer, modifier directement leur email par une adresse non valide 
			et ont des comportements peut recommendables. Histoire d'ajouter une protection suppl�mentaire, l'email d'origine qui a �t� ins�r� 
			lors de l'inscription est elle aussi conserv�e et blacklist�e en cas de ban.<p>
			
			Si � la suite d'un diff�rent un membre est banni et que vous revennez sur votre d�cision, il vous est bien entendu possible 
			de d�bannir ce membre.<p>
			
			Nous avons pr�f�r� ne pas bannir les membres � partir de leur adresse IP. Celle-ci �tant le plus souvent dynamique, elle change 
			couramment, ce type de ban devenant totalement inutile.";
			
$tpl->LNG['mbwait_cmt'] = "Si vous utilisez la confirmation des inscriptions par email, un membre ne sera pas consid�r� comme membre valide tant que 
			l'inscription de son compte ne sera pas confirm�e. En attendant, il aura un statut de Membre en attente. Vous verrez que 
			certains de vos membres vont s'inscrire et ne vont jamais confirmer leur inscription. Il est donc possible de supprimer ces 
			membres.<p>

			Nous recommandons aux administrateurs de laisser un mois de d�lai � vos membres en attente pour confirmer leur inscription. 
			Ensuite vous pourrez le supprimer. Bien entendu libre � vous de d�cider de ce d�lai.";
			
$tpl->LNG['skingest_cmt'] = "Les op�rations sur les skins s'effectuent � partir des liens se trouvant dans le menu de gauche dans la cat�gorie <b>Skins</b>.
			Ces actions sont assez simples et vous permettent de modifier de mani�re simple l'apparence de votre forum.<p>
			
			Un skin est caract�ris� par trois parties:
			<ul>
			<li> La d�finition des propri�t�s du skin et des couleurs
			<li> Le r�pertoire contenant les images du skin
			<li> Le r�pertoire contenant les templates
			</ul>
			
			Vous pouvez tr�s bien utiliser le r�pertoire d'images par d�faut ou utiliser un r�pertoire avec des images personnalis�es
			et faire la m�me choses avec le r�pertoire template qui contient toute la partie HTML.";
			
$tpl->LNG['dbopt_cmt'] = "Votre base de donn�e se comporte un peu comme un disque dur. A mesure que vous l'utilisez, elle a un comportement similaire
			� une \\\"fragmentation\\\" de disque dur qui la ralentit. L'optimisation de la base de donn�e permet de la \\\"d�fragmenter\\\". Ainsi
			vous allez gagner non seulement en place mais aussi en rapidit�.<p>
			
			Il n'est par contre pas n�cessaire d'optimiser votre base de donn�e trop souvent, cela prend des ressources inutilement.
			Une bonne moyenne est d'optimiser votre base une fois par mois. Pour cela cliquez sur le lien <b>Optimiser les tables</b>";
			
$tpl->LNG['mailing_cmt'] = "Si votre h�bergeur supporte l'envoi d'emails en PHP, vous avez la possibilit� d'envoyer un mailing � tous vos membres. Bien
			entendu, les membres ont le choix dans leur profil de refuser ce mailing et vous vous devez de respecter cette d�cision.<p>
			
			Notez que l'envoi d'un mailing peut prendre plusieurs minutes selon le nombre de membres que vous poss�dez, soyez donc patient...";

$tpl->LNG['pub_cmt'] = "Coolforum int�gre un syst�me de gestion de banni�res publicitaires. Ce syst�me vous permet de g�rer aussi bien les banni�res
			de vos partenaires que les banni�res de vos r�gies publicitaires.<p>
			
			Le syst�me permet �galement de d�finir un ratio qui vous permet d'afficher une campagne plus souvent qu'une autre et vous propose
			un syst�me de statistiques rapide ainsi que des statistiques graphiques d�taill�es par jour.";

