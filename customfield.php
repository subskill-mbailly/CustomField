<?php
/**
* 2007-2021 PrestaShop
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
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class customfield extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'customfield';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Subskill';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('customfield');
        $this->description = $this->l('Configurations générales du site customisable avec un fichier de config');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $module_configuration = file_get_contents($this->local_path.'config.json');
        foreach(json_decode($module_configuration)->tabs as $conf){
            foreach($conf->fields as $field){
                Configuration::updateValue($field->key, false);
            }
        };

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        $module_configuration = file_get_contents($this->local_path.'config.json');
        foreach(json_decode($module_configuration)->tabs as $conf){
            foreach($conf->fields as $field){
                Configuration::deleteByName($field->key, false);
            }
        };

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitcustomfieldModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    public function renderForm()
    {

    $module_configuration = file_get_contents($this->local_path.'config.json');
    $fields_forms = [];
    
    // Boucle des tabs
    foreach(json_decode($module_configuration)->tabs as $conf){
        
        $tmp = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l($conf->title),
                    'icon' => $conf->icon,
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitForm'.$conf->key,
                )
            ),
        );

        // Boucle des fields
        foreach($conf->fields as $field){
            if($field->type == 'hidden'){
                $tmp_field = array(
                    'type' => 'hidden',
                    'name' => $field->key,
                    'required' => $field->required,
                );
            }else{
                if($field->type == 'text' || $field->type == 'color' || $field->type == 'file' || $field->type == 'password'){
                    $tmp_field = array(
                        'type' => $field->type,
                    );
                };
    
                if($field->type == 'textarea'){
                    $tmp_field = array(
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'validate' => 'isCleanHtml'
                    );
                };
    
                if($field->type == 'date'){
                    $tmp_field = array(
                        'type' => 'date',
                        'search' => true,
                        'validate' => 'isDate',
                    );
                };
    
                // Settings globaux
                if($field->key){ $tmp_field['name'] = $field->key; }

                if($field->disabled){ $tmp_field['disabled'] = $field->disabled; }
                if($field->instructions){ $tmp_field['hint'] = $this->l($field->instructions); }
                if($field->maxlength){ $tmp_field['maxlength'] = $field->maxlength ;}
                if($field->label){ $tmp_field['label'] = $this->l($field->label); }
                if($field->instructions){ $tmp_field['desc'] = $this->l($field->instructions); }
                if($field->placeholder){ $tmp_field['placeholder'] = $this->l($field->placeholder); }
                if($field->required){ $tmp_field['required'] = $field->required; }
                if($field->wrapper->class){ $tmp_field['class'] = $field->wrapper->class; }
                if($field->wrapper->id){ $tmp_field['id'] = $field->wrapper->id; }
                if($field->wrapper->id){ $tmp_field['id'] = $field->wrapper->id; }
                if($field->suffix){ $tmp_field['suffix'] = $field->suffix; }
                if($field->prefix){ $tmp_field['prefix'] = $field->prefix; }

                if($field->default_value){ $tmp_field['field_values'] = array($field->key => $field->default_value); }
            }

            // Push du field dans la tab
            array_push($tmp['form']['input'], $tmp_field);
        }
        // Push de la tab dans le tableau
        array_push($fields_forms, $tmp);
    };

    $helper = new HelperForm();
    $helper->show_toolbar = false;
    $helper->table =  $this->table;
    $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
    $helper->default_form_language = $lang->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitcustomfieldModule';
    $helper->module = $this;
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->tpl_vars = array(
        'currencies' => Currency::getCurrencies(),
        'fields_value' => $this->getConfigFormValues(),
        'languages' => $this->context->controller->getLanguages(),
        'id_language' => $this->context->language->id
    );


    return $helper->generateForm($fields_forms);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
       // Removed
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {

        $module_configuration = file_get_contents($this->local_path.'config.json');
        $values = array();
        foreach(json_decode($module_configuration)->tabs as $conf){
            foreach($conf->fields as $field){
                $values[$field->key] = Configuration::get($field->key, true);
            }
        };

        return $values;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayFooter()
    {
        /* Place your code here. */
    }
}
