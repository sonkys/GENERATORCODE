<html>
<head>
</head>
<body background="yellow.gif">
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

        // V�rifier si le fichier a �t� soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){
        // V�rifie si le fichier a �t� upload� sans erreur.
           if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0){
              $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
              $photo = $_FILES["photo"]["name"];
              $filetype = $_FILES["photo"]["type"];
              $filesize = $_FILES["photo"]["size"];

             // V�rifie l'extension du fichier
             $ext = pathinfo($photo, PATHINFO_EXTENSION);
             if(!array_key_exists($ext, $allowed)) die("Erreur : Veuillez s�lectionner un format de fichier valide.");

             // V�rifie la taille du fichier - 2Mo maximum
             $maxsize = 2 * 1024 * 1024;
             if($filesize > $maxsize) die("Error: La taille du fichier est sup�rieure � la limite autoris�e.");

             // V�rifie le type MIME du fichier
             if(in_array($filetype, $allowed)){
               // V�rifie si le fichier existe avant de le t�l�charger.
                 if(file_exists("upload/" . $_FILES["photo"]["name"])){
                   // echo $_FILES["photo"]["name"] . " existe d�j�.";
                 } else{
                   move_uploaded_file($_FILES["photo"]["tmp_name"], "upload/" . $_FILES["photo"]["name"]);
                   echo "Votre fichier a �t� t�l�charg� avec succ�s.";
                 } 
              } else{
                 echo "Error: Il y a eu un probl�me de t�l�chargement de votre fichier. Veuillez r�essayer."; 
              }
          } else{
            echo "Error: " . $_FILES["photo"]["error"];
          }
      }
        // Donn�es utilisateur
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);  // On cr�e notre QR Code

       //affichage du fichier g�n�r�
       echo "QR-Code g�n�r� :<br/><br/><hr/><br/>";

       // Debut du dessin du LOGO dans QRCODE
       $QR = imagecreatefrompng($filename);

       // d�marrage du dessin de l'image dans QR CODE
       $logo = imagecreatefromstring(file_get_contents('upload/'.$photo));

       // Fix la transparence du fond-�cran
       imagecolortransparent($logo , imagecolorallocatealpha($logo , 0, 0, 0, 127));
       imagealphablending($logo , false);
       imagesavealpha($logo , true);

       $QR_width = imagesx($QR);
       $QR_height = imagesy($QR);

       $logo_width = imagesx($logo);
       $logo_height = imagesy($logo);

       // Mise � l'�chelle du logo � mettre dans le QR Code
       $logo_qr_width = $QR_width/3;
       $scale = $logo_width/$logo_qr_width;
       $logo_qr_height = $logo_height/$scale;

       imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

       // Save du QR code avec son logo
       imagepng($QR,$filename);

       // sortie �cran
       // echo '<img src="'.$filename.'" />';
       echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><h5>Prenez en photo ce qr-code</h5><hr/>';
       // fin de cr�ation du QR Code avec logo
    }else { 
     //Donn�es par d�faut
     QRcode::png('Votre QR-Code', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
     //affichage du fichier g�n�r�
     echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
  }
    //Construction du formulaire pour ce qrcode il n y a que la haute qualit� de valable
    echo '<form action="logo.php" method="post" enctype="multipart/form-data">
        <b>Donn�es :&nbsp;</b></br><textarea name="data" size="50" value="'.(isset($_REQUEST['data'])?htmlspecialchars($_REQUEST['data']):'Entrez vos donn�es ici pour g�n�rer votre QRCode').'" /></textarea></br></br>
        <b>Logo    :&nbsp;</b></br><input type="file" name="photo" id="fileUpload" accept="*.jpg,*.gif,*.png,*.JPG,*.GIF,*.PNG"/>(en gris et 400x400 max)</br>
        <b>Qualit� :&nbsp;</b></br><select name="level">
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



    