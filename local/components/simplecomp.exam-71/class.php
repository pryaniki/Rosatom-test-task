<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)  die();

use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;

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
        $params['CLASS_IBLOCK_ID'] = !empty($params['CLASS_IBLOCK_ID']) ? trim($params['CLASS_IBLOCK_ID']) : '';
        $params['SERVISES_IBLOCK_ID'] = !empty($params['SERVISES_IBLOCK_ID']) ? trim($params['SERVISES_IBLOCK_ID']) : '';
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
        // ex2-107
        if (!$this->arParams['SERVICES_IBLOCK_ID']) {
            throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_EMPTY_SERVISES_IBLOCK_ID'));
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
        // ex2-107
        $this->servicesIBId = intval($this->arParams['SERVICES_IBLOCK_ID']);

        $this->classifierCode = str_starts_with($this->arParams['PRODUCT_PROPERTY_CODE'], 'PROPERTY_')
            ?$this->arParams['PRODUCT_PROPERTY_CODE']:
            'PROPERTY_'.$this->arParams['PRODUCT_PROPERTY_CODE'];
    }
    protected function prepareData(): void
    {
        $userGroups = $this->user->getUserGroups();

        // ex2-107
        $taggedCache = Application::getInstance()->getTaggedCache();
        $taggedCache->startTagCache('/cache_simplecomp_exam-71');

        if ( $this->startResultCache(false, ($this->arParams['CACHE_GROUPS']==='N'? false : $userGroups)) ) {

            if (!$this->isExistIblock($this->arParams['PRODUCTS_IBLOCK_ID'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_IBLOCK', ['#ID#' => $this->arParams['PRODUCTS_IBLOCK_ID']]));
            }
            if (!$this->isExistIblock($this->arParams['CLASS_IBLOCK_ID'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_IBLOCK', ['#ID#' => $this->arParams['CLASS_IBLOCK_ID']]));
            }
            if (!$this->isExistIblock($this->arParams['SERVICES_IBLOCK_ID'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_IBLOCK', ['#ID#' => $this->arParams['SERVICES_IBLOCK_ID']]));
            }
            if (!$this->isExistProperty($this->arParams['PRODUCTS_IBLOCK_ID'], $this->arParams['PRODUCT_PROPERTY_CODE'])) {
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_EXIST_PROPERTY',['#CODE#' => $this->arParams['PRODUCT_PROPERTY_CODE']]));
            }

            // ex2-107
            $taggedCache->registerTag('my_iblock_id_' . $this->servicesIBId);

            $this->arResult['SECTIONS'] = [];

            $classifiers = $this->getClassifiers();
            $this->fillClassifier($classifiers);
            $classifierIds = array_keys($this->arResult['SECTIONS']);

            if (!$classifierIds) {
                $this->AbortResultCache();
                throw new SystemException(Loc::getMessage('SIMPLECOMP_EXAM2_NOT_ELEMENTS_CLASS_IBLOCK'));
            }

            $products = $this->getProducts($classifierIds);
            $this->fillProducts($products);
            $this->clearResult();

            $this->arResult['ELEMENT_COUNT'] = count($this->arResult['SECTIONS']);
            $this->SetResultCacheKeys(['ELEMENT_COUNT']);

            $this->IncludeComponentTemplate();
        }

        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage('SC_71_TITLE').$this->arResult['ELEMENT_COUNT']);

    }
    private function getProducts($classifierIds): CIBlockResult
    {
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
            $this->classifierCode => $classifierIds,
            'ACTIVE' => 'Y'
        ];
        return \CIBlockElement::GetList([], $filterElementss, false, false, $selectElements);
    }
    private function fillProducts($products): void
    {
        while($product = $products->GetNext(true, false))
        {
            $sectionId = $product[$this->classifierCode.'_VALUE'];
            $this->arResult['SECTIONS'][$sectionId]['PRODUCTS'][] = [
                'NAME' => $product['NAME'],
                'PRICE' => $product['PROPERTY_PRICE_VALUE'],
                'MATERIAL' => $product['PROPERTY_MATERIAL_VALUE'],
                'ARTNUMBER' => $product['PROPERTY_ARTNUMBER_VALUE'],
                'DETAIL_PAGE_URL' => $product['DETAIL_PAGE_URL'],
            ];
        }
    }
    private function getClassifiers(): CIBlockResult
    {
        $selectSections = [
            'ID',
            'NAME',
        ];
        $filterSections = [
            'IBLOCK_ID' => $this->classifierIBId,
            'ACTIVE' => 'Y',
            'CHECK_PERMISSIONS' =>  $this->arParams['CACHE_GROUPS']
        ];

        return \CIBlockElement::GetList([], $filterSections, false, false, $selectSections)?:[];
    }
    private function fillClassifier($classifiers): void
    {
        while($classifier = $classifiers->GetNext())
        {
            $this->arResult['SECTIONS'][$classifier['ID']] = [
                'NAME' => $classifier['NAME'],
                'PRODUCTS' => [],
            ];
        }
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