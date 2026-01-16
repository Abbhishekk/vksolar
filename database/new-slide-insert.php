<?php 

require('../database/connection.php');
require('../database/function.php');

$conn = new connection();
$db = $conn->my_connect();

$fun = new fun($db);

$SlideTitle = $_POST['slide_title'];
$SlideContent = $_POST['slide_content'];

// for bg images
$imgName = basename($_FILES['background_image']['name']);
$targetDir = "../database/db_images/";
$img_location = $targetDir.$imgName;
$tempName = $_FILES['background_image']['tmp_name'];
$img_type = pathinfo($img_location,PATHINFO_EXTENSION);

// for logo images
$logoName = basename($_FILES['slide_logo']['name']);
$logoTargetDir = "../database/db_images/";
$Logo_location = $logoTargetDir.$logoName;
$logoTempName = $_FILES['slide_logo']['tmp_name'];
$logo_type = pathinfo($Logo_location,PATHINFO_EXTENSION);

$bg_image =  move_uploaded_file($tempName,$img_location);
$logo_image =  move_uploaded_file($logoTempName,$Logo_location);

if($bg_image && $logo_image)
{
    $result = $fun->carouselInsert($SlideTitle,$SlideContent,$imgName,$img_location,$img_type,$logoName,$Logo_location,$logo_type);

    if($result)
    {
        header('location: ../Dashbord/pages/carousel.php');
    }
    else
    {
        echo "Something went wrong";
    }
}
else
{
    echo "Not Inserted Data";
}


?>