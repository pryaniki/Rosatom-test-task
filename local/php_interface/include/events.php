<?php
use Bitrix\Main\EventManager;
use Events;

$eventManager = EventManager::getInstance();
// ex2-51
$eventManager->addEventHandler(
    "main",
    "OnBeforeEventAdd",
    [Events::class, "OnBeforeEventAddHandler"]
);


