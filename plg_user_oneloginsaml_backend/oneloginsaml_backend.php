<?php
/**
 * @package     OneLogin SAML.Plugin
 * @subpackage  User.oneloginsaml_backend
 *
 * @copyright   Copyright (C) 2020 OneLogin, Inc. All rights reserved.
 * @license     MIT
 */

if (!defined('_JEXEC')) {
    require_once __DIR__ . '/saml.php';
} else {
    require_once JPATH_LIBRARIES.'/loader.php';

    class PlgUserOneloginsaml_Backend extends JPlugin
    {
        public function onUserLogout($parameters, $options)
        {
            if ($this->params->get('onelogin_saml_backend_slo') && JFactory::getApplication()->isAdmin()) {
                $session = JFactory::getSession();
                if ($session->get('saml_backend_login')) {
                    $saml_joomla = new Saml_Joomla($this->params, 'onelogin_saml_backend');
                    $saml_auth = $saml_joomla->getSamlAuth();

                    $nameId = $session->get('saml_backend_nameid', null);
                    $nameIdFormat = $session->get('saml_backend_nameid_format', null);
                    $sessionIndex = $session->get('saml_backend_sessionindex', null);
                    $nameIdNameQualifier = $session->get('saml_backend_nameid_namequalifier', null);
                    $nameIdSPNameQualifier = $session->get('saml_backend_nameid_spnamequalifier', null);

                    $saml_auth->logout(null, [], $nameId, $sessionIndex, false, $nameIdFormat, $nameIdNameQualifier, $nameIdSPNameQualifier);
                }
            }
        }
    }
}
