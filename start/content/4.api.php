<?php
$data = [];
foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM $table");
    $data[$table] = $result->fetch(PDO::FETCH_OBJ);
}

//creating the folder
$path = '../' . $appName;
mkdir($path);
//making the index file and filling it 
touch($path . '/index.php');
$index = fopen($path . '/index.php', 'w');
$indexContent = '<?php
        define("ROOT", dirname(__FILE__));
        define("PROOT", substr($_SERVER["PHP_SELF"],0,-9));
        
    
        header("Access-Control-Allow-Origin: *"); //header that specify what urls have access to this api
        header("Content-Type: application/json"); //header that define what kind of body that will receive
        header("Access-Control-Allow-Methods: *"); //header that define what kind of methods allowed in this api
        header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods,Access-Control-Allow-Origin");   //header that allows all the headers above
        
        
        function autoLoad($className)  
        {
            if (file_exists(ROOT . "/core/$className.php")) {
                require_once ROOT . "/core/$className.php";
            } elseif (file_exists(ROOT . "/src/controllers/$className.php")) {
                require_once ROOT . "/src/controllers/$className.php";
            } elseif (file_exists(ROOT . "/src/models/$className.php")) {
                require_once ROOT . "/src/models/$className.php";
            }
        }
        spl_autoload_register("autoLoad"); 
        
        
        $url = $_GET["url"]; // get the link
        $urlExplode  = explode("/", $url); 
        $controller = (isset($urlExplode[0]) && $urlExplode[0] != "") ? $urlExplode[0]."Controller"  : "HomeController"; 
        array_shift($urlExplode); // we shift to the next part in the link (after the /)
        $action = (isset($urlExplode[0]) && $urlExplode[0] != "") ? $urlExplode[0] : "index";
        array_shift($urlExplode); // we shift to the next part in the link (after the /)
        $params =  $urlExplode;
        
        if (file_exists(ROOT . "/src/controllers/{$controller}.php")) {
            $controllerO = new $controller();
            if (method_exists($controllerO, $action)) {
                call_user_func_array([$controllerO, $action], $params);
            } else die("methode {$action} doesnt exist");
        } else die("controller {$controller} doesnt exist");
        ';
fwrite($index, $indexContent);
fclose($index);

//making the .htaccess file and filling it
touch($path . '/.htaccess');
$htaccess = fopen($path . '/.htaccess', 'w');
$htaccessContent = 'RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php?url=$1
        ';
fwrite($htaccess, $htaccessContent);
fclose($htaccess);

//making the .env file
touch($path . '/.env');
$env = fopen($path . '/.env', 'w');
$envContent = 'DB_CONNECTION=mysql
DB_HOST=' . $host . '
DB_PORT=' . $port . '
DB_DATABASE=' . $db . '
DB_USERNAME=' . $user . '
DB_PASSWORD=' . $password . '';
fwrite($env, $envContent);
fclose($env);





//making the core folder and filling it
mkdir($path . '/core');

//making the Connection class
touch($path . '/core/Connection.php');
$connection = fopen($path . '/core/Connection.php', 'w');
$connectionContent = '<?php
        class Connection
{
    private static $con = null;


    private function __construct()
    {
        $cnx = file(ROOT."/.env");
        $o = new stdClass();

        foreach ($cnx as $key => $v) {
            $key = explode("=", $v)[0];
            $o->$key = explode("=", $v)[1];
        }
        try{
            self::$con = new PDO(trim($o->DB_CONNECTION).":host=".trim($o->DB_HOST).";dbname=".trim($o->DB_DATABASE),trim($o->DB_USERNAME),trim($o->DB_PASSWORD));
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }

    public static function Connect(){
        if(self::$con == null){
           new Connection();
        } 

        return self::$con;
        
        
    }
}
        ';
fwrite($connection, $connectionContent);
fclose($connection);


//making the Model class
touch($path . '/core/Model.php');
$model = fopen($path . '/core/Model.php', 'w');
$modelContent = '<?php

    abstract class Model {
            protected static $pdo=null;
            public $id;

            function __construct()
            {
                self::$pdo = Connection::Connect();
            }
        
            public function save()
        {
            $keys = array_filter(array_keys((array)$this),function($key){return $key!="id";});
            $values = array_values(array_filter((array)$this,function($key){return $key!="id";},ARRAY_FILTER_USE_KEY));
            if(!isset($this->id)){
                $sql="insert into ".get_called_class()." (".implode(",",$keys).") values(";
                for($i=0;$i<count($values);$i++)
                $sql.="?,";
                $sql=substr($sql,0,-1).")";
            }else{
                $sql = "update ".get_called_class()." set ".implode("=?, ",$keys)."=? where id ={$this->id}";       
            }          
            $stm = self::$pdo->prepare($sql);
            return $stm->execute($values) ? true : ["error"=>$stm->errorInfo()];
            
        }
     
            public function delete(){
                $sql="delete from ".get_called_class()." where id={$this->id}";
                return self::$pdo->exec($sql) ? true : ["error"=>self::$pdo->errorInfo()];
                
            }
        
          
            public static function find($id){
                $class=get_called_class();
                $sql="select * from ".$class." where id=$id";
                $object = new $class();    
                $res=self::$pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            
                foreach($res as $key=>$value)
                    $object->$key=$value;    
                return $object ? $object : ["error"=>"not found"];
            }
            public static function all(){
                $class=get_called_class();
                $sql="select * from ".$class;
                new $class();
                $res=self::$pdo->query($sql)->fetchAll(PDO::FETCH_OBJ);
                return $res ? $res : null;
            }
                   
        
        }
        
';

fwrite($model, $modelContent);
fclose($model);


//making the src folder
$path = '../' . $appName . '/src';
mkdir($path);

//making the models folder
$path = '../' . $appName . '/src/models';
mkdir($path);

foreach ($tables as $table) {
    $path = '../' . $appName . '/src/models/' . $table . '.php';
    $file = fopen($path, 'w');
    $content = '<?php
            class ' . $table . ' extends Model{';
    file_put_contents($path, $content, FILE_APPEND);
    foreach ($data[$table] as $key => $value) {
        if ($key != 'id') {

            $content = '
                    public $' . $key . ';';
            file_put_contents($path, $content, FILE_APPEND);
        }
    }
    $content = '
                public function __construct(){
                    parent::__construct();
    }}';
    file_put_contents($path, $content, FILE_APPEND);
}

//making the controllers folder
$path = '../' . $appName . '/src/controllers';
mkdir($path);
//making the controllers
$path = '../' . $appName . '/src/Controllers/HomeController.php';
$file = fopen($path, 'w');
$content = '<?php
        class HomeController{
            public function index(){
                echo json_encode("API MADE BY ZAKAIRYAE CHMAILI");
                echo json_encode([
                    "Tables"=>[';
                    foreach($tables as $table){
                        $content.= "'$table',";
                    }
                    $content.='],
                    "GetAll"=>"tableName",
                    "Delete"=>"tableName/delete/id",
                    "Update"=>"tableName/update/id",
                    "Save"=>"tableName/add",
                    "Find"=>"tableName/get/id"]);
            }
        }';
fwrite($file, $content);
fclose($file);
foreach ($tables as $table) {
    $path = '../' . $appName . '/src/Controllers/' . $table . 'Controller.php';
    $file = fopen($path, 'w');
    $content = '<?php
            class ' . $table . 'Controller {
                public function index(){
                    $' . $table . ' = new ' . $table . '();
                    echo json_encode($' . $table . '->all());
                }
                
                public function add()
                {
                    $data = json_decode(file_get_contents("php://input"));
                    if ($data) {
                        $o = new ' . $table . '();';
    foreach ($data[$table] as $key => $value) {
        if ($key != 'id') {
            $content .= '
                        $o->' . $key . ' = $data->' . $key . ';';
        }
    }
    $content .= '
                        echo json_encode($o->save());
                    }
                }
                        
                
                public function get($id)
                                        {
                    $' . $table . ' = new ' . $table . '();
                    echo json_encode($' . $table . '->find($id));
                }
                public function update()
                  {   
                    $data = json_decode(file_get_contents("php://input"));
                    if ($data) {
                    $o = new ' . $table . '();';
    foreach ($data[$table] as $key => $value) {
        $content .= '
                    $o->' . $key . '=$data->' . $key . ';';
    }
    $content .= '
                    echo json_encode($o->save());
                }
             }
                public function delete($id)
                {
                    $' . $table . ' = new ' . $table . '();
                    $' . $table . '->id = $id;
                    echo json_encode($' . $table . '->delete());
                }
            
               
            }
            ';
    fwrite($file, $content);
    fclose($file);
}
?>
<div class="container">
    <div class="row">
        <div style="margin-top:10% ;" class="alert alert-success text-center">
            <h1>API</h1>
            <p class="display-6">Your API Application has been ctreated successfully check your directory</p>
            <a href="../<?= $appName ?>" class="btn  btn-lg btn-outline-dark">Your App</a>
        </div>
    </div>
</div>