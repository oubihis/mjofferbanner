<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mjofferbanner extends Module
{
    public $version = '0.0.1';
    public $author = 'oubihis';
    public $displayName = 'MJ - Offer Banner';
    public $description = 'This is Show Offer Banner in Front Side';

    public function __construct()
    {
        $this->name = 'mjofferbanner';
        $this->tab = 'front_office_features';
        $this->version = '0.0.1';
        $this->author = 'oubihis';
        $this->displayName = 'MJ - Offer Banner';
        $this->description = 'This is Show Offer Banner in Front Side';
        $this->confirmUninstall = 'Are you sure you want to uninstall this module?';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->module_key = '1a2b3c4d5e6f7g8h9i0j';

        parent::__construct();
        $this->bootstrap = true;
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('HookDisplayOfferBanner')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->installDB()
            || !$this->installTab()
        ) {
            return false;
        }
        return true;
    }

    public function installDB()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mjofferbanner` (
                    `id_mjofferbanner` INT(11) NOT NULL AUTO_INCREMENT,
                    `img_path` VARCHAR(255) NOT NULL,
                    `img_description` VARCHAR(255) NOT NULL,
                    `img_link` VARCHAR(255) NOT NULL,
                    `date_add` DATETIME NOT NULL,
                    `date_upd` DATETIME NOT NULL,
                    PRIMARY KEY (`id_mjofferbanner`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        return Db::
        getInstance()->execute($sql);
    }
    
    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminMjofferbanner';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Majormedia Offer Banner';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentThemes');
        $tab->module = $this->name;
        $tab->add();
        return true;
    }
    
    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallDB() || !$this->uninstallTab()) {
            return false;
        }
        return true;
    }
    
    public function uninstallDB()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'mjofferbanner`');
    }
    
    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminMjofferbanner');
        $tab = new Tab($id_tab);
        $tab->delete();
        return true;
    }
    
    public function hookDisplayHome($params)
    {
        $this->context->smarty->assign(
            array(
                'img_path' => Configuration::get('MJOFFERBANNER_IMG_PATH'),
                'img_description' => Configuration::get('MJOFFERBANNER_IMG_DESCRIPTION'),
                'img_link' => Configuration::get('MJOFFERBANNER_IMG_LINK'),
            )
        );
        return $this->display(__FILE__, 'mjofferbanner.tpl');
    }
    
    public function hookHookDisplayOfferBanner($params)
    {
        $this->context->smarty->assign(
            array(
                'img_path' => Configuration::get('MJOFFERBANNER_IMG_PATH'),
                'img_description' => Configuration::get('MJOFFERBANNER_IMG_DESCRIPTION'),
                'img_link' => Configuration::get('MJOFFERBANNER_IMG_LINK'),
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/mjofferbanner.tpl');
    }
    
    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCSS($this->_path.'views/css/mjofferbanner.css');
    }
    
    public function getContent()
    {
        $output = null;
        if (Tools::isSubmit('submitMjofferbanner')) {
            $img_path = Tools::getValue('img_path');
            $img_description = Tools::getValue('img_description');
            $img_link = Tools::getValue('img_link');
            if (!$img_path || !$img_description || !$img_link) {
                    $output .= $this->displayError($this->l('Invalid Configuration value'));
                } else {
                    if (isset($_FILES['img_path']['tmp_name']) && $_FILES['img_path']['tmp_name'] != null) {
                        $img_path = $this->uploadImage($_FILES['img_path']['tmp_name']);
                    }
                    Configuration::updateValue('MJOFFERBANNER_IMG_PATH', $img_path);
                    Configuration::updateValue('MJOFFERBANNER_IMG_DESCRIPTION', $img_description);
                    Configuration::updateValue('MJOFFERBANNER_IMG_LINK', $img_link);
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }        

    private function uploadImage()
    {
            $file_name = uniqid().'.png'.pathinfo($_FILES['img_path']['tmp_name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['img_path']['tmp_name'], dirname(__FILE__).'/views/img/'.$file_name)) {
                return '<img src="'.$this->_path."views/img/".$file_name.'" style="width: 380px; height: 100%; border-radius: 3px;">';
            }
    }
    
    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'img_path',
                    'required' => true,
                    'display_image' => true,
                    'image' => Configuration::get('MJOFFERBANNER_IMG_PATH') ? Configuration::get('MJOFFERBANNER_IMG_PATH') : $this->_path.'views/img/default_image.png',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Image description'),
                    'name' => 'img_description',
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Image link'),
                    'name' => 'img_link',
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
                'name' => 'submitMjofferbanner',
            )
        );
    
        $helper = new HelperForm();
    
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
    
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
    
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );    
        // Load current value
        $helper->fields_value['img_path'] = Configuration::get('MJOFFERBANNER_IMG_PATH');
        $helper->fields_value['img_description'] = Configuration::get('MJOFFERBANNER_IMG_DESCRIPTION');
        $helper->fields_value['img_link'] = Configuration::get('MJOFFERBANNER_IMG_LINK');

        return $helper->generateForm($fields_form);
    }
}

