<html>
<head>
</head>
<body background = "yellow.gif">
<?php    
/*
 * Encodeur PHP QR Code
 * PHP QR Code est distribué sous LGPL 3
 * Cette bibliothèque est un logiciel libre; vous pouvez le redistribuer et / ou le modifier selon les termes du GNU Lesser General Public Licence publiée par la Free Software Foundation; Soit version 3 de la licence ou toute version ultérieure.
 * Cette bibliothèque est distribuée dans l'espoir qu'elle vous sera utile,mais SANS AUCUNE GARANTIE; sans même la garantie implicite de QUALITÉ MARCHANDE ou ADÉQUATION À UN USAGE PARTICULIER. Voir le GNU Licence publique générale moindre pour plus de détails.
 * Vous devriez avoir reçu une copie du GNU Lesser General Public Licence avec cette bibliothèque; sinon, écrivez au logiciel libre Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 États-Unis
*/

    // Envoi de l'entête HTML
    echo "<h1>Générateur de QR Code pour envoyer un email</h1><hr/>";
    
    //définissez le repertoire temp sur un emplacement accessible en écriture, un emplacement pour les fichiers PNG générés
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //préfixe d'emplacement PNG
    $PNG_WEB_DIR = 'temp/';

    // suppression des anciens png
    $rep=opendir($PNG_WEB_DIR);
    while($file = readdir($rep)){
    	if($file != '..' && $file !='.' && $file !='' && $file!='.htaccess'){
    		unlink($PNG_WEB_DIR.$file);
	    }
    }
    closedir($rep);

    // La librairie QR
    include "phpqrcode/qrlib.php";    
    
    //bien sûr, nous avons besoin de droits pour créer temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
     
    $filename = $PNG_TEMP_DIR.'test.png';
    
    //traitement de la saisie du formulaire 
    //n'oubliez pas de ''désinfecter'' l'entrée utilisateur dans une solution réelle !!!

    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 20);


    if (isset($_REQUEST['data'])) { 
    
        //c'est très important !
        if (trim($_REQUEST['data']) == '')
            die('Données inexistantes ! <a href="index.php">retour</a>');
            
        // Données utilisateur
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png('mailto:'.$_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    } else { 
    	   
        //Données par défaut
        QRcode::png('Votre QR-Code', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    }    
        
    //affichage du fichier généré
    echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><h5>Prenez en photo ce qr-code</h5><hr/>';
    
    //Construction du formulaire
    echo '<form action="mailextend.php" method="post">
        <b>Données :&nbsp;</b></br><input type="text" name="data" size="50" >&nbsp;Entrez le mail ici pour générer votre QRCode</br></br>
        <b>Qualité :&nbsp;</b></br><select name="level">
            <option value="L"'.(($errorCorrectionLevel=='L')?' selected':'').'>L - Petite</option>
            <option value="M"'.(($errorCorrectionLevel=='M')?' selected':'').'>M - Moyenne</option>
            <option value="Q"'.(($errorCorrectionLevel=='Q')?' selected':'').'>Q - Normale</option>
            <option value="H"'.(($errorCorrectionLevel=='H')?' selected':'').'>H - Haute</option>
        </select></br></br>
        <b>Taille :&nbsp;</b></br><select name="size">';
        
    for($i=1;$i<=20;$i++)
        echo '<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.$i.'</option>';
        
    echo '</select></br></br>
        <b><input type="submit" value="GENERER"></b></br></form><hr/>';
        
?>
<a href="index.php">Retour</a>
</body>
</html>



    