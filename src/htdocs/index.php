<?php
// Copyright 1999-2021. Plesk International GmbH.

$moduleId = basename(dirname(__FILE__));

pm_Context::init($moduleId);

$application = new pm_Application();
$application->run();
