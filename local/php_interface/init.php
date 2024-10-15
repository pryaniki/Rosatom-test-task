<?
$includeDir = '/local/php_interface/include/';

if (file_exists($_SERVER['DOCUMENT_ROOT'].$includeDir.'events.php'))
    require_once($_SERVER['DOCUMENT_ROOT'].$includeDir.'events.php');

CModule::AddAutoloadClasses (
    '',
    [
        'Events' => $includeDir . 'classes/Events.php',
    ]
);