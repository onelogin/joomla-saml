<?php

// No direct access
defined('_JEXEC') or die;

$sp_entity_id = $saml_params->get('onelogin_saml_advanced_settings_sp_entity_id');

$saml_settings = array (
    'strict' => $saml_params->get('onelogin_saml_advanced_settings_debug'),
    'debug' => $saml_params->get('onelogin_saml_advanced_settings_strict_mode'),
    'sp' => array (
        'entityId' => ($sp_entity_id ? $sp_entity_id : 'php-saml'),
        'assertionConsumerService' => array (
            'url' => JURI::root().'oneloginsaml.php?acs',
        ),
        'singleLogoutService' => array (
            'url' => JURI::root().'oneloginsaml.php?sls',
        ),
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified',
        'x509cert' => $saml_params->get('onelogin_saml_advanced_settings_sp_x509cert'),
        'privateKey' => $saml_params->get('onelogin_saml_advanced_settings_sp_privatekey'),
    ),

    'idp' => array (
        'entityId' => $saml_params->get('onelogin_saml_idp_entityid'),
        'singleSignOnService' => array (
            'url' => $saml_params->get('onelogin_saml_idp_sso'),
        ),
        'singleLogoutService' => array (
            'url' => $saml_params->get('onelogin_saml_idp_slo'),
        ),
        'x509cert' => $saml_params->get('onelogin_saml_idp_x509cert'),
    ),

    // Security settings
    'security' => array (

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

        /** signatures and encryptions required **/

        // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        // <samlp:LogoutResponse> elements received by this SP to be signed.
        'wantMessagesSigned' => $saml_params->get('onelogin_saml_advanced_settings_want_message_signed'),

        // Indicates a requirement for the <saml:Assertion> elements received by
        // this SP to be signed.        [The Metadata of the SP will offer this info]
        'wantAssertionsSigned' => $saml_params->get('onelogin_saml_advanced_settings_want_assertion_signed'),

        // Indicates a requirement for the NameID received by
        // this SP to be encrypted.
        'wantNameIdEncrypted' => $saml_params->get('onelogin_saml_advanced_settings_want_assertion_encrypted'),
    ),
);
