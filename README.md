Rpi-Motion-Browser
==================

Permet la visualisation des événements capturés par la detection de mouvement d'un raspberry PI 
équipé d'une caméra et du logiciel motion

Plutôt que de partir d'une feuille blanche j'ai adapté un bout de script de Cédric Verstraeten,
dans la version 1 c'est fonctionnel chez moi (repertoire V1 du dépot).

A l'usage, au bout de trois jours avec prêt de 5000 fichiers de capture, le système atteint des 
limites, la boucle qui parcours les fichiers met sur l'hebergement Free 6 secondes la première 
fois puis suite certainement à des mécanismes de cache elle met 2 secondes.   

Pour cette V1 :
Les pré-requis sont :
* disposer d'un hebergement supportant le PHP 5 (pas besoins de base de données)
* les images et vidéo capturés par le raspberry doivent être mise dans un seul et même repertoire

Pour la V2 :
La liste de TODO :
* usage d'une BDD afin de pouvoir gérer sereinement un nombre illimités d'événement, seul la place disque comptera 
* supprimer l'usage de jquery et fancy (je pense notament à des fenetres modal html5 css3) 
* faire un thème responsive afin d'avoir le même confort et version sur l'ensemble des appareils
* pouvoir supprimer des journées en un clique
* regler un problème au niveau de motion et du rapsberry qui m'oblige a executer la comande synchro et suppression en mode super utilisateur

Le fonctionnement chez moi :

Mon Rapsberry Pi avec une camera HD ainsi que le logiciel motion detecte un mouvement,
il y a enregistrement des images de captures et de la video concernant l'évenement sur 
la carte SD puis envoi des fichiers via lftp vers un hebergement hors de la maison.
Le script qui permet l'evoi des fichiers via lftp est dans le repertoire outils.

Le paramétrage :
Au niveau du logiciel motion, plus exactement dans /etc/motion.conf il faut 
impérativement que motion enregistre les noms de fichiers par exemple comme cela :
20140529201815-00.jpg soit L'année sur 4 chiffres + Le mois sur 2 chiffres +
Le jours sur 2 chiffres + L'heure sur 2 chiffres + Les minutes sur 2 chiffres +
Les secondes sur 2 chiffres.

Pour ce faire il suffit de modifier dans motion.conf ces deux parametres :
picture_filename %Y%m%d%H%M%S-%q
movie_filename %Y%m%d%H%M%S  

Il faut maintenant lors d'un evenement que le Rapsberry Pi envoi les fichiers 
sur l'hebergement pour cela on appel le script mis en exemple

Concretement que se passe-t-il ?
La caméra est en route avec le raspberry configuré et ok. 
Un mouvement est detecté, motion (le logiciel de detection) enregistre alors les 
fichiers de capture sur la carte sd puis active le script synchrolftp.sh qui 
a la tache de copier ce qui vient d'être inscrit sur la carte sur un hebergement 
distant via le protocole ftp. Une fois fait le script efface les fichiers de la carte 
et envoi un mail indiquant un evenement. 

Le de Cédric Verstraeten
===
http://blog.cedric.ws/
Le dépot d'origine dans lequel j'ai repris la partie browser https://github.com/cedricve/motion_detection

Le site officiel de Kenneth Jahn Lavrsen (Logiciel motion)  
===
http://www.lavrsen.dk/foswiki/bin/view/Motion/WebHome
