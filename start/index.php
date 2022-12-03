<?php
        define("ROOT", dirname(__DIR__));
        //define("PROOT", "/projects/phpGenerator/start/");
        define("PROOT", substr($_SERVER['PHP_SELF'],0,-9));

        require_once ROOT."/start/layout/header.php";      
        $url = $_GET["url"]; 
        $urlExplode  = explode("/", $url); 
        $file = (isset($urlExplode[0]) && $urlExplode[0] != "") ? $urlExplode[0]  : "1"; 
        if (file_exists(ROOT . "/start/content/{$file}.php")) {
            require_once ROOT . "/start/content/{$file}.php";
        } else {
            require_once ROOT . "/start//content/404.php";
        }


        require_once ROOT."/start/layout/footer.php"; 