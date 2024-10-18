<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<p><?=Loc::getMessage('SIMPLECOMP_EXAM2_TIMESTAMP').time()?></p>
<?if($arResult['SECTIONS']):?>
<p><b><?=Loc::getMessage('SIMPLECOMP_EXAM2_CAT_TITLE')?></b></p>

<div>
	<ul>
	<?foreach ($arResult['SECTIONS'] as $section):?>
		<li class="section-name"><b><?=$section['NAME']?></b></li>
		<ul>
			<?foreach ($section['PRODUCTS'] as $product):?>
				<li>
					<a href="<?=$product['DETAIL_PAGE_URL']?>">
						<?=implode(' - ', [$product['NAME'], $product['PRICE'], $product['MATERIAL'], $product['ARTNUMBER']]);?>
					</a>
				</li>
			<?endforeach;?>
		</ul>
	<?endforeach;?>
	</ul>
</div>
<?endif;?>

