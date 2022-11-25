<?php
        define("ROOT", dirname(__FILE__));
        define("PROOT", "/projects/phpGenerator/start/");
        require_once ROOT."/layout/header.php";      
        $url = $_GET["url"]; 
        $urlExplode  = explode("/", $url); 
        $file = (isset($urlExplode[0]) && $urlExplode[0] != "") ? $urlExplode[0]  : "1"; 
        if (file_exists(ROOT . "/content/{$file}.php")) {
            require_once ROOT . "/content/{$file}.php";
        } else {
            require_once ROOT . "/content/404.php";
        }


        require_once ROOT."/layout/footer.php"; 