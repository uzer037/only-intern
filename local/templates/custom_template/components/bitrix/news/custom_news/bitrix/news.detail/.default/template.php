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
<div class="article-card">
    <? if ($arParams["DISPLAY_NAME"] != "N" && $arResult["NAME"]): ?>
        <div class="article-card__title">
            <?= $arResult["NAME"] ?>
        </div>
    <? endif; ?>
    <? if ($arParams["DISPLAY_DATE"] != "N" && $arResult["DISPLAY_ACTIVE_FROM"]): ?>
        <div class="article-card__date">
            <?= $arResult["DISPLAY_ACTIVE_FROM"] ?>
        </div>
    <? endif; ?>
    <div class="article-card__content">
        <? if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arResult["DETAIL_PICTURE"])): ?>
            <div class="article-card__image sticky">
                <img src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>" alt="" data-object-fit="cover" />
            </div>
        <? endif ?>

        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
                <? echo $arResult["DETAIL_TEXT"]; ?>
				<p><a href = "<? echo $arParams["IBLOCK_URL"] ?>"><?=GetMessage("T_NEWS_DETAIL_BACK")?></a></p>
            </div>
        </div>
    </div>
    <br />
    <? foreach ($arResult["FIELDS"] as $code => $value):
        if ('PREVIEW_PICTURE' == $code || 'DETAIL_PICTURE' == $code) {
            ?>
            <?= GetMessage("IBLOCK_FIELD_" . $code) ?>:&nbsp;
            <?
            if (!empty($value) && is_array($value)) {
                ?><img border="0" src="<?= $value["SRC"] ?>" width="<?= $value["WIDTH"] ?>" height="<?= $value["HEIGHT"] ?>"><?
            }
        } else {
            ?><?= GetMessage("IBLOCK_FIELD_" . $code) ?>:&nbsp;<?= $value; ?><?
            }
            ?><br />
    <? endforeach;?>
    <? foreach ($arResult["DISPLAY_PROPERTIES"] as $pid => $arProperty): ?>

        <?= $arProperty["NAME"] ?>:&nbsp;
        <? if (is_array($arProperty["DISPLAY_VALUE"])): ?>
            <?= implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]); ?>
        <? else: ?>
            <?= $arProperty["DISPLAY_VALUE"]; ?>
        <? endif ?>
        <br />
    <? endforeach; ?>
    
	<? if (array_key_exists("USE_SHARE", $arParams) && $arParams["USE_SHARE"] == "Y") {
        ?>
        <div class="news-detail-share">
            <noindex>
                <?
                $APPLICATION->IncludeComponent(
                    "bitrix:main.share",
                    "",
                    array(
                        "HANDLERS" => $arParams["SHARE_HANDLERS"],
                        "PAGE_URL" => $arResult["~DETAIL_PAGE_URL"],
                        "PAGE_TITLE" => $arResult["~NAME"],
                        "SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
                        "SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
                        "HIDE" => $arParams["SHARE_HIDE"],
                    ),
                    $component,
                    array("HIDE_ICONS" => "Y")
                );
                ?>
            </noindex>
        </div>
        <?
    }
    ?>
</div>