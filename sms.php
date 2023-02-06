<html>
<head>
</head>
<body background = "yellow.gif">
<?php    
/*
 * Encodeur PHP QR Code
 * PHP QR Code est distribu� sous LGPL 3
 * Cette biblioth�que est un logiciel libre; vous pouvez le redistribuer et / ou le modifier selon les termes du GNU Lesser General Public Licence publi�e par la Free Software Foundation; Soit version 3 de la licence ou toute version ult�rieure.
 * Cette biblioth�que est distribu�e dans l'espoir qu'elle vous sera utile,mais SANS AUCUNE GARANTIE; sans m�me la garantie implicite de QUALIT� MARCHANDE ou AD�QUATION � UN USAGE PARTICULIER. Voir le GNU Licence publique g�n�rale moindre pour plus de d�tails.
 * Vous devriez avoir re�u une copie du GNU Lesser General Public Licence avec cette biblioth�que; sinon, �crivez au logiciel libre Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 �tats-Unis
*/
    // La librairie QR
    include "phpqrcode/qrlib.php";    
       
    // Envoi de l'ent�te HTML
    echo "<h1>G�n�rateur de QR Code avec du texte et un logo en son milieu</h1><hr/>";

    //d�finissez le repertoire temp sur un emplacement accessible en �criture, o� un emplacement pour les fichiers PNG g�n�r�s
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;

    //pr�fixe d'emplacement PNG et JPG
    $PNG_WEB_DIR = 'temp/';
    $JPG_WEB_DIR = 'upload/';

    //nous avons besoin de droits pour cr�er temp dir
    if (!file_exists($PNG_WEB_DIR))
        mkdir($PNG_WEB_DIR);

    if (!file_exists($JPG_WEB_DIR))
        mkdir($JPG_WEB_DIR);
    
    // suppression des anciens png
    $rep=opendir($PNG_WEB_DIR);
    while($file = readdir($rep)){
    	if($file != '..' && $file !='.' && $file !='' && $file!='.htaccess'){
    		unlink($PNG_WEB_DIR.$file);
	    }
    }
    closedir($rep);

    // suppression des anciens jpg
    $rep=opendir($JPG_WEB_DIR);
    while($file = readdir($rep)){
    	if($file != '..' && $file !='.' && $file !='' && $file!='.htaccess'){
    		unlink($JPG_WEB_DIR.$file);
	    }
    }
    closedir($rep);
    
    //nous avons besoin de droits pour cr�er temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
        $filename = $PNG_TEMP_DIR.'test.png';
    
    //traitement de la saisie du formulaire 
    //n'oubliez pas de ''d�sinfecter'' l'entr�e utilisateur dans une solution r�elle !!!

    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 20);

    if (isset($_REQUEST['data'])) { 
    
        //c'est tr�s important !
        if (trim($_REQUEST['data']) == '')
            die('Donn�es inexistantes ! <a href="index.php">retour</a>');
            
        // Donn�es utilisateur
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png('sms:'.$_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    } else { 
    	   
        //Donn�es par d�faut
        QRcode::png('Votre QR-Code', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    }    
        
    //affichage du fichier g�n�r�
    echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><h5>Prenez en photo ce qr-code</h5><hr/>'; 
    
    //Construction du formulaire
    echo '<form action="sms.php" method="post">
        <b>Donn�es :&nbsp;</b></br><input type="text" name="data" size="50" >&nbsp;Entrez votre num�ro de t�l�phone SMS (ex : +33699999999 ou 0699999999) ici pour g�n�rer votre QRCode</br></br>
        <b>Qualit� :&nbsp;</b></br><select name="level">
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


    