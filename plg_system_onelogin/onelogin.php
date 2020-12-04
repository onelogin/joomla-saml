<?php

/**
 * @copyright   Copyright (C) 2019 OneLogin, Inc. All rights reserved.
 * @license     MIT
 * @author Michael Andrzejewski <michael@jetskitechnologies.com>
 */

/**
 * OneLogin Plugin class
 * 
 *  @package     OneLogin PHP-SAML Library
 *  @subpackage  OneLogin.PHP-SAML.JoomlaLoader
 */
class plgSystemOnelogin extends JPlugin {

    /**
     * Register the library into the Joomla application
     */
    public function onAfterInitialise() {
        JLoader::register('XMLSecurityKey',                     JPATH_LIBRARIES . '/onelogin/extlib/xmlseclibs/xmlseclibs.php');
        JLoader::register('XMLSecurityDSig',                    JPATH_LIBRARIES . '/onelogin/extlib/xmlseclibs/xmlseclibs.php');
        JLoader::register('XMLSecEnc',                          JPATH_LIBRARIES . '/onelogin/extlib/xmlseclibs/xmlseclibs.php');
        JLoader::register('OneLogin_Saml2_Auth',                JPATH_LIBRARIES . '/onelogin/lib/Saml2/Auth.php');
        JLoader::register('OneLogin_Saml2_AuthnRequest',        JPATH_LIBRARIES . '/onelogin/lib/Saml2/AuthnRequest.php');
        JLoader::register('OneLogin_Saml2_Constants',           JPATH_LIBRARIES . '/onelogin/lib/Saml2/Constants.php');
        JLoader::register('OneLogin_Saml2_Error',               JPATH_LIBRARIES . '/onelogin/lib/Saml2/Error.php');
        JLoader::register('OneLogin_Saml2_ValidationError',     JPATH_LIBRARIES . '/onelogin/lib/Saml2/Error.php');
        JLoader::register('OneLogin_Saml2_IdPMetadataParser',   JPATH_LIBRARIES . '/onelogin/lib/Saml2/IdPMetadataParser.php');
        JLoader::register('OneLogin_Saml2_LogoutRequest',       JPATH_LIBRARIES . '/onelogin/lib/Saml2/LogoutRequest.php');
        JLoader::register('OneLogin_Saml2_LogoutResponse',      JPATH_LIBRARIES . '/onelogin/lib/Saml2/LogoutResponse.php');
        JLoader::register('OneLogin_Saml2_Metadata',            JPATH_LIBRARIES . '/onelogin/lib/Saml2/Metadata.php');
        JLoader::register('OneLogin_Saml2_Response',            JPATH_LIBRARIES . '/onelogin/lib/Saml2/Response.php');
        JLoader::register('OneLogin_Saml2_Settings',            JPATH_LIBRARIES . '/onelogin/lib/Saml2/Settings.php');
        JLoader::register('OneLogin_Saml2_Utils',               JPATH_LIBRARIES . '/onelogin/lib/Saml2/Utils.php');
        JLoader::register('OneLogin_Saml2_Auth_Joomla',         JPATH_LIBRARIES . '/onelogin/loader.php');
    }
}
