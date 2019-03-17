<form action="" method="post">
    <fieldset>
        <legend>Create location</legend>
        <div class="control-group<?php echo ($data["location_error"] != "" ? " error" : ""); ?>">
            <label for="location">Location</label>
            <div class="controls">
                <input type="text" placeholder="Type location name" id="location" name="name" value="<?php echo $data["name"]; ?>">
                <span class="help-block">Location name</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Gem</button>
        </div>
    </fieldset>
</form>
