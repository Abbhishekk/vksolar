<?php
    session_start();
    if(isset($_SESSION["user_id"])&& isset($_SESSION['username']) ) {
        if($_SESSION['role']){
        
        }
        else{
            header("Location: ../index.php");
            exit();
        }

    }
    else{ 
        
        header("Location: ../index.php");
        exit();
       

    }
    
?>