<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)  die();

use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


class Catalog extends CBitrixComponent
{
    private CurrentUser $user;
    private int $catalogIBId;
    private int $classifierIBId;
    private string $classifierCode;

    public function executeComponent()
    {
        $this->user = CurrentUser::get();

        try {
            $this->checkModules();
            $this->checkParams();
            $this->setProperties();
            $this->prepareData();

        } catch (SystemException $e) {
            if ($this->user->isAdmin()) {
                ShowError($e->getMessage());
            }
            return;
        }
    }
    protected function checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_IBLOCK_MODULE_NONE'));
        }
    }
    public function onPrepareComponentParams($params)
    {
        $params['PRODUCTS_IBLOCK_ID'] = !empty($params['PRODUCTS_IBLOCK_ID']) ? trim($params['PRODUCTS_IBLOCK_ID']) : '';
        $params['CLASS_IBLOCK_ID'] = !empty($params['CLASS_IBLOCK_ID']) ? trim($params['CLASS_IBLOCK_ID']) : 0;
        $params['PRODUCT_PROPERTY_CODE'] = !empty($params['PRODUCT_PROPERTY_CODE']) ? trim($params['PRODUCT_PROPERTY_CODE']) : '';
        $params['CACHE_GROUPS'] = $params['CACHE_GROUPS'] ?: 'N';
        $params['DETAIL_PAGE_URL_TEMPLATE'] = $params['DETAIL_PAGE_URL_TEMPLATE'] ?? '';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 36000000;

        return $params;
    }
    protected function checkParams(): void
    {
        if (!$this->arParams['PRODUCTS_IBLOCK_ID']) {
            throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_EMPTY_PRODUCTS_IBLOCK_ID'));
        }
        if (!$this->arParams['CLASS_IBLOCK_ID']) {
            throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_EMPTY_CLASS_IBLOCK_ID'));
        }
        if (!$this->arParams['PRODUCT_PROPERTY_CODE']) {
            throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_EMPTY_PRODUCT_PROPERTY_CODE'));
        }
    }
    protected function isExistIblock($iblockId): bool
    {
        $iblock = IblockTable::getList([
            'filter' => ['ID' => $iblockId],
            'limit' => 1,
        ])->fetch();

        return (bool)$iblock;
    }
    protected function isExistProperty($iblockId, $code): bool
    {
        $property = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $code,
            ],
            'select' => ['ID'],

        ])->fetch();

        return (bool)$property;
    }
    private function setProperties(): void
    {
        $this->catalogIBId = intval($this->arParams['PRODUCTS_IBLOCK_ID']);
        $this->classifierIBId = intval($this->arParams['CLASS_IBLOCK_ID']);
        $this->classifierCode = str_starts_with($this->arParams['PRODUCT_PROPERTY_CODE'], 'PROPERTY_')
            ?$this->arParams['PRODUCT_PROPERTY_CODE']:
            'PROPERTY_'.$this->arParams['PRODUCT_PROPERTY_CODE'];
    }
    protected function prepareData(): void
    {
        $userGroups = $this->user->getUserGroups();
        if ( $this->startResultCache(false, ($this->arParams['CACHE_GROUPS']==='N'? false : $userGroups)) ) {

            if (!$this->isExistIblock($this->arParams['PRODUCTS_IBLOCK_ID'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_IBLOCK', ['#ID#' => $this->arParams['PRODUCTS_IBLOCK_ID']]));
            }
            if (!$this->isExistIblock($this->arParams['CLASS_IBLOCK_ID'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_IBLOCK', ['#ID#' => $this->arParams['CLASS_IBLOCK_ID']]));
            }
            if (!$this->isExistProperty($this->arParams['PRODUCTS_IBLOCK_ID'], $this->arParams['PRODUCT_PROPERTY_CODE'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_PROPERTY',['#CODE#' => $this->arParams['PRODUCT_PROPERTY_CODE']]));
            }

            // Разделы инфоблока
            $selectSections = [
                'ID',
                'NAME',
            ];
            $filterSections = [
                'IBLOCK_ID' => $this->classifierIBId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' =>  $this->arParams['CACHE_GROUPS']
            ];

            $this->arResult['SECTIONS'] = [];
            $sectionIds = [];
            $rsSections = \CIBlockElement::GetList([], $filterSections, false, false, $selectSections);
            while($arSection = $rsSections->GetNext())
            {
                $this->arResult['SECTIONS'][$arSection['ID']] = [
                    'NAME' => $arSection['NAME'],
                    'PRODUCTS' => [],
                ];
                $sectionIds[] = $arSection['ID'];
            }

            // Нет разделов
            if (!$sectionIds) {
                $this->AbortResultCache();
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_ELEMENTS_CLASS_IBLOCK'));
            }

            $selectElements = [
                'ID',
                'NAME',
                'PROPERTY_PRICE',
                'PROPERTY_MATERIAL',
                'PROPERTY_ARTNUMBER',
                $this->classifierCode,
                'DETAIL_PAGE_URL',
            ];
            $filterElementss = [
                'IBLOCK_ID' => $this->catalogIBId,
                'CHECK_PERMISSIONS' => $this->arParams['CACHE_GROUPS'],
                $this->classifierCode => $sectionIds,
                'ACTIVE' => 'Y'
            ];

            $elements = \CIBlockElement::GetList([], $filterElementss, false, false, $selectElements);
            while($element = $elements->GetNext(true, false))
            {
                $sectionId = $element[$this->classifierCode.'_VALUE'];
                $this->arResult['SECTIONS'][$sectionId]['PRODUCTS'][] = [
                    'NAME' => $element['NAME'],
                    'PRICE' => $element['PROPERTY_PRICE_VALUE'],
                    'MATERIAL' => $element['PROPERTY_MATERIAL_VALUE'],
                    'ARTNUMBER' => $element['PROPERTY_ARTNUMBER_VALUE'],
                    'DETAIL_PAGE_URL' => $element['DETAIL_PAGE_URL'],
                ];
            }

            $this->clearResult();

            $this->arResult['ELEMENT_COUNT'] = count($this->arResult['SECTIONS']);
            $this->SetResultCacheKeys(['ITEMS', 'ELEMENT_COUNT']);

        }
        $this->IncludeComponentTemplate();

        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage('SC_71_TITLE').$this->arResult['ELEMENT_COUNT']);

    }
    protected function clearResult()
    {
        // Убрать пустые разделы
        foreach ($this->arResult['SECTIONS'] as $id => $section) {
            if (!count($section['PRODUCTS'])) {
                unset($this->arResult['SECTIONS'][$id]);
            }
        }
    }
}