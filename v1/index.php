<?php
/*
But du script : Presenter dans une page web les captures images et vidéo prise 
par le programme motion ( http://www.lavrsen.dk ) sur raspberry pi

Le script initial est de Cédric Verstraeten
Site de Cédric Vertraeten : http://blog.cedric.ws/ 
Source d'origine : https://github.com/cedricve/motion_detection 

Pour que le script fonctionne correctement il faut s'assurer que motion
fournisse des noms de fichiers structurés comme ci-dessous. 

Pour ce faire il suffit de modifier les deux paramètres suivant dans le 
fichier motion.conf afin de prendre un nommage dit 3.0 

picture_filename %Y/%m/%d/%H/%M/%S-%q
movie_filename %Y/%m/%d/%H/%M/%S
 
Exemple une image nommée comme cela 20140529201815-00.jpg est 
* Exemple une image nommée comme cela 2014/05/29/20/1815-00.jpg est 
decorticable comme cela :
    	Par defaut la config des noms de fichier de motion configué par motion.conf comme cela 
		2014	Annee sur 4 char		(pos 0)
		05		Mois					(pos 4)
		29		Jour					(pos 6)
		20		Heure					(pos 8)
		18		Minute					(pos 10)
		15		Second					(pos 12)
		-		Separateur
		00		Numero img dans la sequence
		.jpg	Extension
*/
// Emplacement où se trouve les captures
$emplacement = "pics";   // Exemple /home/usr/motion
// Variable des positions afin d'adapter plus facile le script
$offsetfic=strlen($emplacement)+1;
$pos_an 	= $offsetfic + 0;
$pos_mois 	= $offsetfic + 1 + 4;
$pos_jour	= $offsetfic + 2 + 6;
$pos_heure	= $offsetfic + 3 + 8;
$pos_min	= $offsetfic + 4 + 10;
$pos_sec	= $offsetfic + 5 + 12;
?>
<html>
<head>
<script type="text/javascript" src="jquery.js"></script>
<link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<link rel="stylesheet" type="text/css" href="jquery-ui-1.8.18.custom.css" />
<link rel="stylesheet" type="text/css" href="styles.css" />
<script type="text/javascript" src="fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
		$(document).ready(function() {
				$("a.vergroot").fancybox();   
				$(".video").click(function() {
					$.fancybox({
						'padding'		: 0,
						'autoScale'		: false,
						'transitionIn'	: 'none',
						'transitionOut'	: 'none',
						'title'			: this.title,
						'width'			: 640,
						'height'		: 385,
						'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
						'type'			: 'swf',
						'swf'			: {
							'wmode'				: 'transparent',
							'allowfullscreen'	: 'true'
						}
					});
				return false;
				});     
		});
</script>
</head>
<body>
<?php
if(!isset($_GET['page'])) $_GET['page'] = 1;

$aantal = 16;	
$j = 0;
$begin = ($_GET['page']-1)*$aantal;
$end = $begin+$aantal; 

if(!isset($_GET['dag'])){
	$dag = date("d",time());
	//$maand = substr(date("F",time()),0,3);
	$maand = date("m",time());
	$jaar = date("Y",time());
	//$current = $dag.$maand.$jaar;
	$current = $jaar.$maand.$dag;
} else {
	$current = $_GET['dag'];
}
$current_aff = substr($current,6,2)."/".substr($current,4,2)."/".substr($current,0,4);

if ( is_dir($emplacement) ) {
	// On init une variable temps pour calculer le temps de la boucle de parcours recursif
	$timestart=microtime(true);
	// On va essayer de remplir avec une seule passe sur le repertoire tous les tableaux necessaires
	$months = array(); // Tableau pour presenter les jour où il y a quelque chose
	$day = array(); // Tableau des evenements d'une journée
	$hour = array(); // Tableau des evenements pour une heure donnée
	$dir_iterator = new RecursiveDirectoryIterator($emplacement);
	$iterator = new RecursiveIteratorIterator($dir_iterator);
	foreach ($iterator as $entry) {
			//echo $entry."<br />";
			array_push($months, $entry);
        	// On rempli le tableau $day & $hour
        	if (isset($_GET['dag'])){
				//echo "annee=".substr($_GET['dag'],0,4)."=".substr($entry,3,4)."<br />";
				if (substr($_GET['dag'],0,4)==substr($entry,$pos_an,4)){
					// Il faut le bon mois
					//echo "mois=".substr($_GET['dag'],4,2)."=".substr($entry,7,2)."<br />";
					if (substr($_GET['dag'],4,2)==substr($entry,$pos_mois,2)){							
						// Le bon jour
						//echo "jours=".substr($_GET['dag'],6,2)."=".substr($entry,9,2)."<br />";
						if (substr($_GET['dag'],6,2)==substr($entry,$pos_jour,2)){
							array_push($day, $entry);
							//echo "trouve:".$entry."<br />";
							// Et enfin la bonne heure
							if (isset($_GET['uur'])){
								$uur =  substr($entry,$pos_heure,2);
								//if($uur == 0) $uur = 24;
								if ($uur == $_GET['uur']){
									array_push($hour, $entry);
								}
							} 
						}
					}
				}
			}
	}
	$timeend=microtime(true);
	$time=$timeend-$timestart;
	$page_load_time = number_format($time, 3);
    //sort($months);
    //sort($day);
    //sort($hour);
} else {
	echo "<div class='month'>Repertoire capture images vide !!!</div>";
	echo "</body></html>";
	return;
}    

echo "<div class='month'>Date en cours de visualisation : $current_aff</div>";
echo "<ul id='month_list' style='overflow:hidden'>";
    $oldday = "";
    for($f = 0; $f < count($months); $f++){
			$dag 	= 	substr($months[$f],$pos_jour,2);
			$maand 	= 	substr($months[$f],$pos_mois,2);
			$jaar 	=  	substr($months[$f],$pos_an,4);
			// On fait qu'une seule entree par jour
			if ($dag != $oldday){
					$oldday = $dag;
					echo "<li  class='button' style='float:left'><a style='text-decoration:none;color: #fff;' href='".$_SERVER["PHP_SELF"]."?page=1&dag=".$jaar.$maand.$dag."'>".$dag."/".$maand."/".$jaar."</a></li>"; 
			}  	
	}
echo "</ul>";
echo "<h2>Nombre de fichiers de capture presents sur le disque : ".count($months)." Tps execution : ".$page_load_time." sec</h2><br/>";

$current_hour = date("G",time());
if($current_hour==0) $current_hour = 24;

echo "<a href='".$_SERVER["PHP_SELF"]."?dag=$current'  class='button' >Look sequentially</a>&nbsp;&nbsp;&nbsp;<a  class='button'  href='".$_SERVER["PHP_SELF"]."?dag=$current&uur=".$current_hour."'>Look hourly</a><br/><br/>";

if (isset($_GET['dag'])){
	if(!isset($_GET['uur'])){
			$cols = 4; $i = 0;

			if(count($day) <  $end)	$end = count($day);
 	
			for($f = 0; $f <= count($day)/$aantal; $f++){
				if($f%20==0) echo "<br/><br/><br/>";
				if($_GET['page']==$f+1){
					echo "<a class='button'  href='".$_SERVER["PHP_SELF"]."?page=".($f+1)."&dag=".$current."'>";
					echo "<span style='text-decoration:underline;color:#B23232;'>". ($f+1) ."</span>";
					echo "</a>";
				} else {
					echo "<a class='button'  href='".$_SERVER["PHP_SELF"]."?page=".($f+1)."&dag=".$current."'><span>". ($f+1) ."</span></a>";
				}
			}
			echo "</ul>";
			echo "<br/><br/>&nbsp;<br/>";
        
			echo "<table><tr>";
			for($f = $begin; $f < $end; $f++){
				if($i%$cols == 0) echo "</tr><tr>";
				$i++;
				$uur = substr($day[$f],$pos_heure,2);
				$min = substr($day[$f],$pos_min,2);
				$sec = substr($day[$f],$pos_sec,2);
				// Si c'est un .avi on traite differement
				if (strrchr($day[$f],'.')==".avi"){
					echo "<td style='text-align:center'>";
					echo "		<a href=\"".$day[$f]."\">";
					echo "		Video sequence <br />".substr($_GET['dag'],6,2)." ".substr($_GET['dag'],4,2)." ". substr($_GET['dag'],0,4) . " - ".$uur. ":" . $min. ":". $sec;
					echo "		</a>";
					echo "</td>";
				} else {
					echo "<td style='text-align:center'>";
					echo "	<a class='vergroot' title='".substr($_GET['dag'],6,2)." ".substr($_GET['dag'],4,2)." ". substr($_GET['dag'],0,4) ." - ".$uur. ":" . $min. ":". $sec."' rel='reeks' href='".$day[$f]."'>";
					echo "		<img src='".$day[$f]."' width='300' style='float:left;'>";
					echo "		<br/>".substr($_GET['dag'],6,2)." ".substr($_GET['dag'],4,2)." ". substr($_GET['dag'],0,4) . " - ".$uur. ":" . $min. ":". $sec;
					echo "	</a>";
					echo "</td>";
				}
			}
			echo "</tr></table>";
    
	} else {

		$oldhour = "";
		echo "<br/><br/><br/>";
		foreach ($day as &$value) {
			if($oldhour!=substr($value,$pos_heure,2)){
				$oldhour=substr($value,$pos_heure,2);
				echo "<a class='button' href='".$_SERVER["PHP_SELF"]."?dag=".$current."&uur=".substr($value,$pos_heure,2)."'><span style='text-decoration:underline;color:#B23232;'>".substr($value,$pos_heure,2)."H</span></a>&nbsp;&nbsp;&nbsp;";
			}
		}
		unset($value);
		echo "<br/><br/><br/>";
	
		echo "<table><tr>";
		$cols = 4; $i = 0;
   	
		for($f = 0; $f < count($hour); $f++){
			if($i%$cols == 0) echo "</tr><tr>";
			$i++;
    	
			$uur = substr($hour[$f],$pos_heure,2);
			$min = substr($hour[$f],$pos_min,2);
			$sec = substr($hour[$f],$pos_sec,2);
			if (strrchr($hour[$f],'.')==".avi"){
				echo "<td style='text-align:center'>";
				echo "		<a href=\"".$hour[$f]."\">";
				echo "		Voir video sequence<br/>".substr($_GET['dag'],6,2)." ".substr($_GET['dag'],4,2)." ". substr($_GET['dag'],0,4) . " - ".$uur. ":" . $min. ":". $sec;
				echo "		</a>";
				echo "</td>";
			} else {
				echo "<td style='text-align:center'>";
				echo "	<a class='vergroot' title='".substr($_GET['dag'],6,2)." ".substr($_GET['dag'],4,2)." ". substr($_GET['dag'],0,4) ." - ".$uur. ":" . $min. ":". $sec."' rel='24feb' href='".$hour[$f]."'>";
				echo "		<img src='".$hour[$f]."' width='300' style='float:left;'>";
				echo "		<br/>".substr($_GET['dag'],6,2)." ".substr($_GET['dag'],4,2)." ". substr($_GET['dag'],0,4) . " - ".$uur. ":" . $min. ":". $sec;
				echo "	</a>";
				echo "</td>";
			}
		}
		echo "</tr></table>";    
	}
} else {
	print "?..";
}
?>
</body>
</html>
