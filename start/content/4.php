<?php

if (!empty($_POST['port'])) {
    $host = $_POST['host'];
    $port = $_POST['port'];
    $user = $_POST['user'];
    $password = $_POST['password'];
    $db = $_POST['db'];
    $appName = $_POST['appName'];
    $tables = $_POST['tables'];
    $frameWork = $_POST['frameWork'];
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



    if (!$conn) {
        die("Connection failed");
    }
    if ($frameWork == 'api') {
        require_once '4.api.php';

    }
    else if($frameWork == 'fullStack')
    require_once '4.fullStack.php';

}
?>
