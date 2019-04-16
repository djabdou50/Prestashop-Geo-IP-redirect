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
 * Tab Example - Controller Admin Example
 *
 * @category   	Module / checkout
 * @author     	PrestaEdit <j.danse@prestaedit.com>
 * @copyright  	2012 PrestaEdit
 * @version   	1.0
 * @link       	http://www.prestaedit.com/
 * @since      	File available since Release 1.0
*/



class GeoController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'geo_data';
		$this->className = 'GeoData';
		$this->lang = false;
		$this->deleted = false;
		$this->colorOnBackground = false;
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		$this->context = Context::getContext();
		$this->bootstrap = true;


		parent::__construct();
	}

	/**
	 * Function used to render the list to display for this controller
	 */
	public function renderList()
	{
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->addRowAction('details');

		$this->bulk_actions = array(
			'delete' => array(
				'text' => $this->l('Delete selected'),
				'confirm' => $this->l('Delete selected items?')
				)
			);

		$this->fields_list = array(
			'id_geo_data' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'country' => array(
				'title' => $this->l('Country'),
				'width' => 'auto',
			),
			'dest_url' => array(
				'title' => $this->l('Destination URL'),
				'width' => 'auto',
			),
		);

		$lists = parent::renderList();

		parent::initToolbar();

		return $lists;
	}

	/**
	 * method call when ajax request is made with the details row action
	 * @see AdminController::postProcess()
	 */
	public function ajaxProcessDetails()
	{
		if (($id = Tools::getValue('id')))
		{
			// override attributes
			$this->display = 'list';
			$this->lang = false;

			$this->addRowAction('edit');
			$this->addRowAction('delete');

			$this->_select = 'b.*';
			$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'tab_lang` b ON (b.`id_tab` = a.`id_tab` AND b.`id_lang` = '.$this->context->language->id.')';
			$this->_where = 'AND a.`id_parent` = '.(int)$id;
			$this->_orderBy = 'position';

			// get list and force no limit clause in the request
			$this->getList($this->context->language->id);

			// Render list
			$helper = new HelperList();
			$helper->actions = $this->actions;
			$helper->list_skip_actions = $this->list_skip_actions;
			$helper->no_link = true;
			$helper->shopLinkType = '';
			$helper->identifier = $this->identifier;
			$helper->imageType = $this->imageType;
			$helper->toolbar_scroll = false;
			$helper->show_toolbar = false;
			$helper->orderBy = 'position';
			$helper->orderWay = 'ASC';
			$helper->currentIndex = self::$currentIndex;
			$helper->token = $this->token;
			$helper->table = $this->table;
			$helper->position_identifier = $this->position_identifier;
			// Force render - no filter, form, js, sorting ...
			$helper->simple_header = true;
			$content = $helper->generateList($this->_list, $this->fields_list);

			echo Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content));
		}

		die;
	}

	public function renderForm()
	{
		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('Redirections'),
				'image' => '../img/admin/cog.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Pays:'),
					'name' => 'country',
					'size' => 40
				),
				array(
					'type' => 'text',
					'label' => $this->l('Destination (URL):'),
					'name' => 'dest_url',
					'size' => 40
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button',
				'name' =>  ($_GET['id_geo_data'] ) ? "updategeo_data" : "addgeo_data",
			)
		);


		if (!($obj = $this->loadObject(true)))
			return;

		return parent::renderForm();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			// Create Object ExampleData
			$geo_data = new GeoData();

			$geo_data->country = Tools::getValue('country');
			$geo_data->dest_url = Tools::getValue('dest_url');

			$id = Tools::getValue('id_geo_data');


			if( Tools::isSubmit('update'.$this->table ) )
			{
				$where = 'id_geo_data = '.$id;
				Db::getInstance()->update('geo_data', array(
					'country'=> $geo_data->country,
					'dest_url'=> $geo_data->dest_url,
				), $where );
			}


			if( Tools::isSubmit('add'.$this->table ) ){

				if (!$geo_data->save())
					$this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
				else
					Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
			}

		}

		if(Tools::isSubmit('delete'.$this->table) && Tools::isSubmit('id_geo_data'))
		{
			Db::getInstance()->delete('geo_data', 'id_geo_data =' . Tools::getValue('id_geo_data') );
		}

	}
}
