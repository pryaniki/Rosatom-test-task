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
// ex2-94
$eventManager->addEventHandler(
    "main",
    "OnBeforeProlog",
    [Events::class, "OnBeforePrologHandler"]
);
// ex2-107
$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementAdd",
    [Events::class, "clearIblockCache"]
);
$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    [Events::class, "clearIblockCache"]
);
$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementDelete",
    [Events::class, "clearIblockCache"]
);