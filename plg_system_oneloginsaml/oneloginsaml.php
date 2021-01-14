<?php
/**
 * @package     OneLogin SAML.Plugin
 * @subpackage  System.oneloginsaml
 *
 * @copyright   Copyright (C) 2021 OneLogin, Inc. All rights reserved.
 * @license     MIT
 */

if (!defined('_JEXEC')) {
    require_once __DIR__ . '/saml.php';
} else {
    require_once JPATH_LIBRARIES.'/onelogin/loader.php';

    class PlgSystemOneloginsaml extends JPlugin
    {
        public function onUserLogout($parameters, $options)
        {
            if ($this->params->get('onelogin_saml_slo') && JFactory::getApplication()->isSite()) {
                $session = JFactory::getSession();
                if ($session->get('saml_login')) {
                    $saml_joomla = new Saml_Joomla($this->params, 'onelogin_saml');
                    $saml_auth = $saml_joomla->getSamlAuth();

                    $nameId = $session->get('saml_nameid', null);
                    $nameIdFormat = $session->get('saml_nameid_format', null);
                    $sessionIndex = $session->get('saml_sessionindex', null);
                    $nameIdNameQualifier = $session->get('saml_nameid_namequalifier', null);
                    $nameIdSPNameQualifier = $session->get('saml_nameid_spnamequalifier', null);

                    $saml_auth->logout(null, [], $nameId, $sessionIndex, false, $nameIdFormat, $nameIdNameQualifier, $nameIdSPNameQualifier);
                }
            }
        }

        public function onAfterInitialise() {
            $skipForceSamlWord = $this->params->get('onelogin_saml_force_saml_skip_world');
            if (empty($skipForceSamlWord)) {
                $skipForceSamlWord = 'normal';
            }

            if (JFactory::getUser()->guest && JFactory::getApplication()->isSite()) {
                if ($this->params->get('onelogin_saml_force_saml') && !((isset($_GET[$skipForceSamlWord]) || isset($_POST['username'])))) {
                    $response = new JAuthenticationResponse();
                    if (empty($response->error_message)) {
                        $ssoUrl = JRoute::_(JUri::root().'plugins/system/oneloginsaml/oneloginsaml.php?sso', true);

                        JFactory::getApplication()->redirect($ssoUrl);
                    }
                }
            }
        }

        public function onRenderModule(&$module, &$attribs) {
            if (JFactory::getUser()->guest && JFactory::getApplication()->isSite()) {
                if ($this->params->get('onelogin_saml_inject_login')) {
                    if ($module->module == "mod_login") {
                        $link = $this->getSSOLinkAndText();
                        $samlContent = '<div ><center><a class="btn btn-primary" href="'.$link['href'].'">'.$link['text'].'</a></center><hr></div>';

                        $module->content = $samlContent . $module->content;
                    }
                }
            }
        }

        public  function onContentPrepareForm($form, $data)
        {
            if (JFactory::getUser()->guest && JFactory::getApplication()->isSite()) {
                if ($this->params->get('onelogin_saml_inject_login')) {
                    $app    = JFactory::getApplication();
                    $option = $app->input->get('option');
                    $view = $app->input->get('view');

                    $link = $this->getSSOLinkAndText();
                    $samlContent = '<div ><center><a class=\"btn btn-primary\" href=\"'.$link['href'].'\">'.$link['text'].'</a></center><hr></div>';

                    if ($option == 'com_users' && $view == 'login') {
                        
                        echo '<script>
                            jQuery(document).ready(function() {
                                var username_field = jQuery("div.login form fieldset div input#username");
                                if (username_field != null) {
                                    username_field.parent().parent().prepend("'.$samlContent.'");
                                }
                            });
                        </script>';
                    }
                }
            }
            return true;
        }

        public function getSSOLinkAndText() {
            $ssoUrl = JRoute::_(JUri::root().'plugins/system/oneloginsaml/oneloginsaml.php?sso', true);

            $link_text = $this->params->get('onelogin_saml_link_text');
            if (empty($link_text)) {
                $link_text = "SAML Login";
            }
            return ["href" => $ssoUrl, "text" => $link_text];
        }
    }
}
