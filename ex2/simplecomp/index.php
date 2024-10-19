<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Простой компонент");
?><?$APPLICATION->IncludeComponent(
	"simplecomp.exam-71",
	"",
	Array(
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CLASS_IBLOCK_ID" => "6",
		"DETAIL_PAGE_URL_TEMPLATE" => "",
		"PRODUCTS_IBLOCK_ID" => "2",
		"PRODUCT_PROPERTY_CODE" => "FIRM",
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>