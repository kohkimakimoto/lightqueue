#!/usr/bin/env php
<?php
set_include_path(realpath(dirname(__FILE__).'/../lib').PATH_SEPARATOR.get_include_path());

require_once 'LightQueue/Daemon.php';
$daemon = new LightQueue_Daemon(
    realpath(dirname(__FILE__).'/../config/lightqueued.conf')
    );
$daemon->run();
