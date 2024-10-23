<?
if (IsModuleInstalled('webcomp.market')) {

    // Копировать компоненты веб комп
    if (is_dir(dirname(__FILE__) . '/install/components'))
        $updater->CopyFiles("install/components", "components/");

    // Копировать админские скрипты
    if (is_dir(dirname(__FILE__) . '/install/js'))
        $updater->CopyFiles("install/js", "js/");

    // Копировать админские стили
    if (is_dir(dirname(__FILE__) . '/install/css'))
        $updater->CopyFiles("install/css", "css/");

    // Копировать админские картинки
    if (is_dir(dirname(__FILE__) . '/install/images'))
        $updater->CopyFiles("install/images", "images/");

    // Копировать все изменные файлы в шаблоне
    if (is_dir(dirname(__FILE__) . '/install/wizards/webcomp/market/site/templates'))
        if (is_dir($_SERVER["DOCUMENT_ROOT"].'/bitrix/templates/webcomp_yellow')){

            mkdir($_SERVER["DOCUMENT_ROOT"].'/bitrix/templates/webcomp', 0777, true);

            $updater->CopyFiles('/bitrix/templates/webcomp_yellow', '/bitrix/templates/webcomp');

            function remove_dir($dir)
            {
                if ($objs = glob($dir . '/*')) {
                    foreach($objs as $obj) {
                        is_dir($obj) ? remove_dir($obj) : unlink($obj);
                    }
                }
                rmdir($dir);
            }
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/webcomp_yellow';
            remove_dir($dir);
        }
        $updater->CopyFiles("install/wizards/webcomp/market/site/templates/webcomp", "templates/webcomp/");

    // Копировать корневые файлы (разделы)
    if (is_dir(dirname(__FILE__) . '/install/wizards/webcomp/market/site/public/ru'))
        $updater->CopyFiles("install/wizards/webcomp/market/site/public/ru", "../");
}

/*

//
// Sample database update
//


if($updater->CanUpdateDatabase())
{
	if($updater->TableExists("b_iblock_element_property"))
	{
		if(!$DB->IndexExists("b_iblock_element_property", array("VALUE_NUM", "IBLOCK_PROPERTY_ID")))
		{
			$updater->Query(array(
				"MySQL" => "CREATE INDEX ix_iblock_element_prop_num ON b_iblock_element_property(VALUE_NUM, IBLOCK_PROPERTY_ID)",
				"MSSQL" => "CREATE INDEX IX_B_IBLOCK_ELEMENT_PROPERTY_4 ON B_IBLOCK_ELEMENT_PROPERTY(VALUE_NUM, IBLOCK_PROPERTY_ID)",
				"Oracle" => "CREATE INDEX IX_IBLOCK_ELEMENT_PROP_NUM ON B_IBLOCK_ELEMENT_PROPERTY(VALUE_NUM, IBLOCK_PROPERTY_ID)",
			));
		}
        }
	if($updater->TableExists("b_iblock_property"))
	{
		if(!$DB->IndexExists("b_iblock_property", array("UPPER(\"CODE\")")))
		{
			$updater->Query(array(
				"Oracle" => "CREATE INDEX ix_iblock_property_2 ON B_IBLOCK_PROPERTY(UPPER(CODE))",
			));
		}
        }
}

*/

if ($updater->CanUpdateDatabase()) {
    if ($updater->TableExists("b_iblock_fields")) {
        /*В разделах каталога по умолчанию стоит настройка обрезать картинку если большая, и там 200px всего лишь, клиенты не понимаю почему обрезается, надо глянуть как там при установке можно поменять это дело.*/
        $updater->Query(array(
            "MySQL" => "UPDATE `b_iblock_fields` SET `DEFAULT_VALUE` = 'a:20:{s:5:\"SCALE\";s:1:\"N\";s:5:\"WIDTH\";s:0:\"\";s:6:\"HEIGHT\";s:0:\"\";s:13:\"IGNORE_ERRORS\";s:1:\"N\";s:6:\"METHOD\";s:8:\"resample\";s:11:\"COMPRESSION\";i:75;s:18:\"USE_WATERMARK_TEXT\";s:1:\"N\";s:14:\"WATERMARK_TEXT\";s:0:\"\";s:19:\"WATERMARK_TEXT_FONT\";s:0:\"\";s:20:\"WATERMARK_TEXT_COLOR\";s:0:\"\";s:19:\"WATERMARK_TEXT_SIZE\";s:0:\"\";s:23:\"WATERMARK_TEXT_POSITION\";s:2:\"tl\";s:18:\"USE_WATERMARK_FILE\";s:1:\"N\";s:14:\"WATERMARK_FILE\";s:0:\"\";s:20:\"WATERMARK_FILE_ALPHA\";s:0:\"\";s:23:\"WATERMARK_FILE_POSITION\";s:2:\"tl\";s:20:\"WATERMARK_FILE_ORDER\";s:0:\"\";s:11:\"FROM_DETAIL\";s:1:\"Y\";s:18:\"DELETE_WITH_DETAIL\";s:1:\"Y\";s:18:\"UPDATE_WITH_DETAIL\";s:1:\"Y\";}' WHERE `b_iblock_fields`.`IBLOCK_ID` = 1 AND `b_iblock_fields`.`FIELD_ID` = 'PREVIEW_PICTURE'",
        ));

    }

    if ($updater->TableExists("b_user_field")) {
        /*Настройка вывода цен, должна быть возможность отображать цены с десятками, сейчас обрезается до целых*/
        $updater->Query(array(
            "MySQL" => "UPDATE `b_user_field` SET `SETTINGS` = 'a:5:{s:9:\"PRECISION\";i:4;s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";d:0;s:9:\"MAX_VALUE\";d:0;s:13:\"DEFAULT_VALUE\";N;}' WHERE `b_user_field`.`ID` = 40",
        ));

    }

}
?>