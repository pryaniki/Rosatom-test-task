<?php
use Bitrix\Main\EventManager;
use Events;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    "main",
    "OnBeforeEventAdd",
    [Events::class, "OnBeforeEventAddHandler"]
);