<?php
	require "settings.php";
    initSession();
    
    destroySession();
        
    $a = "/";
    if (safeValue($_REQUEST, "a") == 1) {
        $a .= "AdminLogin";
    }
            
    header('location: '.domainName().$a);
?>