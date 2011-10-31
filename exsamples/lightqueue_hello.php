#!/usr/bin/env php
<?php
set_include_path(realpath(dirname(__FILE__).'/../lib').PATH_SEPARATOR.get_include_path());
require_once 'LightQueue.php';
require_once 'LightQueue/Manager.php';
require_once 'LightQueue/SampleTask/HelloTask.php';

LightQueue_Manager::setup('mysql:host=127.0.0.1;dbname=lightqueue', 'root', 'root');

$lighQueue = new LightQueue();
$task = new LightQueue_SampleTask_HelloTask();

$lighQueue->addTask($task, 'sample');
