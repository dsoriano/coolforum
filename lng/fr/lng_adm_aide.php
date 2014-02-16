<?php
$tpl->LNG['titlesection'] = "Aide à l'administration";
$tpl->LNG['menuback'] = "Retour au menu";

$tpl->LNG['forumoptions'] = "Les options du forum";
$tpl->LNG['creatcat'] = "Créer une catégorie";
$tpl->LNG['creatforum'] = "Créer un forum";
$tpl->LNG['delcatforum'] = "Supprimer une catégorie ou un forum";
$tpl->LNG['modcatforum'] = "Modifier une catégorie ou un forum";
$tpl->LNG['modogest'] = "Gérer les modérateurs d'un forum";
$tpl->LNG['pvforum'] = "Les forums privés";
$tpl->LNG['annoncestt'] = "Les annonces";
$tpl->LNG['infomb'] = "Chercher un membre et modifier son profil";
$tpl->LNG['banmb'] = "Bannir / Débannir un membre";
$tpl->LNG['mbwait'] = "Membres en attente";
$tpl->LNG['skingest'] = "Ajouter / modifier / supprimer un Skin";
$tpl->LNG['dbopt'] = "Optimisation des tables";
$tpl->LNG['mailing'] = "Mailing-List";
$tpl->LNG['pub'] = "Gestion publicitaire";

$tpl->LNG['forumoptions_cmt'] = "La première chose qu'il convient de régler lors de la première installation du forum sont les options générales. Ces options
			sont regroupées sous divers parties que l'on retrouve dans le menu de gauche: <b>Configuration du board</b>, <b>Options générales</b>,
			<b>Gestion des limites</b>, <b>Avatars</b>, <b>Insertions HTML</b>, <b>Statistiques</b>, <b>Cookies</b> et <b>Emails</b>.<p>
			
			Il convient de régler soigneusement ces paramètres dès l'installation de votre forum. Le réglage de ce grand nombre de paramètres
			est facilité par la description de chacune des options et vous ne devriez pas rencontrer de problèmes à les assimiler.<p>
			
			Si certains paramètres ne vous semblent pas clairs, n'hésitez pas à consulter la <a href=\\\"http://www.coolforum.net/doc/index.html\\\">documentation officielle</a> ou à demander de l'aide
			sur les forums support.";
			
$tpl->LNG['creatcat_cmt'] = "Une catégorie est une entité qui contient un ou des forums. Un forum appartient donc forcément à une catégorie. Ce qui veut 
			dire que même si vous n'avez besoin que d'un seul forum, il vous faudra d'abord créer une catégorie pour l'héberger.<p>

			Créer une catégorie ne prend quelques secondes. Cliquez sur le lien 'Créer une catégorie' dans le menu de gauche, insérez le 
			titre de votre catégorie et validez. Votre catégorie est maintenant créée. Le commentaire sur une catégorie est optionnel.<p>
			
			Notez que l'apparation d'une catégorie sur le forum ne se fait que si le membre présent a au moins le droit de lecture sur 
			un de ses forums, sinon elle est invisible. Ce qui veut donc aussi dire qu'une catégorie qui ne contient pas de forum
			existera mais n'apparaîtra jamais visuellement. Vous n'avez donc pas à vous préoccuper de cacher les catégories contenant 
			des forums privés ou autre, cela se fait automatiquement. Le nombre de catégories n'est pas limité.";
			
$tpl->LNG['creatforum_cmt'] = "Pour pouvoir créer un forum, il vous faudra d'abord créer au moins une catégorie. Cela étant fait, cliquez sur le lien 
			de création de forum dans le menu de gauche. On vous demandera: 
			
			<ul>
			<li>Son titre 
			<li>La catégorie dans laquelle vous désirez qu'il apparaisse 
			<li>Un commentaire qui apparaîtra sous le nom du forum et qui peut servir à détailler son thème 
			<li>Si vous voulez que le forum soit ouvert aux membres ou fermé.
			<li>Les droits des utilisateurs qui y auront accès.
			</ul>
			 
			Faites attention de toujours garder une certaine logique avec les droits de vos membres sur les forums. En effet, sachant 
			par exemple que pour ajouter un sondage il faut ajouter un nouveau sujet, il ne serait pas logique de donner les droits 
			d'ajouter un sondage à une catégorie de membres sans ajouter les droits d'ajouter un sujet. Les membres ne pourraient 
			toujours pas ajouter de sondage... Veillez donc à garder une certaine logique dans vos combinaisons.<p>
			
			En général, vous fermerez rarement un forum, cependant, cela peut-être utile en cas de maintenance dans ce forum
			comme la suppression de sujets ou autre. Un forum fermé empêche qui que ce soit d'y pénétrer, sauf pour le ou les 
			administrateurs.<p>
			
			La configuration terminée, cliquez sur le bouton 'Valider'. Votre forum est enfin créé et est prêt à recevoir des messages.";
			
$tpl->LNG['delcatforum_cmt'] = "Ces deux options présentes dans le menu de gauche sont à manier avec précaution: 
			
			<ul>
			<li>Supprimer un forum revient à le retirer compètement. Tous les messages qui en font partie sont irrémédiablement 
			effacés lors de la suppression. Pour supprimer un forum, cliquez sur le lien concerné dans le menu et cliquez sur 'supprimer' 
			à côté du forum à effacer. 
			<li>Tout comme la suppression d'un forum, supprimer une catégorie revient également à supprimer tous les forums de cette 
			catégorie. La procédure est exactement la même que pour les forums et vous y avec accès grâce au menu de gauche.<p>
			</ul>";
			
$tpl->LNG['modcatforum_cmt'] = "A tout moment, vous avez la possibilité de modifier les caractéristiques d'une catégorie ou d'un forum. Ajouté à cela, vous pouvez 
			aussi choisir la position d'apparition des catégories et des forums afin d'organiser comme vous le souhaitez sans contraintes. 
			Pour modifier la position d'un forum ou d'une catégorie il vous suffit simplement de cliquer sur la position que vous voulez 
			lui donner à côté de son nom pour effectuer le changement. Cette opération s'effectue à partir des liens 'Modifier un forum' 
			ou 'Modifier une catégorie' dans le menu de gauche. Si vous cliquez sur un forum ou une catégorie, vous accèderez à ses propriétés 
			et pourrez effectuer vos changement.<p>

			L'interface s'apparente à l'ajout d'une catégorie ou d'un forum et reprend donc les mêmes fonctions sauf qu'ici vous retrouverez 
			les paramètres définis pour le forum choisis.";
			
$tpl->LNG['modogest_cmt'] = "Les modérateurs sont des membres du forum qui jouent le rôle de \\\"surveillant\\\" sur un forum bien précis. Selon vos désirs, il a 
			plus ou moins de droits d'action afin de faire appliquer les règles du forum.<p>

			En tout premier lieu, sachez que pour être modérateur d'un forum, un membre doit avant tout avoir le statut de modérateur 
			Pour cela vous devez modifier le statut du membre en question et le rendre modérateur. Si vous ne savez pas encore comment
			réaliser cette opération, rendez-vous dans la partie \\\"Chercher un membre et modifier son profil\\\".<p>
			
			Une fois que votre membre a le niveau de modérateur, rendez-vous dans la section \\\"Modifier un forum\\\" et cliquez sur le forum 
			auquel vous voulez ajouter un modérateur.<p>
			
			Tout en bas, vous retrouverez l'interface qui permet de gérer vos modérateurs.<br>
			Cette interface se présente en deux colonnes:<br>
			- La liste des modérateurs disponibles : il s'agit de tous les membres qui ont le statut de modérateur mais qui ne sont pas sélectionnés pour modérer ce forum<br>
			- Modérateurs Sélectionnés : Les modérateurs actuels qui ont été sélectionnés pour s'occuper du forum en question.<p>
			
			Tout d'abord, pour ajouter un modérateur dans la colonne des modérateurs sélectionnés, choisissez un membre dans la liste 
			des modérateurs disponibles et cliquez sur son pseudo. Il passera automatiquement du côté des modérateurs sélectionnés.<p>
			
			Si vous en ajoutez au moins deux, vous constaterez que vous avez la possibilité de modifier leur position d'affichage. Cette 
			position représente leur ordre d'apparition sur le forum.<p>
			
			Maintenant vous pouvez affiner les droits de chacun des modérateurs si vous le désirez. Cliquez sur un modérateur de la 
			colonne \\\"modérateurs sélectionnés\\\", vous entrerez dans le détail de ses droits. C'est ici que vous pourrez ajouter ou supprimer 
			des possibilité à ce modérateur. Attention, cette configuration n'est valable que pour ce modérateur et pour ce forum. Vous 
			remarquerez que c'est ici aussi que vous pouvez supprimer ce modérateur et le replacer dans la colonne des modérateurs disponibles.";

$tpl->LNG['annoncestt_cmt'] = "Elles vous permettent de diffuser un message dans un ou plusieurs forums. Ce message apparait sous la forme d'un sujet classique 
			dont le sujet est précédé de Annonce:. Une annonce apparait toujours en tête de page et sur chaque page du forum. Seuls les 
			administrateurs peuvent ajouter des annonces. Les membres peuvent la consulter mais ne peuvent y répondre, l'éditer ou 
			la supprimer. Seuls les administrateurs ont ces droits à partir de l'administration du forum.<p>

			L'interface pour gérer les annonces est trés simple et s'apparente à l'écriture d'un simple message. Seule l' option d'application 
			sur les forums est rajoutée: il vous suffit de cliquer sur les forums dans lesquels vous voulez que l'annonce apparaisse.";

$tpl->LNG['infomb_cmt'] = "Vous avez la possibilité de visionner et de modifier les informations relatives à un membre. Pour cela vous devez utiliser le
			lien \\\"Infos membre\\\". Vous avez à votre disposition deux méthode de recherche: l'affichage des membre selon certains critères
			ou bien un moteur de recherche.<p>
			
			Dans les deux cas, lorsque vous avez trouvé votre membre, vous devez cliquer sur le <b>+</b> afin d'accéder à son profil. De là
			vous pouvez modifier les informations du profil de ce membre ou son groupe.<p>
			
			Vous avez également la possibilité de supprimer le membre. Attention, la suppression du membre entraîne la suppression de tous
			ses sujets et réponses du forum.<p>
			
			Si votre membre a perdu son mot de passe, ou s'il a un problème avec son mot de passe, vous pouvez également lui générer un
			nouveau mot-de-passe qui vous sera transmit. A vous de le lui communiquer ensuite.<p>
			
			C'est aussi par là qu'il faudra passer pour supprimer un membre.";
			
$tpl->LNG['banmb_cmt'] = "Si un de vos membre n'a pas un comportement se conformant à vos règles (propos injurieux, racistes...), vous avez la possibilité 
			de bannir ce membre du forum. Il ne pourra plus poster de message sous son pseudonyme et dès son arrivée sur le forum, il sera 
			refoulé. Bien entendu, rien n'empêche ce membre de supprimer tous ses cookies et de revenir sur le forum en tant que simple 
			visiteur, ou même de se réinscrire sous un autre pseudonyme. Cependant, lorsqu'un membre est banni, son adresse email est 
			<b>blacklistée</b>, ce qui veut dire qu'elle sera reconnue comme appartenant à un membre banni et l'inscription sera refusée.<p>

			Certains <b>flooders</b> ou <b>spammeurs</b> de forums s'amusent à s'enregistrer, modifier directement leur email par une adresse non valide 
			et ont des comportements peut recommendables. Histoire d'ajouter une protection supplémentaire, l'email d'origine qui a été inséré 
			lors de l'inscription est elle aussi conservée et blacklistée en cas de ban.<p>
			
			Si à la suite d'un différent un membre est banni et que vous revennez sur votre décision, il vous est bien entendu possible 
			de débannir ce membre.<p>
			
			Nous avons préféré ne pas bannir les membres à partir de leur adresse IP. Celle-ci étant le plus souvent dynamique, elle change 
			couramment, ce type de ban devenant totalement inutile.";
			
$tpl->LNG['mbwait_cmt'] = "Si vous utilisez la confirmation des inscriptions par email, un membre ne sera pas considéré comme membre valide tant que 
			l'inscription de son compte ne sera pas confirmée. En attendant, il aura un statut de Membre en attente. Vous verrez que 
			certains de vos membres vont s'inscrire et ne vont jamais confirmer leur inscription. Il est donc possible de supprimer ces 
			membres.<p>

			Nous recommandons aux administrateurs de laisser un mois de délai à vos membres en attente pour confirmer leur inscription. 
			Ensuite vous pourrez le supprimer. Bien entendu libre à vous de décider de ce délai.";
			
$tpl->LNG['skingest_cmt'] = "Les opérations sur les skins s'effectuent à partir des liens se trouvant dans le menu de gauche dans la catégorie <b>Skins</b>.
			Ces actions sont assez simples et vous permettent de modifier de manière simple l'apparence de votre forum.<p>
			
			Un skin est caractérisé par trois parties:
			<ul>
			<li> La définition des propriétés du skin et des couleurs
			<li> Le répertoire contenant les images du skin
			<li> Le répertoire contenant les templates
			</ul>
			
			Vous pouvez trés bien utiliser le répertoire d'images par défaut ou utiliser un répertoire avec des images personnalisées
			et faire la même choses avec le répertoire template qui contient toute la partie HTML.";
			
$tpl->LNG['dbopt_cmt'] = "Votre base de donnée se comporte un peu comme un disque dur. A mesure que vous l'utilisez, elle a un comportement similaire
			à une \\\"fragmentation\\\" de disque dur qui la ralentit. L'optimisation de la base de donnée permet de la \\\"défragmenter\\\". Ainsi
			vous allez gagner non seulement en place mais aussi en rapidité.<p>
			
			Il n'est par contre pas nécessaire d'optimiser votre base de donnée trop souvent, cela prend des ressources inutilement.
			Une bonne moyenne est d'optimiser votre base une fois par mois. Pour cela cliquez sur le lien <b>Optimiser les tables</b>";
			
$tpl->LNG['mailing_cmt'] = "Si votre hébergeur supporte l'envoi d'emails en PHP, vous avez la possibilité d'envoyer un mailing à tous vos membres. Bien
			entendu, les membres ont le choix dans leur profil de refuser ce mailing et vous vous devez de respecter cette décision.<p>
			
			Notez que l'envoi d'un mailing peut prendre plusieurs minutes selon le nombre de membres que vous possédez, soyez donc patient...";

$tpl->LNG['pub_cmt'] = "Coolforum intègre un système de gestion de bannières publicitaires. Ce système vous permet de gérer aussi bien les bannières
			de vos partenaires que les bannières de vos régies publicitaires.<p>
			
			Le système permet également de définir un ratio qui vous permet d'afficher une campagne plus souvent qu'une autre et vous propose
			un système de statistiques rapide ainsi que des statistiques graphiques détaillées par jour.";

