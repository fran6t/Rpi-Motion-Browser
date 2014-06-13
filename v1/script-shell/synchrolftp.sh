#!/bin/bash
#
# Synchronise deux répertoires en utilisant FTP

# Les infos pour connection FTP et synchro
HOST="ftpperso.free.fr"
LOGIN="monlogin"
PASSWORD="monmdp"
#LOCALDIR="$1"
LOCALDIR="/home/pi/motion"
REMOTEDIR="/camft/pics"
EXCLUDED="*.*~"


# Les infos pour le mail d'alerte
SITEVISU="http://monnom.free.fr/camft/"
AQUIMAIL="monadresse@gmail.com"
FROMQUIMAIL="From:monadresse@free.fr"

SCRIPTNAME=`basename $0`

echo "["`date +"%Y/%m/%d %H:%M:%S"`"] Demarrage du script $0" >>/tmp/$SCRIPTNAME.log
function Usage()
{
  echo -e "\n  Synchronise un répertoire local avec un répertoire distant en utilisant FTP";
  echo -e "\n  USAGE: ftpsync local_dir";
}

if [ "$LOCALDIR" = "" ]
then
  echo -e "  ERREUR: Veuillez spécifier un répertoire local";
  Usage;
  exit 1;
fi
if [ -f "/tmp/$SCRIPTNAME.lock" ]
then
        echo "Script $SCRIPTNAME déjà en cours d'execution"
        echo "["`date +"%Y/%m/%d %H:%M:%S"`"] Déjà en cours execution abandon" >>/tmp/$SCRIPTNAME.log
        exit 1
fi
if [ -e "$LOCALDIR" ]
then
  echo "$SCRIPTNAME" > /tmp/$SCRIPTNAME.lock
  sleep 10
  echo "["`date +"%Y/%m/%d %H:%M:%S"`"] Début synchronisation" >>/tmp/$SCRIPTNAME.log
  sudo lftp -c "set ftp:list-options -a;
  open ftp://$LOGIN:$PASSWORD@$HOST;
  lcd $LOCALDIR;
  cd $REMOTEDIR;
  mirror --reverse \
         --only-newer \
         --verbose \
         --exclude-glob $EXCLUDED";
 sudo rm -rf $LOCALDIR/*
 echo $SITEVISU | mail -s "Detection mouvement à la maison" -t $AQUIMAIL -a $FROMQUIMAIL
 rm /tmp/$SCRIPTNAME.lock
 echo "["`date +"%Y/%m/%d %H:%M:%S"`"] Fin synchronisation" >>/tmp/$SCRIPTNAME.log
fi
