<?php

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

$pathsConfig = FCPATH . '../app/Config/Paths.php';

require $pathsConfig;

$paths = new Config\Paths();

require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));
