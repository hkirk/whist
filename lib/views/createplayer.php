<form action="" method="post">
    <fieldset>
        <legend>Create player</legend>
        <div class="control-group<?php echo ($data["name_error"] != "" ? " error" : ""); ?>">
            <label for="name">Name</label>
            <div class="controls">
                <input type="text" placeholder="Type players name" id="name" name="name" value="<?php echo $data["name"]; ?>">
                <span class="help-block">Player name</span>
            </div>
        </div>

        <div class="control-group<?php echo ($data["nickname_error"] != "" ? " error" : ""); ?>">
            <label for="nickname">Nick name</label>
            <div class="controls">
                <input type="text" placeholder="Type players nickname" id="nickname" name="nickname" value="<?php echo $data["nickname"]; ?>">
                <span class="help-block">Player nickname</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
    </fieldset>
</form>
