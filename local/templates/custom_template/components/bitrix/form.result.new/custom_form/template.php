<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if ($arResult["isFormErrors"] == "Y"): ?>
    <?= $arResult["FORM_ERRORS_TEXT"]; ?>
<? endif; ?>
<?= $arResult["FORM_HEADER"] ?>

<div class="contact-form">
    <div class="contact-form__form">
        <div class="contact-form__form-inputs">
            <?
            $message_name = '';
            foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) {
                // executing regex search to extract bitrix-specific names from bitrix-generated code
                preg_match('/(?:name=")(.*?)(?:")/', $arQuestion["HTML_CODE"], $name_regex_match);

                $exclude = False;
                $type = "text";
                $extra_params = '';
                $err_msg = '';
                if ($arQuestion["REQUIRED"] == "Y")
                    $is_requiered = '';
                else
                    $is_requiered = 'requiered=""';

                switch ($FIELD_SID) {
                    case "medicine_name":
                        $err_msg = 'Поле должно содержать не менее 3-х символов';
                        break;
                    case "medicine_company":
                        $err_msg = 'Поле должно содержать не менее 3-х символов';
                        break;
                    case "medicine_email":
                        $type = "email";
                        $err_msg = 'Неверный формат почты';
                        break;
                    case "medicine_phone":
                        $type = "tel";
                        $extra_params = `data-inputmask="'mask': '+79999999999', 'clearIncomplete': 'true'" maxlength="12"
                            x-autocompletetype="phone-full"`;
                        break;
                    case "medicine_message":
                        $exclude = True;
                        $message_name = $name_regex_match[1];
                        break;
                }
                ?>

                <? if (!$exclude): ?>
                    <div class="input contact-form__input"><label class="inputtext input__label" for="<? $FIELD_SID ?>">
                            <div class="input__label-text">
                                <?= $arQuestion["CAPTION"] ?>
                                <?= $arResult["REQUIRED_SIGN"]; ?>
                            </div>
                            <input class="input__input" type="<?= $type ?>" id="<?= $FIELD_SID ?>" name="<?= $name_regex_match[1] ?>"
                                value="" <?= $is_requiered ?>         <?= $extra_params ?>>
                            <? if (!empty($err_msg)): ?>
                                <div class="input__notification">$err_msg</div>
                            <? endif ?>
                        </label></div>
                <? endif ?>

                <?
            }
            ?>
        </div>
        <div class="contact-form__form-message">
            <div class="input"><label class="input__label" for="medicine_message">
                    <div class="input__label-text">Сообщение</div>
                    <textarea class="input__input" type="text" id="medicine_message" name="<?= $message_name ?>"
                        value=""></textarea>
                    <div class="input__notification"></div>
                </label></div>
        </div>
        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
                ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
                данных&raquo;.
            </div>
            <input class="form-button contact-form__bottom-button" <?= (intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : ""); ?> type="submit" name="web_form_submit"
                value="<?= htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]); ?>"
                data-success="Отправлено" data-error="Ошибка отправки" />
        </div>
    </div>
</div>