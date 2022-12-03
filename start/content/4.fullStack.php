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
    array_shift($urlExplode); 
    $action = (isset($urlExplode[0]) && $urlExplode[0] != "") ? $urlExplode[0] : "index";
    array_shift($urlExplode); 
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

    abstract class  Model {
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


    // making the Main Controller
    $content = '<?php
    class Controller {
        public $view;
        function __construct()
        {
            $this->view = new View();

        }
    }';
    file_put_contents($path . "/core/Controller.php", $content);

    //making Request class
    $content = '<?php

    class Request{
        private static $request;
        public static function get(){
            self::$request =new stdClass();
            if(!empty($_POST)){
                foreach($_POST as $key=>$value){
                    self::$request->$key=$value;
                }
            }
            elseif(!empty($_GET)){
                foreach($_GET as $key=>$value){
                    self::$request->$key=$value;
                }
            }
            else
                self::$request=null;
            return self::$request;
        }
    }';
    file_put_contents($path . "/core/Request.php", $content);

    //making the View class
    $content = '<?php

    class View{
        //head and body ... attritbute are variables to stock the html content to we can display it later on in the layout 
        protected $head,$body,$nav,$outputBuffer;
        public function __construct()
        {
            # code...
        }
    
        public function render($viewName, $data=null) //view is the method that will charge the view file with the layout 
        {   

                include_once ROOT."/src/views/$viewName.php";
                include ROOT."/src/layouts/layout.php"; //remember layout is the one that will display the content of the view
        }

        public function redirect($viewName)
        {
            header("Location: ".PROOT."".$viewName);
        }

        public function content($type) //methode that we will use to display those (head,body...) attribute that contains html contents in the layout
        {
            switch ($type) {
                case "head":
                    return $this->head;
                case "body":
                    return $this->body;
                case "nav":
                    return $this->nav;
                default:
                    return false;
            }
        }

        public function start($type) //methode that will record (save) any html scripts in the buffer after it launch
        {
            $this->outputBuffer = $type;
            ob_start();
        }

        public function end()  //methode that will save buffer into a variable and then clean it
        {
            switch ($this->outputBuffer) {
                case "head":
                    $this->head=ob_get_clean();
                    break;
                case "body":
                    $this->body=ob_get_clean();
                    break;
                case "nav":
                    $this->nav=ob_get_clean();
                    break;
                
                default:
                die("you must first run the start method");
                    break;
            }
        }

    }';

    file_put_contents($path . "/core/View.php", $content);

    //making the public folder
    mkdir($path . "/public");
    mkdir($path . "/public/css");
    touch($path . "/public/css/style.css");
    $content = '
    body{
        font-family: "Courier New", Courier, monospace;
    }
    .form-control:focus {
        border-color: #ecd527;
        box-shadow: 0 0 0 0.2rem rgb(238, 229, 53);
    } 
    ';

    file_put_contents($path . "/public/css/style.css", $content);
    mkdir($path . "/public/js");
    touch($path . "/public/js/script.js");
    mkdir($path . "/public/img");
    $url = ROOT . "/start/content/public/imgs/appBack.jpg";
    $content = file_get_contents($url);
    file_put_contents($path . "/public/img/backGround.jpg", $content);
    file_put_contents($path . "/public/css/bootstrap.css", file_get_contents(ROOT . "/start/content/public/css/style1.css"));
    file_put_contents($path . "/public/js/jquery.js", file_get_contents(ROOT . "/start/content/public/js/jquery.js"));

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
    class HomeController extends Controller{
        public function index(){
            $this->view->render("index");
        }
    }';
    fwrite($file, $content);
    fclose($file);
    foreach ($tables as $table) {
        $path = '../' . $appName . '/src/Controllers/' . $table . 'Controller.php';
        $file = fopen($path, 'w');
        $content = '<?php
        class ' . $table . 'Controller extends Controller{
            public function index(){
            $' . $table . ' = new ' . $table . '();
            $' . $table . 'List = $' . $table . '->all();
            $this->view->render("' . $table . '/' . $table . 'List",$' . $table . 'List);
            
            }

            public function updateForm($id){
                $' . $table . ' = new ' . $table . '();
                $' . $table . ' = $' . $table . '->find($id);
                $this->view->render("' . $table . '/' . $table . 'Form",$' . $table . ');
            }



            public function addForm()
        {
            $this->view->render("' . $table . '/' . $table . 'Form");
        }

            public function update(){
                $request = Request::get();
                $' . $table . ' = new ' . $table . '();';
        foreach ($data[$table] as $key => $value) {
            $content .= '
                        $' . $table . '->' . $key . ' = $request->' . $key . ';';
        }
        $content .= '
                $' . $table . '->save();
                $this->view->redirect("' . $table . '");
            }

            public function add(){
                $request = Request::get();
                $' . $table . ' = new ' . $table . '();';
        foreach ($data[$table] as $key => $value) {
            if ($key != 'id') {
                $content .= '
                        $' . $table . '->' . $key . ' = $request->' . $key . ';';
            }
        }
        $content .= '
                $' . $table . '->save();
                $this->view->redirect("' . $table . '");
            }

            public function delete($id)
        {
            $' . $table . ' = new ' . $table . '();
            $' . $table . '->id = $id;
            $' . $table . '->delete();
            $this->view->redirect("' . $table . '");
        }
        }';
        file_put_contents($path, $content);
    }
    //making the layout folder

    mkdir("../" . $appName . "/src/layouts");
    //making the layout
    $content = '

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= PROOT ?>public/css/icons.css">
        <script src="<?= PROOT ?>public/js/jquery.js" ></script>    
        <link rel="stylesheet" type="text/css" href="<?= PROOT ?>public/css/bootstrap.css"  >
        <link rel="stylesheet" href="<?= PROOT ?>public/css/style.css">

    <title>Zak7killer</title>
    </head>

    <body>

    <nav class="nav justify-content-center my-3 h1">
        <a style="font-size:3rem; font-weight:900 ;text-decoration: none;" class="  btn btn-outline-warning border-0  w-50 border-warning" href="
            <?=PROOT?>">Home</a>
    </nav>



        <?= $this->content("head");?>


        <?= $this->content("body");?>


        <script src="<?=PROOT?>public/js/script.js"></script>
    </body>

    </html>

    ';
    file_put_contents("../" . $appName . "/src/layouts/layout.php", $content);

    //making the views folder
    $path = '../' . $appName . '/src/views';
    mkdir($path);
    //making the views
    foreach ($tables as $table)
        mkdir($path . '/' . $table);
    //making list view
    foreach ($tables as $table) {
        $content = '<?php $this->start("head") ?>

        <div class="container my-5">
            <div class="row text-center">
                    <div class="bg-warning rounded col col-sm-8 col-md-6  mx-auto alert-warning display-4 text-uppercase">' . $table . '</div>
            </div>
        </div>
        <?php $this->end()?>
        
        <?php $this->start("body") ?>
        
        
        <div class="row justify-content-center text-center h5">
            <div class="col-12 col-md-4">
                <a href="<?=PROOT?>' . $table . '/addForm" class="btn btn-outline-warning">Add new ' . $table . ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
              </svg></a>
            </div>
        </div>
        
        
        <div class="row justify-content-center text-center my-5 ">
            <div class="col-auto col-md-10">
            <?php
        
        if(isset($data)){
            ?>
        <table class="table table-striped table-inverse table-responsive">
            <thead class="thead-inverse bg-warning">
                <tr>';
        foreach ($data[$table] as $key => $value) {
            $content .= '

                    <th>' . $key . '</th>';
        }
        $content .= '
        <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($data as $item){ ?>
                <tr>
                ';
        foreach ($data[$table] as $key => $value) {
            $content .= '
                    <td ><?= $item->' . $key . ' ?></td>';
        }
        $content .= '
                    <td>
                    <div  class="d-flex justify-content-evenly ">
                        <a class="btn btn-outline-dark mx-2  my-1  " href="<?=PROOT?>' . $table . '/updateForm/<?=$item->id?>" class="mx-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                        <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001zm-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708l-1.585-1.585z"/>
                      </svg></a>
                        <a class="btn btn-outline-dark mx-2  my-1  " href="<?=PROOT?>' . $table . '/delete/<?=$item->id?>" class="mx-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                      </svg></i></a>
                    </div>
                    </td>
                </tr>
                <?php
                }?>
                </tbody>
            </table>
        <?php
        }else{
            ?>
        
        <center>
                <h1>List is empty</h1>
        </center>
        
        <?php
        }
            ?>
            </div>
        </div>
        
        <?php $this->end()?>';

        $path = '../' . $appName . '/src/views/' . $table . '/' . $table . 'List.php';
        file_put_contents($path, $content);
    }


    foreach ($tables as $table) {
        $content = '
    <?php $this->start("body") ?>

    <div class="container mt-5">
        <div class="row text-center">
            <div class="col-12 mx-auto font-weight-bold h2 text-warning border-bottom border-2 border-warning w-50">
                <?= !isset($data) ? "Add Form" : "Modify Form" ?>
            </div>
        </div>
        


        <form action="<?=PROOT?>' . $table . '/<?= !isset($data) ? "add" : "update" ?>" method="post">
        <div class="row justify-content-center mt-5">
                <div class="col-8">
                <div class="card">
                <div class="card-body">';
        foreach ($data[$table] as $key => $value) {
            if ($key != 'id') {

                $content .= '
                        <div class="form-group my-3">
                        <label for="">' . $key . '</label>
                        <input type="text" name="' . $key . '" id="" value="<?php
                        if(isset($data)){
                            echo $data->' . $key . ';
                        }
                            ?>" class="form-control" placeholder="" aria-describedby="helpId">
                        </div>';
            }
        }
        $content .= '
                    <input type="text" class="form-control" id="id" name="id" value="<?= !isset($data) ? "" : $data ->id ?>" hidden readonly>
                    </div>
                    <div class="card-footer text-center ">
                            <button type="submit" class="btn btn-warning btn-lg  w-50">Submit</button>
                        </div>
                </div>
                </div>
            </div>
            </form>
    </div>
    </div>

    <?php $this->end()?>
    ';
        $path = '../' . $appName . '/src/views/' . $table . '/' . $table . 'Form.php';
        file_put_contents($path, $content);
    }
    //making the views
    $path = '../' . $appName . '/src/views/index.php';
    $content = '
    <?php $this->start("body") ?>

    <style>
    body {
        background-image: url("<?=PROOT?>public/img/backGround.jpg");
        background-repeat: no-repeat;
        background-size: cover;
        font-family: "Courier New", Courier, monospace;
        font-weight: 400;
        padding-bottom: 120px;

    }
    </style>



    <div class="container">
        <div class="row">
            <div style="margin-top:10% ;" class="alert col-lg-6 col-md-8 col-sm-12 mx-auto alert-dark text-center">
                <h1>Full-Stack</h1>
                <p class="display-6">Welcome To Your App ♥</p>
                <select id="tableSelect" class="form-select" aria-label="Default select example">
                    <option disabled selected>Choose Your Table</option>';
    foreach ($tables as $table) {
        $content .= '
                    <option value="' . $table . '"><a href="<?=PROOT?>' . $table . '">' . $table . '</a></option>';
    }
    $content .= '
                </select>
                <br>
                <br>
                <a  id="table" >Select The Table First</a>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#tableSelect").change(function(){
                $("#table").html($("#tableSelect").val()).addClass("btn btn-lg btn-outline-dark").attr("href","<?=PROOT?>"+$("#tableSelect").val());
            });
        });


    </script>

    <!-- footer -->

    <footer class="footer mt-auto py-3   fixed-bottom text-center">

        <span style="font-size:1.2rem ; font-weight:200 ;" class="text-light">© 2022 Made with Zakariyae♥.</span>

    </footer>
    <?php $this->end() ?>';
    file_put_contents($path, $content);

    ?>

    <div class="container">
        <div class="row">
            <div style="margin-top:10% ;" class="alert alert-info  text-center">
                <h1>Full-Stack</h1>
                <p class="display-6">Your Full-Stack Application has been ctreated successfully check your directory</p>
                <a href="../<?= $appName ?>" class="btn  btn-lg btn-outline-dark">Your App</a>
            </div>
        </div>
    </div>