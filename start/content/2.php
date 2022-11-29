<?php
if (!empty($_POST['port'])) {
    $host = $_POST['host'];
    $port = $_POST['port'];
    $user = $_POST['user'];
    $password = $_POST['password'];
    try {
        $conn = new PDO("mysql:host=$host;port=$port", $user, $password);
        if ($conn) {
            $databases = $conn->query("SHOW DATABASES WHERE `Database` NOT IN ('mysql', 'performance_schema', 'sys','information_schema ','phpmyadmin')")->fetchAll(PDO::FETCH_OBJ);
?>

            <center>
                <div class="container ">
                    <div class="row">
                        <div class="col col-12 col-md-8  col-lg-6 mx-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Server Information</h3>
                                </div>
                                <div class="card-body">
                                    <form action="<?= PROOT ?>3" method="post">
                                        <div class="form-group">
                                            <label class="control-label label" for="">Select A DataBase</label>
                                            <select required class="form-select" name="db" id="">
                                                <option disabled selected>Select one</option>
                                                <?php foreach ($databases as $database) : ?>
                                                    <option value="<?php echo $database->Database; ?>"><?php echo $database->Database; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group my-2">
                                            <label class="control-label label" for="">Enter The Name Of Your App</label>
                                            <input required type="text" name="appName" id="" class="form-control" placeholder="" aria-describedby="helpId">
                                            <small id="helpId" class="text-muted">name of the folder where your app will be stored</small>
                                        </div>
                                        <input type="text" value="<?= $_POST['user'] ?>" name="user" hidden>
                                        <input type="text" value="<?= $_POST['port'] ?>" name="port" hidden>
                                        <input type="text" value="<?= $_POST['host'] ?>" name="host" hidden>
                                        <input type="text" value="<?= $_POST['password'] ?>" name="password" hidden>
                                        <div class="card-footer">
                                            <button type="submit" name="submit" class="btn btn-warning">Submit</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </center>
        <?php
        } else {
            ?>
            <div class="container">
                <div class="row ">
                    <div class="alert alert-danger text-center">
                        <h1>:(</h1>
                        <script>
                            function backButton() {
                                window.history.back();
                            }
                        </script>
                        <p class="display-6">
                            Connection Faild Check Your Server
                        </p>
                        <button class="btn btn-lg btn-info" onclick="backButton()">Return!</button>
                    </div>
                </div>
            </div>
    <?php
        }
    } catch (PDOException $e) {
        ?>
        <div class="container">
            <div class="row ">
                <div class="alert alert-warning text-center">
                    <h1>:(</h1>
                    <script>
                        function backButton() {
                            window.history.back();
                        }
                    </script>
                    <p class="display-6">
                        <?php echo $e->getMessage(); ?>
                    </p>
                    <button class="btn btn-lg btn-info" onclick="backButton()">Return!</button>
                </div>
            </div>
        </div>
<?php
    }
}



?>