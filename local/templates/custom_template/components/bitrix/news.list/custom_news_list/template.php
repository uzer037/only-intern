<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<div id="barba-wrapper">
    <div class="article-list">
        <? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
            <?= $arResult["NAV_STRING"] ?><br />
        <? endif; ?>
        <? foreach ($arResult["ITEMS"] as $arItem): ?>
            <?
			$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
			?>
            <? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
				<a class="article-item article-list__item <? echo $alignment_class; ?>" id="<?= $this->GetEditAreaId($arItem['ID']); ?>" href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
			<? else: ?>
				<div class="article-item article-list__item <? echo $alignment_class; ?>" id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
			<? endif ?>
                <div class="article-item__background"><img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                                                        data-src="xxxHTMLLINKxxx0.39186223192351520.41491856731872767xxx"
                                                        alt=""/></div>
                <div class="article-item__wrapper">
                    <div class="article-item__title"><? echo $arItem["NAME"] ?></div>
                    <div class="article-item__content">
                    <? if ($arParams["DISPLAY_PREVIEW_TEXT"] != "N" && $arItem["PREVIEW_TEXT"]): ?>
						<? echo $arItem["PREVIEW_TEXT"]; ?>
					<? endif; ?>
					<? if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arItem["PREVIEW_PICTURE"])): ?>
						<div style="clear:both"></div>
					<? endif ?>
					<? foreach ($arItem["FIELDS"] as $code => $value): ?>
						<small>
							<?= GetMessage("IBLOCK_FIELD_" . $code) ?>:&nbsp;
							<?= $value; ?>
						</small><br />
					<? endforeach; ?>
					<? foreach ($arItem["DISPLAY_PROPERTIES"] as $pid => $arProperty): ?>
						<small>
							<?= $arProperty["NAME"] ?>:&nbsp;
							<? if (is_array($arProperty["DISPLAY_VALUE"])): ?>
								<?= implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]); ?>
							<? else: ?>
								<?= $arProperty["DISPLAY_VALUE"]; ?>
							<? endif ?>
						</small><br />
					<? endforeach; ?>
                    </div>
                </div>
            <? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
				</a>
			<? else: ?>
				</div>
			<? endif ?>
        <? endforeach; ?>
    </div>
</div>
