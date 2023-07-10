<?
namespace Classes\IBlockParsers;

use \CIBLockElement;
use \CIBlockPropertyEnum;

// class defenition
class VacancyIBlockParser
{
    public int  $iblockId;
    public int  $iblockCategoryId;
    private CIBlockElement $iblockElement;

    private array $arProps;
    /**
     * Should print full import info or errors only
     */
    private bool $verbose = false;

    private array $propMapping = array(
        'OFFICE'        => 1,
        'LOCATION'      => 2,
        'NAME'          => 3,
        'REQUIRE'       => 4,
        'DUTY'          => 5,
        'CONDITIONS'    => 6,
        'SALARY_VALUE'  => 7,
        'TYPE'          => 8,
        'ACTIVITY'      => 9,
        'SCHEDULE'      => 10,
        'FIELD'         => 11,
        'EMAIL'         => 12
    );

    function __construct(int $iblockId, int $iblockCategoryId)
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        
        $this->iblockId = $iblockId;
        $this->iblockCategoryId = $iblockCategoryId;
        
        $this->iblockElement = new CIBlockElement();
        $this->arProps = [];
        // getting structure of target IBlock
        $rsElement = CIBlockElement::GetList([], ['IBLOCK_ID' => $this->iblockId],
        false, false, ['ID', 'NAME']);
        
        while ($ob = $rsElement->GetNextElement()) {
            $arFields = $ob->GetFields();
            $key = str_replace(['»', '«', '(', ')'], '', $arFields['NAME']);
            $key = strtolower($key);
            $arKey = explode(' ', $key);
            $key = '';
            foreach ($arKey as $part) {
                if (strlen($part) > 2) {
                    $key .= trim($part) . ' ';
                }
            }
            $key = trim($key);
            $this->arProps['OFFICE'][$key] = $arFields['ID'];
        }
        
        $rsProp = CIBlockPropertyEnum::GetList(
            ["SORT" => "ASC", "VALUE" => "ASC"],
            ['IBLOCK_ID' => $iblockId]
        );
        while ($arProp = $rsProp->Fetch()) {
            $key = trim($arProp['VALUE']);
            $this->arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
        }
    }

    function processFile(string $filePath, bool $verbose = false)
    {
        $this->verbose = $verbose;
        $errCount = 0;
        $rowIndex = 1;
        
        print("Загружаем файл...<br>");
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                // skipping the header
                if ($rowIndex == 1) {
                    $rowIndex++;
                    continue;
                }
                $rowIndex++;
                if(!$this->addElementToIBlock($row, $iblock)){
                    $errCount++;
                }
            }
            fclose($handle);
            if($errCount == 0) {
                print("Файл " . $filePath . " успешно обработан.<br>");
            } else {
                print("Файл " . $filePath . " обработан c " . $errCount . " ошибками(-ой).<br>");
            }
        } else {
            print("Файл не существует.<br>");
        }
    }

    private function processListLike($value) {
        $value = explode('•', $value);
        array_splice($value, 0, 1);
        foreach ($value as &$str) {
            $str = trim($str);
        }
        return $value;
    }

    private function processSalary($value) {
        if ($value == '-') {
            $value = '';
        } elseif ($value == 'по договоренности') {
            $value = '';
            $salaryType = $this->arProps['SALARY_TYPE']['договорная'];
        } else {
            $salaryData = explode(' ', $value);
            if ($salaryData[0] == 'от' || $salaryData[0] == 'до') {
                $salaryType = $this->arProps['SALARY_TYPE'][$salaryData[0]];
                array_splice($salaryData, 0, 1);
                $value = implode(' ', $salaryData);
            }
        }
        if(empty($salaryType) && !empty($value)) {
            $salaryType = $this->arProps['SALARY_TYPE']['='];
        }
        return array($value, $salaryType);
    }

    private function getFieldIndex($property, $value, $location) {
        if ($this->arProps[$property]) {
            $arSimilar = [];
            foreach ($this->arProps[$property] as $propKey => $propVal) {
                if ($property == 'OFFICE') {
                    $value = strtolower($value);
                    if ($value == 'центральный офис') {
                        $value .= 'свеза ' . $location;
                    } elseif ($value == 'лесозаготовка') {
                        $value = 'свеза ресурс ' . $value;
                    } elseif ($value == 'свеза тюмень') {
                        $value = 'свеза тюмени';
                    }
                    $arSimilar[similar_text($value, $propKey)] = $propVal;
                }
                if (stripos($propKey, $value) !== false) {
                    $value = $propVal;
                    break;
                }

                if (similar_text($propKey, $value) > 50) {
                    $value = $propVal;
                }
            }
            if ($property == 'OFFICE' && !is_numeric($value)) {
                ksort($arSimilar);
                $value = array_pop($arSimilar);
            }
        }
        return $value;
    }

    private function addElementToIBlock($csvLineData, &$iblock)
    {
        global $USER;

        $elementName = '';
        $propertiesArray['SALARY_TYPE'] = '';
        $propertiesArray['SALARY_VALUE'] = '';

        foreach ($this->propMapping as $property => $index) {
            $value = trim($csvLineData[$index]);
            $value = str_replace('\n', '', $value);

            switch($property) {
                // processing list-like properties
                case 'REQUIRE':
                case 'DUTY':
                case 'CONDITIONS':
                    $value = $this->processListLike($value);
                    break;
                case 'SALARY_VALUE':
                    $salaryInfo = $this->processSalary($value);
                    $value = $salaryInfo[0];
                    if(isset($salaryInfo[1])) {
                        $propertiesArray['SALARY_TYPE'] = $salaryInfo[1];
                    }
                    break;
            }

            // getting ids of fields where its needed
            $value = $this->getFieldIndex($property, $value, $csvLineData[$this->propMapping["LOCATION"]]);

            // processing 'NAME' separatley because it stored separate in IBlock
            if ($property == "NAME") {
                $elementName = $value;
            } else {
                $propertiesArray[$property] = $value;
            }
        }

        // additionally setting date to current
        $propertiesArray["DATE"] = date('d.m.Y');

        $iblockDataArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $this->iblockId,
            "PROPERTY_VALUES" => $propertiesArray,
            "NAME" => $elementName,
            "ACTIVE" => end($csvLineData) ? 'Y' : 'N',
        ];

        if($elementId = $this->iblockElement->Add($iblockDataArray)) {
            if($this->verbose) {
                print("Добавлен эелемент id=" . $elementId . "<br>");
            }
            return true;
        } else {
            print("Ошибка: " . $this->iblockElement->LAST_ERROR . "<br>");
            return false;
        }
    }
}