<?php

if (!empty($_POST['db'])) {
    $db = $_POST['db'];
    $port = $_POST['port'];
    $host = $_POST['host'];
    $user = $_POST['user'];
    $password = $_POST['password'];
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
    $tables = $conn->query("show tables")->fetchAll(PDO::FETCH_OBJ);
    $key = "Tables_in_$db";
}




?>


    <center>
        <div class="container ">
            <div class="row">
                <div class="col col-12 col-md-8  col-lg-6 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h3>DATABASE</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?=PROOT?>4" method="post">
                                <div class="form-group my-3">
                                    <label class="control-label label" for="">Select Your Tables: </label>
                                    <select required class="selectpicker" name="tables[]" id="" multiple data-live-search="true">
                                        <?php foreach ($tables as $table) : ?>
                                            <option value="<?php echo $table->$key; ?>"><?php echo $table->$key; ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>
                                <div class="form-check">
                                    <h3>Choose Your FrameWork</h3>
                                    <div class="row justify-content-center my-4">
                                        <div class="col col-4">
                                            <input class="form-check-input" type="radio" value="api" id="flexCheckDefault" name="frameWork">
                                            <label class="form-check-label">
                                                API
                                            </label>
                                        </div>
                                        <div class="col col-4">
                                            <input class="form-check-input" type="radio" value="fullStack" id="flexCheckDefault" name="frameWork">
                                            <label class="form-check-label">
                                                FULL-STACK
                                            </label>
                                        </div>
                                    </div>

                                </div>
                                <input type="text" value="<?= $_POST['user'] ?>" name="user" hidden>
                                <input type="text" value="<?= $_POST['port'] ?>" name="port" hidden>
                                <input type="text" value="<?= $_POST['host'] ?>" name="host" hidden>
                                <input type="text" value="<?= $_POST['password'] ?>" name="password" hidden>
                                <input type="text" value="<?= $_POST['db'] ?>" name="db" hidden>
                                <input type="text" value="<?= $_POST['appName'] ?>" name="appName" hidden>


                                <div class="card-footer">
                                    <button type="submit" name="submit" class="btn btn-outline-dark">Submit</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </center>
