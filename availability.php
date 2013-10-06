<?php
/**
 * @author Ivan Matveev <Redjiks@gmail.com>.
 */

require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->register();
$loader->registerNamespace('Symfony', __DIR__.'/vendor');
$loader->registerNamespace('Availability', __DIR__);

$console = new \Symfony\Component\Console\Application('Availability application','0.1');
$console->addCommands(array(new \Availability\Commands\CheckCommand(), new \Availability\Commands\ParseCommand()));
$console->run();
