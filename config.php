<?php

    $cd = getcwd();

    chdir(__DIR__);
    require('config.default.php');
    chdir($cd);
