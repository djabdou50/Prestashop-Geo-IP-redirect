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

class BannerController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'example_data';
		$this->className = 'ExampleData';
		$this->lang = true;
		$this->deleted = false;
		$this->colorOnBackground = false;
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		$this->context = Context::getContext();

		// définition de l'upload, chemin par défaut _PS_IMG_DIR_
		$this->fieldImageSettings = array('name' => 'image', 'dir' => 'example');

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
			'id_example_data' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 'auto',
			),
			'lorem' => array(
				'title' => $this->l('Lorem'),
				'width' => 'auto',
			),
		);

		// Gère les positions
//		$this->fields_list['position'] = array(
//			'title' => $this->l('Position'),
//			'width' => 70,
//			'align' => 'center',
//			'position' => 'position'
//		);

		$lists = parent::renderList();

		parent::initToolbar();

		return $lists;
	}


	public function renderForm()
	{

		$isAdd = ($_GET['id_example_data'] ) ? "updateexample_data" : "addexample_data";

		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('Example'),
				'image' => '../img/admin/cog.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'lang' => true,
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 40
				),
				array(
					'type' => 'hidden',
//					'value' => 43,
					'name' => 'id_example_data',
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button',
				'name' => $isAdd,

			)
		);



		$languages = Language::getLanguages(false);
		foreach ($languages as $language){
			$this->fields_form['input'][] = array(
				'type' => 'file',
				'label' => $this->l('Image '.$language['name'].':'),
				'lang' => true,
				'name' => 'file_url_'.$language['id_lang'],
				'display_image' => true,
				'desc' => $this->l('Upload image from your computer')
			);
		}

		if (!($obj = $this->loadObject(true)))
			return;


		return parent::renderForm();
	}

	public function postProcess()
	{

		if (Tools::isSubmit('submitAdd'.$this->table))
		{

			// Create Object ExampleData
			$exemple_data = new ExampleData();

			$exemple_data->exampledate = date("Y-m-d H:i:s");

			$id = Tools::getValue('id_example_data');

			$languages = Language::getLanguages(false);
			foreach ($languages as $language){
				$id_lang = $language['id_lang'];
				$exemple_data->name[$language['id_lang']] = Tools::getValue('name_'.$language['id_lang']);
				$exemple_data->lorem[$language['id_lang']] = Tools::getValue('lorem_'.$language['id_lang']);
				$exemple_data->file_url[$language['id_lang']] = self::upload_img($_FILES, 'file_url_'.$language['id_lang'] ); //Tools::getValue('file_url_'.$language['id_lang']);


				if($_POST['update'.$this->table])
				{
					$where = 'id_example_data = '.$id.' AND id_lang =' . $language['id_lang'] ;
					Db::getInstance()->update('example_data_lang', array(
						'name'=> $exemple_data->name[$language['id_lang']],
						'lorem'=> $exemple_data->lorem[$language['id_lang']],
//						'file_url' => $exemple_data->file_url[$language['id_lang']]
					), $where );

					if($exemple_data->file_url[$language['id_lang']]){

						Db::getInstance()->update('example_data_lang', array(
							'file_url' => $exemple_data->file_url[$language['id_lang']]
						), $where );

						//$sql = 'SELECT file_url FROM '._DB_PREFIX_.'example_data_lang WHERE id_example_data = 5 AND id_lang =' . $id_lang;
						//						$customVal = Db::getInstance()->getValue($sql);

					}
				}
			}

			// Save object
			if($_POST['add'.$this->table])
			{
				if (!$exemple_data->save()){
					$this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
				}else{
					Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
				}
			}
		}

		if(Tools::isSubmit('delete'.$this->table) && Tools::isSubmit('id_example_data'))
		{
			$id_example_data = Tools::getValue('id_example_data');

			Db::getInstance()->delete('example_data_lang', 'id_example_data =' . $id_example_data );
			Db::getInstance()->delete('example_data', 'id_example_data =' . $id_example_data );

		}

	}

	public function upload_img($FILES, $field_name){

		//file upload code
		if (isset($_FILES[$field_name])) {
			$target_dir    = _PS_UPLOAD_DIR_;
			$file_name      = $this->generateRandomString() .'-'. self::normalizeString( basename( $_FILES[$field_name]["name"] ) );
			$target_file   = $target_dir . $file_name;
			$uploadOk      = 1;
			$imageFileType = pathinfo( $target_file, PATHINFO_EXTENSION );
			// Check if image file is a actual image or fake image
			if ( isset( $_POST["submit"] ) ) {
				$check = getimagesize( $_FILES[$field_name]["tmp_name"] );
				if ( $check !== false ) {
//					echo "File is an image - " . $check["mime"] . ".";
					$uploadOk = 1;
				} else {
					Tools::displayError( "File is not an image.");
					$uploadOk = 0;
				}
			}
			// Check if file already exists
			if ( file_exists( $target_file ) ) {
				Tools::displayError( "Sorry, file already exists.");
				$uploadOk = 0;
			}
			// Allow certain file formats
			if ( $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			     && $imageFileType != "gif" ) {
				Tools::displayError( "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if ( $uploadOk == 0 ) {
				Tools::displayError( "Sorry, your file was not uploaded.");
			} else {
				if ( move_uploaded_file( $_FILES[$field_name]["tmp_name"], $target_file ) ) {
//					echo "The file " . basename( $_FILES[$field_name]["name"] ) . " has been uploaded. <br>";
					$file_location = basename( $_FILES[$field_name]["name"] );
//					echo $file_name;
//					echo $file_location;

					return $file_name;

				} else {
					Tools::displayError("Sorry, there was an error uploading your file.");
				}
			}
		}

	}

	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public static function normalizeString ($str = '')
	{
		$str = strip_tags($str);
		$str = preg_replace('/[\r\n\t ]+/', ' ', $str);
		$str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
		$str = strtolower($str);
		$str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
		$str = htmlentities($str, ENT_QUOTES, "utf-8");
		$str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
		$str = str_replace(' ', '-', $str);
		$str = rawurlencode($str);
		$str = str_replace('%', '-', $str);
		return $str;
	}
}
