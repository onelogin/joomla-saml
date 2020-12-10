<?php

/*
 *  @copyright   Copyright (C) 2019 OneLogin, Inc. All rights reserved.
 *  @license     MIT
 *  @author Michael Andrzejewski <michael@jetskitechnologies.com>
 */

/**
 * Wrapper for the library interface to make things easier.
 *
 * @author Michael Andrzejewski<michael@jetskitechnologies.com>
 */
class OneLogin_Saml2_Auth_Joomla extends Onelogin_Saml2_Auth {

    public function __construct($plgParams) {
        parent::__construct($this->formatSettings($plgParams));
    }
    
    protected function formatSettings($saml_params) {
        return array(
            'strict' => $saml_params->get('onelogin_saml_advanced_settings_strict_mode'),
            'debug' => $saml_params->get('onelogin_saml_advanced_settings_debug'),
            'sp' => array(
                'entityId' => ($saml_params->get('onelogin_saml_advanced_settings_sp_entity_id') ? $saml_params->get('onelogin_saml_advanced_settings_sp_entity_id') : 'php-saml'),
                'assertionConsumerService' => array(
                    'url' => JURI::root() . 'oneloginsaml.php?acs',
                ),
                'singleLogoutService' => array(
                    'url' => JURI::root() . 'oneloginsaml.php?sls',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'x509cert' => $saml_params->get('onelogin_saml_advanced_settings_sp_x509cert'),
                'privateKey' => $saml_params->get('onelogin_saml_advanced_settings_sp_privatekey'),
            ),
            'idp' => array(
                'entityId' => $saml_params->get('onelogin_saml_idp_entityid'),
                'singleSignOnService' => array(
                    'url' => $saml_params->get('onelogin_saml_idp_sso'),
                ),
                'singleLogoutService' => array(
                    'url' => $saml_params->get('onelogin_saml_idp_slo'),
                ),
                'x509cert' => $saml_params->get('onelogin_saml_idp_x509cert'),
            ),
            // Security settings
            'security' => array(
                /** signatures and encryptions offered */
                // Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
                // will be encrypted.
                'nameIdEncrypted' => $saml_params->get('onelogin_saml_advanced_settings_nameid_encrypted'),
                // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
                // will be signed.              [The Metadata of the SP will offer this info]
                'authnRequestsSigned' => $saml_params->get('onelogin_saml_advanced_settings_authn_request_signed'),
                // Indicates whether the <samlp:logoutRequest> messages sent by this SP
                // will be signed.
                'logoutRequestSigned' => $saml_params->get('onelogin_saml_advanced_settings_logout_request_signed'),
                // Indicates whether the <samlp:logoutResponse> messages sent by this SP
                // will be signed.
                'logoutResponseSigned' => $saml_params->get('onelogin_saml_advanced_settings_logout_response_signed'),
                /** signatures and encryptions required * */
                // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
                // <samlp:LogoutResponse> elements received by this SP to be signed.
                'wantMessagesSigned' => $saml_params->get('onelogin_saml_advanced_settings_want_message_signed'),
                // Indicates a requirement for the <saml:Assertion> elements received by
                // this SP to be signed.        [The Metadata of the SP will offer this info]
                'wantAssertionsSigned' => $saml_params->get('onelogin_saml_advanced_settings_want_assertion_signed'),
                // Indicates a requirement for the NameID received by
                // this SP to be encrypted.
                'wantNameIdEncrypted' => $saml_params->get('onelogin_saml_advanced_settings_want_assertion_encrypted'),
                'relaxDestinationValidation' => true,
            ),
        );
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

    function get_mapped_groups($saml_params, $saml_groups) {
        $groups = array();

        if (!empty($saml_groups)) {
            $saml_mapped_groups = array();
            $i = 1;
            while ($i < 21) {
                $saml_mapped_groups_value = $saml_params->get('group' . $i . '_map');
                $saml_mapped_groups[$i] = explode(',', $saml_mapped_groups_value);
                $i++;
            }
        }

        foreach ($saml_groups as $saml_group) {
            if (!empty($saml_group)) {
                $i = 0;
                $found = false;
                while ($i < 21 && !$found) {
                    if (!empty($saml_mapped_groups[$i]) && in_array($saml_group, $saml_mapped_groups[$i])) {
                        $groups[] = $saml_params->get('group' . $i);
                        $found = true;
                    }
                    $i++;
                }
            }
        }

        return array_unique($groups);
    }

}
