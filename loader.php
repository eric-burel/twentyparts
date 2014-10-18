<?php

/**
 * Include necessary files, and setting class autoloader
 *
 * @copyright  Copyright 2014 - MidichlorianPHP and contributors
 * @author     NAYRAND Jérémie (dreadlokeur) <dreadlokeur@gmail.com>
 * @version    1.0.1dev2
 * @license    GNU General Public License 3 http://www.gnu.org/licenses/gpl.html
 * @package    MidichloriansPHP
 */
use framework\Autoloader;

// Checking
if (!version_compare(PHP_VERSION, '5.4.0', '>='))
    throw new \Exception('You must have at least PHP 5.4.0');

// Include neccesary files
require_once 'paths.php';
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'Classes.trait.php';
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'Directories.trait.php';
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'Namespaces.trait.php';
require_once PATH_FRAMEWORK . 'Autoloader.class.php';

// Autoloader configuration
$autoloader = new Autoloader();
$autoloader->setAutoloadExtensions(array(
    'class.php',
    'abstract.php',
    'final.php',
    'interface.php',
    'trait.php',
    'php')
);
$autoloader->addNamespaces(array(
    'framework' => PATH_FRAMEWORK,
    'libs' => PATH_LIBS,
    'controllers' => PATH_CONTROLLERS,
    'models' => PATH_MODELS)
);

// Include autoloaders adaptaters
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'IAdaptater.interface.php';
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'adaptaters' . DS . 'Finder.class.php';
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'adaptaters' . DS . 'Cache.class.php';
require_once PATH_FRAMEWORK . 'autoloader' . DS . 'adaptaters' . DS . 'Includer.class.php';
$autoloader->registerAutoloaders(array('Finder', 'Cache', 'Includer'));
?>
