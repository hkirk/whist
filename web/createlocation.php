<?php

require("lib.php");

switch (request_method()) {
    case "POST":
        $name = check_get_string($_REQUEST, "name");
        check_input();
        $data =  ["name" => $name];
        if ($name == NULL || $name == "") {
            $input_error = true;
            $data["location_error"] = "name";
        }

        if ($input_error) {
            render_page("Create location", "Create location", "createlocation", $data);
            break;
        }

        db_create_location($name);
        redirect_to_root();

        break;

    case "GET":
        $data = [];
        render_page("Create location", "Create location", "createlocation", $data);
    break;
}
