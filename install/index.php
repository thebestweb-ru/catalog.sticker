<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

Class thebestweb_catalog_sticker extends CModule{
	var	$MODULE_ID = 'thebestweb.catalog.sticker';
	var	$MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

	function __construct(){
		$arModuleVersion = array();

        include($this->GetPath()."/install/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage($this->MODULE_LANG_PREFIX."_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage($this->MODULE_LANG_PREFIX."_MODULE_DESC");

		$this->PARTNER_NAME = getMessage($this->MODULE_LANG_PREFIX."_PARTNER_NAME");
		$this->PARTNER_URI = getMessage($this->MODULE_LANG_PREFIX."_PARTNER_URI");

		$this->exclusionAdminFiles=array(
			'..',
			'.',
			'menu.php',
			'operation_description.php',
			'task_description.php'
		);
	}

	function InstallDB($arParams = array()){
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch($this->GetPath()."/install/db/".strtolower($DB->type)."/install.sql");
        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("", $this->errors));

            $this->UnInstallFiles();
            $this->deleteNecessaryMailEvents();
            $this->UnInstallEvents();
            $this->UnInstallDB();

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

            return false;
        }
		$this->createNecessaryIblocks();
        $this->createNecessaryUserFields();
	}

	function UnInstallDB($arParams = array()){
        global $DB, $DBType, $APPLICATION;

        $this->errors = false;
        if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
        {
            $this->errors = $DB->RunSQLBatch($this->GetPath()."/install/db/".$DBType."/uninstall.sql");
            if($this->errors !== false)
            {
                $APPLICATION->ThrowException(implode("", $this->errors));
                return false;
            }
        }
		\Bitrix\Main\Config\Option::delete($this->MODULE_ID);
		$this->deleteNecessaryIblocks();
        $this->deleteNecessaryUserFields();
	}

	function InstallEvents(){
		return true;
	}

	function UnInstallEvents(){
		return true;
	}

	function InstallFiles($arParams = array()){
        CopyDirFiles($this->GetPath()."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        CopyDirFiles($this->GetPath()."/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
        CopyDirFiles($this->GetPath()."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);

		return true;
	}

	function UnInstallFiles(){
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/'.$this->MODULE_ID.'.catalog.sticker/');

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')){
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].$this->GetPath().'/install/admin/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin');
			if ($dir = opendir($path)){
				while (false !== $item = readdir($dir)){
					if (in_array($item, $this->exclusionAdminFiles))
						continue;
					\Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
        DeleteDirFilesEx("/bitrix/js/thebestweb/".$this->MODULE_ID.'/');

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/files')){
			$this->deleteArbitraryFiles();
		}

		return true;
	}

	function copyArbitraryFiles(){
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$localPath = $this->GetPath().'/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object){
			$destPath = $rootPath.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
			($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
		}
	}

	function deleteArbitraryFiles(){
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$localPath = $this->GetPath().'/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object){
			if (!$object->isDir()){
				$file = str_replace($localPath, $rootPath, $object->getPathName());
				\Bitrix\Main\IO\File::deleteFile($file);
			}
		}
	}

	function createNecessaryIblocks(){
		return true;
	}

	function deleteNecessaryIblocks(){
		return true;
	}

	function createNecessaryUserFields(){
		return true;
	}

	function deleteNecessaryUserFields(){
		return true;
	}

	function createNecessaryMailEvents(){
        return true;
	}

	function deleteNecessaryMailEvents(){
        return true;
	}

	function isVersionD7(){
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	function GetPath($notDocumentRoot = false){
		if ($notDocumentRoot){
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		}else{
			return dirname(__DIR__);
		}
	}

	function getSitesIdsArray(){
		$ids = Array();
		$rsSites = CSite::GetList($by = "sort", $order = "desc");
		while ($arSite = $rsSites->Fetch()){
			$ids[] = $arSite["LID"];
		}

		return $ids;
	}

    function DoInstall(){

		global $APPLICATION;
		if ($this->isVersionD7()){
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallDB();
			$this->createNecessaryMailEvents();
			$this->InstallEvents();
			$this->InstallFiles();
		}else{
			$APPLICATION->ThrowException(Loc::getMessage($this->MODULE_LANG_PREFIX."_INSTALL_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage($this->MODULE_LANG_PREFIX."_INSTALL"), $this->GetPath()."/install/step.php");
	}

	function DoUninstall(){

		global $APPLICATION;

		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		$this->UnInstallFiles();
		$this->deleteNecessaryMailEvents();
		$this->UnInstallEvents();

		if ($request["savedata"] != "Y")
			$this->UnInstallDB();

		\Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(Loc::getMessage($this->MODULE_LANG_PREFIX."_UNINSTALL"), $this->GetPath()."/install/unstep.php");
	}
}
?>