<?
$includeDir = '/local/php_interface/include/';

if (file_exists($_SERVER['DOCUMENT_ROOT'].$includeDir.'events.php'))
    require_once($_SERVER['DOCUMENT_ROOT'].$includeDir.'events.php');

if (file_exists($_SERVER['DOCUMENT_ROOT'].$includeDir.'consts.php'))
    require_once($_SERVER['DOCUMENT_ROOT'].$includeDir.'consts.php');

CModule::AddAutoloadClasses (
    '',
    [
        'Events' => $includeDir . 'classes/Events.php',
    ]
);