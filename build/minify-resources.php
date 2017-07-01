<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Build;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

require_once __DIR__ . '/../vendor/autoload.php';

$cssMinifier = new CSS(__DIR__ . '/../public/css/main.css');
$cssMinifier->add(__DIR__ . '/../public/css/themes\default.css');

$cssMinifier->minify(__DIR__ . '/../public/css/main.min.css');

(new JS(__DIR__ . '/../public/js/main.js'))->minify(__DIR__ . '/../public/js/main.min.js');
