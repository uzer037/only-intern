<?
// if non-admin runs file - redirect them somewhere
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/local/classes/iblockparsers/vacancy_iblock_parser.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}

use \Classes\IBlockParsers\VacancyIBlockParser;

print("Загружаем парсер...<br>");
$parser = new VacancyIBlockParser(5,4);
print("Начинается импорт инфоблока.<br>");
$parser->processFile($_SERVER['DOCUMENT_ROOT'] . "/local/data/vacancy.csv", true);
print("Импорт завершен.");