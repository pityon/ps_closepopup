<?php
/**
* 2007-2022 PrestaShop
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
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class closepopup extends Module
{
    public function __construct()
    {
        $this->name = 'closepopup';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'https://github.com/pityon/';
        $this->need_instance = false;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Close Popup');
        $this->description = $this->l('Module shows a customizable popup in case customer intends to close website.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete module with all its contents?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        // CUSTOM SETUP
        $pre = strtoupper($this->name).'_';
        $this->config_fields = array(
			array(
				'type' => 'color',
				'label' => $this->l('Background color'),
				'name' => $pre.'BG_COLOR',
				'default_value' => '#f5375d',
			),
            array(
				'type' => 'color',
				'label' => $this->l('Text color'),
				'name' => $pre.'TEXT_COLOR',
				'default_value' => '#ffffff',
			),
            array(
				'type' => 'textarea',
				'autoload_rte' => true,
				'label' => $this->l('Content'),
				'name' => $pre.'CONTENT',
				'default_value' => '<h2>Wait!</h2><p><big>We want to give YOU a <strong>10% discount</strong> for your first order.</big></p><p><strong>Use this discount code</strong> at the checkout - <strong>BH10</strong></p>',
			),
            array(
                'type' => 'file',
                'name' => $pre.'BG_IMAGE',
                'multiple' => false,
                'label' => $this->l('Image as background'),
                'lang' => true,
            ),
            array(
                'type' => 'switch',
                'label' => $this->l('Restrict to date range'),
                'name' => $pre.'DATE_RANGE',
                'is_bool' => true,
                'desc' => $this->l('Should the module be shown in certain date range'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
                'section' => 'restrictions',
            ),
            array(
				'type' => 'date',
				'label' => $this->l('Start date'),
				'name' => $pre.'DATE_FROM',
                'section' => 'restrictions',
			),
            array(
				'type' => 'date',
				'label' => $this->l('End date'),
				'name' => $pre.'DATE_TO',
                'section' => 'restrictions',
			),
            array(
				'type' => 'select',
				'label' => $this->l('Time interval'),
				'name' => $pre.'TIME_INTERVAL',
                'desc' => $this->l('Don\'t show again popup for selected amount of time'),
				'options' => array(
					'query' => array(
						array('id_option' => '10', 'name' => '10m'),
						array('id_option' => '30', 'name' => '30m'),
						array('id_option' => '60', 'name' => '1h'),
						array('id_option' => '1440', 'name' => '24h'),
						array('id_option' => '10080', 'name' => '7d'),
					),
					'id' => 'id_option',
    				'name' => 'name',
				),
				'default_value' => '10',
                'section' => 'restrictions',
			),
            array(
				'type' => 'select',
				'label' => $this->l('Hook'),
				'name' => $pre.'SELECTED_HOOK',
                'desc' => $this->l('Show module only in selected place'),
				'options' => array(
					'query' => array(
						array('id_option' => '', 'name' => 'Everywhere'),
						array('id_option' => 'home', 'name' => 'Homepage'),
						array('id_option' => 'categories', 'name' => 'Categories'),
						array('id_option' => 'products', 'name' => 'Product page'),
					),
					'id' => 'id_option',
    				'name' => 'name',
				),
                'section' => 'restrictions',
			),
		);
        $this->css = array(
			array(
                'file' => 'front.css',
                'front_office' => true,
            ),
            array(
                'file' => 'back.css',
                'back_office' => true,
            ),
		);
		$this->js = array(
            array(
                'file' => 'front.js',
                'front_office' => true,
            )
		);
    }

    public function install()
    {
        // include(dirname(__FILE__).'/sql/install.php');
        return 
            parent::install() &&
            $this->registerFields() && 
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        // include(dirname(__FILE__).'/sql/uninstall.php');
        return 
            parent::uninstall() &&
            $this->unregisterFields();
    }

    public function getContent()
    {
        $html = '';
        if (((bool)Tools::isSubmit('submit_image_delete')) == true) {
            $html = $this->handleImageDeletion();
        }
        elseif (((bool)Tools::isSubmit('submit_'.$this->name)) == true) {
            $this->postProcess();
            $html = $this->displayConfirmation($this->l('Configuration updated'));
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $html.$output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $sections = $this->getConfigForm($helper);
        return $helper->generateForm($sections);
    }

    protected function getConfigForm(&$helper)
    {
        $sections = array(
			'general' => array(
				'form' => array(
					// 'tinymce' => true,
					'legend' => array(
						'title' => $this->l('Layout settings'),
						'icon' => 'icon-cogs'
					),
					'input' => array(),
					// 'submit' => array(
					// 	'title' => $this->l('Save'),
					// 	'class' => 'btn btn-default pull-right'
					// )
				)
			),
            'restrictions' => array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Restrictions'),
						'icon' => 'icon-credit-card'
					),
					'input' => array(),
					'submit' => array(
						'title' => $this->l('Save'),
						'class' => 'btn btn-default pull-right'
					)
				)
			),
		);

		foreach ($this->config_fields as $field) {
			$helper->fields_value[$field['name']] = Configuration::get($field['name']);
			$section = $field['section'] ?? 'general';

            // ADD MINIATURE TO DESCRIPTION
            if ($field['type'] == 'file') {
                $field['desc'] = $this->renderMiniature($field['name']);
            }

			$sections[$section]['form']['input'][] = $field;
		}

        return $sections;
    }

    protected function postProcess()
    {
        foreach ($this->config_fields as $field) {
            $name = $field['name'];
            $value = strval(Tools::getValue($name));
            $tags = ($field['type'] == 'textarea');

            if ($field['type'] == 'file') {
                $size = $_FILES[$field['name']]['size'];
                $previousFile = Configuration::get($field['name']);

                if ($size > 10) {
                    $ext = end((explode(".", $value)));
                    $allowed = array('jpg', 'png');
                    if (in_array($ext, $allowed)) {

                        // REMOVE OLD FILE IF NEW IS UPLOADED
                        if ($previousFile) {
                            unlink($this->local_path.'views/img/upload/'.$previousFile);
                        }

                        $filename = date("Ymdhis").'.'.$ext;
                        move_uploaded_file($_FILES[$field['name']]['tmp_name'], $this->local_path.'views/img/upload/'.$filename);
                        $value = $filename;
                    }
                }
                else {
                    $value = $previousFile;
                }
            }

            Configuration::updateValue($name, $value, $tags);
        }
    }

    public function hookBackOfficeHeader()
    {
        $this->hookCSS('back_office');
        $this->hookJS('back_office');
    }

    public function hookDisplayHeader()
    {
        $this->hookCSS();
        $this->hookJS();
    }

    public function hookDisplayHome()
    {
        if ($this->isAvailable('home')) {
            return $this->renderView();
        }
        return '';
    }

    public function hookDisplayProductAdditionalInfo()
    {
        if ($this->isAvailable('products')) {
            return $this->renderView();
        }
        return '';
    }

    public function hookDisplayFooter()
    {
        if ($this->context->controller->php_self == 'category') {
            if ($this->isAvailable('categories')) {
                return $this->renderView();
            }
        }
        return '';
    }

    // HELPER METHODS
    private function registerFields() {
		foreach ($this->config_fields as $field) {
            $tags = ($field['type'] == 'textarea');
			if (!Configuration::updateValue($field['name'], $field['default_value'] ?? '', $tags)) {
				return false;
			}
		}
		return true;
	}

    private function unregisterFields() {
		foreach ($this->config_fields as $field) {
			if (!Configuration::deleteByName($field['name'])) {
				return false;
			}
		}
		return true;
	}

    private function hookCSS($hook = 'front_office') {
        foreach ($this->css as $file) {
            $_hook = $file[$hook] ?? false;
            if ($_hook == true) {
                $module = $this->_path.'views/css/'.$file['file'];
                $theme = _THEME_DIR_.'css/modules/'.$this->name.'/'.$file['file'];
                if (file_exists($theme)) {
                    $this->context->controller->addCSS($theme, 'all');
                }
                else {
                    $this->context->controller->addCSS($module, 'all');
                }
            }
		}
    }

    private function hookJS($hook = 'front_office') {
        foreach ($this->js as $file) {
            $_hook = $file[$hook] ?? false;
            if ($_hook == true) {
                $module = $this->_path.'views/js/'.$file['file'];
                $theme = _THEME_DIR_.'js/modules/'.$this->name.'/'.$file['file'];
                if (file_exists($theme)) {
                    $this->context->controller->addJS($theme);
                }
                else {
                    $this->context->controller->addJS($module);
                }
            }
		}
    }

    private function renderMiniature($field_name) {
        $filename = Configuration::get($field_name);
        if ($filename) {
            $path = $this->local_path.'views/img/upload/'.$filename;
            $path2 = $this->_path.'views/img/upload/'.$filename;   // path that works on localhost
            if (file_exists($path)) {
                return '<img src="'.$path2.'" class="mini"><br><button type="submit" value="1" name="submit_image_delete" class="btn btn-default">'.$this->l('Delete image').'</button>';
            }
        }
        return '';
    }

    private function handleImageDeletion() {
        $field_name = strtoupper($this->name).'_BG_IMAGE';
        $bg_image = Configuration::get($field_name);
        if ($bg_image) {
            unlink($this->local_path.'views/img/upload/'.$bg_image);
            Configuration::updateValue($field_name, '');
            return $this->displayConfirmation($this->l('File deleted successfully'));
        }
    }

    private function renderView() {
        foreach ($this->config_fields as $field) {
			$key = $field['name'];
			$value = Configuration::get($key);
			$this->context->smarty->assign($key, $value);
		}
        $this->context->smarty->assign('CLOSEPOPUP_IMG_DIR', $this->_path.'views/img/upload/');

        return $this->display(__FILE__, 'closepopup.tpl');
    }

    private function isAvailable($hook_name = '') {
        $pre = strtoupper($this->name).'_';
        $date_restricted = Configuration::get($pre.'DATE_RANGE');
        $hook_value = Configuration::get($pre.'SELECTED_HOOK');

        if ($hook_name != '' && $hook_value != '') {
            if ($hook_name != $hook_value) {
                return false;
            }
        }
        if (isset($_COOKIE['closepopup_cookie'])) {
            return false;
        }
        if ($date_restricted) {
            $now = strtotime(date('Y-m-d'));
            $date_from = Configuration::get($pre.'DATE_FROM');
            $date_to = Configuration::get($pre.'DATE_TO');

            // VALIDATE DATES
            if ((int)substr($date_from, 0, 4) > 2000) {
                $from = strtotime($date_from);
            }
            else {
                $from = null;
            }
            if ((int)substr($date_to, 0, 4) > 2000) {
                $to = strtotime($date_to);
            }
            else {
                $to = null;
            }

            // SWAP DATES IF THEY WERE ARE IN WRONG ORDER
            if ($from != null && $to != null && $from > $to) {
                $cached = $from;
                $from = $to;
                $to = $cached;
            }

            if ($from != null && $now < $from) {
                return false;
            }
            if ($to != null && $to < $now) {
                return false;
            }
        }
        return true;
    }

}
