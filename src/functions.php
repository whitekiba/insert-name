<?php
function get_installation_path() {
    $path = dirname($_SERVER["SCRIPT_NAME"]);

    if (substr($path, -1) != '/') {
        $path = $path."/";
    }

    return $path;
}