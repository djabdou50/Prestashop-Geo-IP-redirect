<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Module Example - Notes
 *
 * Know if the module is enabled or not
 * 	Module::isEnabled($this->name);
 *
 * Know if the module is install or not
 * 	Module::isInstalled($this->name);
 *
 * Know if the module is registerd in one particular hook
 * 	$this->isRegisteredInHook('hook_name');
 *
 * Use the cache
 * 	$this->isCached($template);
 *
 * Check if the module is transplantable on the hook in parameter
 * 	$this->isHookableOn('hook_name');
 *
 * Get errors, warning, ...
 * 	$this->getErrors();
 * 	$this->getConfirmations();
 *
 * Add a warning message to display at the top of the admin page
 * 	$this->adminDisplayWarning('message');
 *
 * Add a info message to display at the top of the admin page
 * 	adminDisplayInformation('message');
 *
 * You don't need to call this one BUT, if you want to make an override in
 * a new version of your module, you will need to call this one (it's call
 * only in install, at first)
 * 	$this->installOverrides();
 *
 * You can disable the module for one shop (the actual in context)
 * 	$this->disable();
 * ... or for all shop
 * 	$this->disabel(true);
*/

/**
 * Module Example - Todo
 * Integrer les langues (champs/value) (http://www.prestashop.com/forums/index.php?/topic/189016-questions-sur-la-creation-de-modules-mvc/page__view__findpost__p__936271)
 * Integrer un fichier à télécharger (http://www.prestashop.com/forums/index.php?/topic/189016-questions-sur-la-creation-de-modules-mvc/page__view__findpost__p__939093)
 * Integrer des commandes sur addRowAction
*/

/* Security */
if (!defined('_PS_VERSION_'))
	exit;

/* Checking compatibility with older PrestaShop and fixing it */
if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM');

/* Loading Models */
require_once(_PS_MODULE_DIR_.'geoipredirect/models/ExampleData.php');
require_once(_PS_MODULE_DIR_.'geoipredirect/models/GeoData.php');

require (dirname(__FILE__). '/vendor/autoload.php');
use GeoIp2\Database\Reader;

class Geoipredirect extends Module
{
	private $errors = null;

	public function __construct()
	{
		// Author of the module
		$this->author = 'eyetags';
		// Name of the module ; the same that the directory and the module ClassName
		$this->name = 'geoipredirect';
		// Tab where it's the module (administration, front_office_features, ...)
		$this->tab = 'others';
		// Current version of the module
		$this->version = '1.0.0';

		//	Min version of PrestaShop wich the module can be install
		$this->ps_versions_compliancy['min'] = '1.5';
		// Max version of PrestaShop wich the module can be install
		$this->ps_versions_compliancy['max'] = '1.6';
		// OR $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

		//	The need_instance flag indicates whether to load the module's class when displaying the "Modules" page
		//	in the back-office. If set at 0, the module will not be loaded, and therefore will spend less resources
		//	to generate the page module. If your modules needs to display a warning message in the "Modules" page,
		//	then you must set this attribute to 1.
		$this->need_instance = 0;

		// Modules needed for install
		$this->dependencies = array();
		// e.g. $this->dependencies = array('blockcart', 'blockcms');

		// Limited country
		$this->limited_countries = array();
		// e.g. $this->limited_countries = array('fr', 'us');

		parent::__construct();

		// Name in the modules list
		$this->displayName = $this->l('Geo Ip Redirect');
		// A little description of the module
		$this->description = $this->l('Redirect shop by ip / country');

		// Message show when you wan to delete the module
		$this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');

		if ($this->active && Configuration::get('GEOIPREDIRECT_CONF') == '')
			$this->warning = $this->l('You have to configure your module');

		$this->errors = array();
	}

	public function install()
	{
		// Install SQL
		$sql = array();
		include(dirname(__FILE__).'/sql/install.php');

		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;

		// Install Tabs
		$parent_tab = new Tab();
		// Need a foreach for the language
		$parent_tab->name[$this->context->language->id] = $this->l('Utilities');
		$parent_tab->class_name = 'AdminMainExample';
		$parent_tab->id_parent = 0; // Home tab
		$parent_tab->module = $this->name;
		$parent_tab->add();

		$tab = new Tab();
		// Need a foreach for the language
		$tab->name[$this->context->language->id] = $this->l('Geo destination');
		$tab->class_name = 'Geo';
		$tab->id_parent = $parent_tab->id;
		$tab->module = $this->name;
		$tab->add();

		$tab = new Tab();
		// Need a foreach for the language
		$tab->name[$this->context->language->id] = $this->l('Banner admin');
		$tab->class_name = 'Banner';
		$tab->id_parent = $parent_tab->id;
		$tab->module = $this->name;
		$tab->add();

		//Init
		Configuration::updateValue('GEOIPREDIRECT_CONF', '');

		// Install Module
		// In this part, you don't need to add a hook in database, even if it's a new one.
		// The registerHook method will do it for your !
		return parent::install() &&
		       $this->registerHook('actionObjectExampleDataAddAfter') &&
		       $this->registerHook('actiondisplayHeaderBanner') &&
		       $this->registerHook('actionDispatcher');
	}

	public function uninstall()
	{
		// Uninstall SQL
		$sql = array();
		include(dirname(__FILE__).'/sql/uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;

		Configuration::deleteByName('GEOIPREDIRECT_CONF');

		// Uninstall Tabs
		$moduleTabs = Tab::getCollectionFromModule($this->name);
		if (!empty($moduleTabs)) {
			foreach ($moduleTabs as $moduleTab) {
				$moduleTab->delete();
			}
		}

		// Uninstall Module
		if (!parent::uninstall())
			return false;

		// You don't need to call this one because uninstall do it for you
		// !$this->unregisterHook('actionObjectExampleDataAddAfter')

		return true;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submit'.Tools::ucfirst($this->name)))
		{
			$example_conf = Tools::getValue('GEOIPREDIRECT_CONF');

			Configuration::updateValue('GEOIPREDIRECT_CONF', $example_conf);

			if (isset($this->errors) && count($this->errors))
				$output .= $this->displayError(implode('<br />', $this->errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$this->context->smarty->assign('request_uri', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$this->context->smarty->assign('path', $this->_path);
		$this->context->smarty->assign('GEOIPREDIRECT_CONF', pSQL(Tools::getValue('GEOIPREDIRECT_CONF', Configuration::get('GEOIPREDIRECT_CONF'))));
		$this->context->smarty->assign('submitName', 'submit'.Tools::ucfirst($this->name));
		$this->context->smarty->assign('errors', $this->errors);

		// You can return html, but I prefer this new version: use smarty in admin, :)
		return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
	}

	public function hookActiondisplayHeaderBanner()
	{

		$id_lang = $this->context->language->id;

		$sql = 'SELECT file_url FROM '._DB_PREFIX_.'example_data_lang WHERE id_lang =' . $id_lang ;
		$file_name = Db::getInstance()->getValue($sql);

		$this->context->smarty->assign(array(
			'banner_file_url' => _PS_BASE_URL_.'/upload/'. $file_name,
			'class' => 'b-' . $this->context->language->iso_code,
			"isset" => $file_name ? true: false,
		));

		// You can return html, but I prefer this new version: use smarty in admin, :)
		return $this->display(__FILE__, 'views/templates/admin/banner.tpl');
	}

	public function hookActionDispatcher($params) {
		// your code

		if($params["controller_type"] !== 2){

			$sql = 'SELECT country, dest_url FROM '._DB_PREFIX_.'geo_data WHERE country = "default" ';
			$default = Db::getInstance()->getRow($sql, $use_cache = 1);

			$reader = new Reader(dirname(__FILE__) . "/GeoLite2-Country.mmdb");
			try {
				$record = $reader->country($_SERVER['REMOTE_ADDR']);
			}catch (Exception $e){}

//		var_dump($record->country->isoCode);

			if(isset( $record->country->isoCode )){

				$sql = 'SELECT country, dest_url FROM '._DB_PREFIX_.'geo_data WHERE country = "'.$record->country->isoCode.'" ';
				$country = Db::getInstance()->getRow($sql, $use_cache = 1);

				if($country["dest_url"] && $country["dest_url"] !== $_SERVER['SERVER_NAME']){
//				echo "Gooo to " . $country["dest_url"];
					Tools::redirect("http://" . $country["dest_url"] );
				}

				if( ! $country["dest_url"]) {
//				echo "Gooo to " . $default["dest_url"];
					Tools::redirect("http://" . $default["dest_url"] );
				}

//				var_dump($_SERVER['SERVER_NAME']);
//				var_dump($_SERVER['REMOTE_ADDR']);
//				var_dump( $country );

			}elseif( $default["dest_url"] !== $_SERVER['SERVER_NAME'] ){
//			echo "Gooo to " . $default["dest_url"];
			Tools::redirect("http://" . $default["dest_url"] );
			}
		}
	}


	public function hookActionObjectExampleDataAddAfter($params)
	{
		/* Do something here... */
		$params = $params;

		return true;
	}
}
