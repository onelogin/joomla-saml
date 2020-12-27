<?php
/**
 * @package     OneLogin SAML.Plugin
 * @subpackage  System.oneloginsaml_backend
 *
 * @copyright   Copyright (C) 2020 OneLogin, Inc. All rights reserved.
 * @license     MIT
 */

if (!defined('_JEXEC')) {
    require_once __DIR__ . '/saml.php';
} else {
    require_once JPATH_LIBRARIES.'/loader.php';

    class PlgSystemOneloginsaml_Backend extends JPlugin
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

        public function onAfterInitialise() {
            if (JFactory::getUser()->guest && JFactory::getApplication()->isAdmin() && !(isset($_GET['normal']) || isset($_POST['username']))) {
                if ($this->params->get('onelogin_saml_backend_force_saml')) {
                    $response = new JAuthenticationResponse();
                    if (empty($response->error_message)) {
                        $ssoUrl = JRoute::_(JUri::root().'plugins/system/oneloginsaml_backend/oneloginsaml_backend.php?sso', true);

                        JFactory::getApplication()->redirect($ssoUrl);
                    }
                }
            }
        }

        public function onRenderModule(&$module, &$attribs) {
            if (JFactory::getUser()->guest && JFactory::getApplication()->isAdmin()) {
                if ($this->params->get('onelogin_saml_backend_inject_login')) {
                    if ($module->module == "mod_login") {
                        $link = $this->getSSOLinkAndText();
                        $samlContent = '<div ><center><a class="btn btn-primary" href="'.$link['href'].'">'.$link['text'].'</a></center><hr></div>';

                        $module->content = $samlContent . $module->content;
                    }
                }
            }
        }

        public function getSSOLinkAndText() {
            $ssoUrl = JRoute::_(JUri::root().'plugins/system/oneloginsaml_backend/oneloginsaml_backend.php?sso', true);

            $link_text = $this->params->get('onelogin_saml_backend_link_text');
            if (empty($link_text)) {
                $link_text = "SAML Login";
            }
            return ["href" => $ssoUrl, "text" => $link_text];
        }
    }
}
