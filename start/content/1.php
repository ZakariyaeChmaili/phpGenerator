
    <center>
        <div class="container  mt-5">
            <div class="row">
                <div class="col col-12 col-md-8  col-lg-6 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h3>Server Information</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?=PROOT?>2" method="post">
                                <div class="form-group my-2">
                                    <label class="control-label label" for="">Enter Server Host</label>
                                    <input type="text" name="host" required id="" class="form-control" placeholder="" aria-describedby="helpId">
                                    <small id="helpId" class="text-muted">its either localhost or 127.0.0.1</small>
                                </div>
                                <div class="form-group my-2">
                                    <label class="control-label label" for="">Enter Server Port</label>
                                    <input type="text" name="port" required id="" class="form-control" placeholder="" aria-describedby="helpId">
                                    <small id="helpId" class="text-muted">default port is 3306</small>
                                </div>
                                <div class="form-group my-2">
                                    <label class="control-label label" for="">Enter UserNAme of phpMyAdmin</label>
                                    <input type="text" name="user" required id="" class="form-control" placeholder="" aria-describedby="helpId">
                                    <small id="helpId" class="text-muted">default is root</small>
                                </div>
                                <div class="form-group my-2">
                                    <label class="control-label label" for="">Enter Password of phpMyAdmin</label>
                                    <input type="text" name="password" id="" class="form-control" placeholder="" aria-describedby="helpId">
                                </div> 
                                <div class="card-footer">
                                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </center>




<!-- Initialize the plugin: -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#example-getting-started').multiselect();
    });
</script>






