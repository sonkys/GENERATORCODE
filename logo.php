<html>
<head>
</head>
<body background="yellow.gif">
<?php 
/*
 * Encodeur PHP QR Code
 * PHP QR Code est distribué sous LGPL 3
 * Cette bibliothèque est un logiciel libre; vous pouvez le redistribuer et / ou le modifier selon les termes du GNU Lesser General Public Licence publiée par la Free Software Foundation; Soit version 3 de la licence ou toute version ultérieure.
 * Cette bibliothèque est distribuée dans l'espoir qu'elle vous sera utile,mais SANS AUCUNE GARANTIE; sans même la garantie implicite de QUALITÉ MARCHANDE ou ADÉQUATION À UN USAGE PARTICULIER. Voir le GNU Licence publique générale moindre pour plus de détails.
 * Vous devriez avoir reçu une copie du GNU Lesser General Public Licence avec cette bibliothèque; sinon, écrivez au logiciel libre Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 États-Unis
*/   
    // La librairie QR
    include "phpqrcode/qrlib.php";    
       
    // Envoi de l'entête HTML
    echo "<h1>Générateur de QR Code avec du texte et un logo en son milieu</h1><hr/>";

    //définissez le repertoire temp sur un emplacement accessible en écriture, où un emplacement pour les fichiers PNG générés
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;

    //préfixe d'emplacement PNG et JPG
    $PNG_WEB_DIR = 'temp/';
    $JPG_WEB_DIR = 'upload/';

    //nous avons besoin de droits pour créer temp dir
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
    
    //nous avons besoin de droits pour créer temp dir
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

        // Vérifier si le fichier a été soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){
        // Vérifie si le fichier a été uploadé sans erreur.
           if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0){
              $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
              $photo = $_FILES["photo"]["name"];
              $filetype = $_FILES["photo"]["type"];
              $filesize = $_FILES["photo"]["size"];

             // Vérifie l'extension du fichier
             $ext = pathinfo($photo, PATHINFO_EXTENSION);
             if(!array_key_exists($ext, $allowed)) die("Erreur : Veuillez sélectionner un format de fichier valide.");

             // Vérifie la taille du fichier - 2Mo maximum
             $maxsize = 2 * 1024 * 1024;
             if($filesize > $maxsize) die("Error: La taille du fichier est supérieure à la limite autorisée.");

             // Vérifie le type MIME du fichier
             if(in_array($filetype, $allowed)){
               // Vérifie si le fichier existe avant de le télécharger.
                 if(file_exists("upload/" . $_FILES["photo"]["name"])){
                   // echo $_FILES["photo"]["name"] . " existe déjà.";
                 } else{
                   move_uploaded_file($_FILES["photo"]["tmp_name"], "upload/" . $_FILES["photo"]["name"]);
                   echo "Votre fichier a été téléchargé avec succès.";
                 } 
              } else{
                 echo "Error: Il y a eu un problème de téléchargement de votre fichier. Veuillez réessayer."; 
              }
          } else{
            echo "Error: " . $_FILES["photo"]["error"];
          }
      }
        // Données utilisateur
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);  // On crée notre QR Code

       //affichage du fichier généré
       echo "QR-Code généré :<br/><br/><hr/><br/>";

       // Debut du dessin du LOGO dans QRCODE
       $QR = imagecreatefrompng($filename);

       // démarrage du dessin de l'image dans QR CODE
       $logo = imagecreatefromstring(file_get_contents('upload/'.$photo));

       // Fix la transparence du fond-écran
       imagecolortransparent($logo , imagecolorallocatealpha($logo , 0, 0, 0, 127));
       imagealphablending($logo , false);
       imagesavealpha($logo , true);

       $QR_width = imagesx($QR);
       $QR_height = imagesy($QR);

       $logo_width = imagesx($logo);
       $logo_height = imagesy($logo);

       // Mise à l'échelle du logo à mettre dans le QR Code
       $logo_qr_width = $QR_width/3;
       $scale = $logo_width/$logo_qr_width;
       $logo_qr_height = $logo_height/$scale;

       imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

       // Save du QR code avec son logo
       imagepng($QR,$filename);

       // sortie écran
       // echo '<img src="'.$filename.'" />';
       echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><h5>Prenez en photo ce qr-code</h5><hr/>';
       // fin de création du QR Code avec logo
    }else { 
     //Données par défaut
     QRcode::png('Votre QR-Code', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
     //affichage du fichier généré
     echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
  }
    //Construction du formulaire pour ce qrcode il n y a que la haute qualité de valable
    echo '<form action="logo.php" method="post" enctype="multipart/form-data">
        <b>Données :&nbsp;</b></br><textarea name="data" size="50" value="'.(isset($_REQUEST['data'])?htmlspecialchars($_REQUEST['data']):'Entrez vos données ici pour générer votre QRCode').'" /></textarea></br></br>
        <b>Logo    :&nbsp;</b></br><input type="file" name="photo" id="fileUpload" accept="*.jpg,*.gif,*.png,*.JPG,*.GIF,*.PNG"/>(en gris et 400x400 max)</br>
        <b>Qualité :&nbsp;</b></br><select name="level">
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



    