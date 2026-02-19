<?php

namespace scc\Controllers;

abstract class BaseController
{
    public function view ()
    {
        require_once("../app/views/layouts/html_header.php");
        echo "teste";
        require_once("../app/views/layouts/html_footer.php");
    }

}