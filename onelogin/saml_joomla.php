<?php
/**
 * @package     OneLogin SAML.Library
 *
 * @copyright   Copyright (C) 2021 OneLogin, Inc. All rights reserved.
 * @license     MIT
 */

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;

/**
 * Wrapper for the library interface to make things easier.
 *
 * @author Michael Andrzejewski<michael@jetskitechnologies.com>
 */
class Saml_Joomla {

    private $plugin;
    private $plgParams;
    private $samlAuth;
    private $samlSettings;

    public function __construct($plgParams, $plugin='onelogin_saml') {
        $this->plugin = $plugin;
        $this->plgParams = $plgParams;
        $this->settings = $this->formatSettings();
    }

    public function getSamlAuth() {
        if (!isset($this->samlAuth)) {
            $this->samlAuth = new Auth($this->settings);
        }
        return $this->samlAuth;
    }

    public function getSamlSettings($spValidationOnly=false) {
        if (!isset($this->samlSettings)) {
            $this->samlSettings = new Settings($this->settings, $spValidationOnly);
        }
        return $this->samlSettings;
    }

    protected function formatSettings() {
        $urlBase = JUri::base();
        $urlBase = str_replace("administrator/", "" , $urlBase);
        $urlBase = str_replace("plugins/system/oneloginsaml_backend/", "" , $urlBase);
        $urlBase = str_replace("plugins/system/oneloginsaml/", "", $urlBase);
        
        if ($this->plugin == 'onelogin_saml') {
            $defaultSPEntityId = $urlBase . 'plugins/system/oneloginsaml/oneloginsaml.php?metadata';
            $acsUrl = $urlBase . 'plugins/system/oneloginsaml/oneloginsaml.php?acs';
            $slsUrl = $urlBase . 'plugins/system/oneloginsaml/oneloginsaml.php?sls';
        } else {
            $defaultSPEntityId = $urlBase . 'plugins/system/oneloginsaml_backend/oneloginsaml_backend.php?metadata';
            $acsUrl = $urlBase . 'plugins/system/oneloginsaml_backend/oneloginsaml_backend.php?acs';
            $slsUrl = $urlBase . 'plugins/system/oneloginsaml_backend/oneloginsaml_backend.php?sls';
        }

        return [
            'strict' => $this->get_config_parameter('advanced_settings_strict_mode'),
            'debug' => false,
            'sp' => [
                'entityId' => $this->get_config_parameter('advanced_settings_sp_entity_id', $defaultSPEntityId),
                'assertionConsumerService' => [
                    'url' => $acsUrl,
                ],
                'singleLogoutService' => [
                    'url' => $slsUrl,
                ],
                'NameIDFormat' => $this->get_config_parameter('advanced_settings_nameid_format', 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'),
                'x509cert' => $this->get_config_parameter('advanced_settings_sp_x509cert'),
                'privateKey' => $this->get_config_parameter('advanced_settings_sp_privatekey'),
            ],
            'idp' => [
                'entityId' => $this->get_config_parameter('idp_entityid'),
                'singleSignOnService' => [
                    'url' => $this->get_config_parameter('idp_sso'),
                ],
                'singleLogoutService' => [
                    'url' => $this->get_config_parameter('idp_slo'),
                ],
                'x509cert' => $this->get_config_parameter('idp_x509cert'),
            ],
            // Security settings
            'security' => [
                // Authentication context.
                // Set to false and no AuthContext will be sent in the AuthNRequest,
                'requestedAuthnContext' => false,
                /** signatures and encryptions offered */
                // Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
                // will be encrypted.
                'nameIdEncrypted' => $this->get_config_parameter('advanced_settings_nameid_encrypted'),
                // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
                // will be signed.              [The Metadata of the SP will offer this info]
                'authnRequestsSigned' => $this->get_config_parameter('advanced_settings_authn_request_signed'),
                // Indicates whether the <samlp:logoutRequest> messages sent by this SP
                // will be signed.
                'logoutRequestSigned' => $this->get_config_parameter('advanced_settings_logout_request_signed'),
                // Indicates whether the <samlp:logoutResponse> messages sent by this SP
                // will be signed.
                'logoutResponseSigned' => $this->get_config_parameter('advanced_settings_logout_response_signed'),
                /** signatures and encryptions required * */
                // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
                // <samlp:LogoutResponse> elements received by this SP to be signed.
                'wantMessagesSigned' => $this->get_config_parameter('advanced_settings_want_message_signed'),
                // Indicates a requirement for the <saml:Assertion> elements received by
                // this SP to be signed.        [The Metadata of the SP will offer this info]
                'wantAssertionsSigned' => $this->get_config_parameter('advanced_settings_want_assertion_signed'),
                // Indicates a requirement for the NameID received by
                // this SP to be encrypted.
                'wantNameIdEncrypted' => $this->get_config_parameter('advanced_settings_want_assertion_encrypted'),
                'relaxDestinationValidation' => true,
                'wantXMLValidation' => true,
                // Algorithm that the toolkit will use on signing process.
                'signatureAlgorithm' => $this->get_config_parameter('advanced_settings_signature_algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1'),
                // Algorithm that the toolkit will use on digest process.
                'digestAlgorithm' => $this->get_config_parameter('advanced_settings_digest_algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1'),

                'lowercaseUrlencoding' => $this->get_config_parameter('retrieve_params_from_server')
            ],
        ];
    }

    function get_config_parameter($name, $default=null) {
        $value = $this->plgParams->get($this->plugin.'_'.$name);
        if (empty($value)) {
            $value = $default;
        }
        return $value;
    }

    function get_user_from_joomla($matcher, $username, $email) {
        // Get a database object
        $db = JFactory::getDbo();

        switch ($matcher) {
            case 'mail':
                $query = $db->getQuery(true)
                        ->select('id')
                        ->from('#__users')
                        ->where('email=' . $db->quote($email));
                break;
            case 'username':
            default:
                $query = $db->getQuery(true)
                        ->select('id')
                        ->from('#__users')
                        ->where('username=' . $db->quote($username));
                break;
        }

        $db->setQuery($query);
        $result = $db->loadObject();
        return $result;
    }

    function get_mapped_groups($saml_groups) {
        /*
		$prefix = '';
        if ($this->plugin == 'onelogin_saml_backend') {
            $prefix = 'onelogin_saml_backend_';
            $max = 11;
        } else {
            $max = 21;
        }

        $groups = [];

        if (!empty($saml_groups)) {
            $saml_mapped_groups = [];
            $i = 1;
            while ($i < $max) {
                $saml_mapped_groups_value = $this->plgParams->get($prefix.'group' . $i . '_map');
                if (!empty($saml_mapped_groups_value)) {
                    $saml_mapped_groups[$i] = explode(',', $saml_mapped_groups_value);
                } else {
                    $saml_mapped_groups[$i] = [];
                }
                $i++;
            }
        }

        foreach ($saml_groups as $saml_group) {
            if (!empty($saml_group)) {
                $i = 0;
                $found = false;
                while ($i < $max && !$found) {
                    if (!empty($saml_mapped_groups[$i]) && in_array($saml_group, $saml_mapped_groups[$i])) {
                        $groups[] = $this->plgParams->get($prefix.'group' . $i);
                        $found = true;
                    }
                    $i++;
                }
            }
        }

        return array_unique($groups);
		*/
		
		$prefix = '';
        if ($this->plugin == 'onelogin_saml_backend') {
			$mappings = $params->get(onelogin_saml_backend_.'mappings');
        } else {
			$mappings = $params->get('mappings');
		}
		
		
		$arr = (array) $mappings;
		
		$groups = array();
		$i=0;

		foreach ($arr as $value)
		{
			if (!empty($value->group) and !empty(trim($value->group)) ) {
				$groups[$i]['group'] = $value->group;
				$groups[$i]['group_map'] = $value->group_map;
			}

			$i++;
		}
		
		return array_unique($groups);
		
    }
}
