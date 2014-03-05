<?php
require("lib.php");

switch (request_method()) {
    case "POST":
        $name = check_get_string($_REQUEST, "name");
        $nickname = check_get_string($_REQUEST, "nickname");
        check_input();
        $data =  ["name" => $name, "nickname" => $nickname];
        if ($name == NULL || $name == "") {
            $input_error = true;
            $data["name_error"] = "name";
        }

        if ($nickname == NULL || $nickname == "") {
            $input_error = true;
            $data["nickname_error"] = "nickname";
        }

        if ($input_error) {
            render_page("Create Player", "Create player", "createplayer", $data);
            break;
        }

        db_create_player($name, $nickname);
        redirect_to_root();

        break;
    case "GET":
        $data = [];
        render_page("Create player", "Create player", "createplayer", $data);
        break;

    default:
        printf("Unknown: %s", request_method());
}
