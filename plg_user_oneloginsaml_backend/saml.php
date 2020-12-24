<?php
/**
 * @package     OneLogin SAML.Plugin
 * @subpackage  User.oneloginsaml_backend
 *
 * @copyright   Copyright (C) 2020 OneLogin, Inc. All rights reserved.
 * @license     MIT
 */

define('_JEXEC', 1);

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))).'/administrator');
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_BASE . '/includes/helper.php';
require_once JPATH_BASE . '/includes/subtoolbar.php';

// Load onelogin library
require_once JPATH_LIBRARIES.'/onelogin/loader.php';

// Instantiate the application.
$app = JFactory::getApplication('administrator');

JPluginHelper::importPlugin('system');
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onAfterInitialise');

$baseUrl = str_replace("plugins/user/", "", str_replace("plugins/user/oneloginsaml_backend/", "", JUri::root())).'administrator/';

$login_url = $baseUrl.'index.php';
$logout_url = $baseUrl.'index.php?option=com_login&task=logout';

$oneLoginPlugin = JPluginHelper::getPlugin('user', 'oneloginsaml_backend');

if (!$oneLoginPlugin) {
    throw new Exception("Onelogin SAML Plugin not active");
}

$user = JFactory::getUser();
$session = JFactory::getSession();

jimport('joomla.html.parameter');
$plgParams = new JRegistry();
if ($oneLoginPlugin && isset($oneLoginPlugin->params)) {
    $plgParams->loadString($oneLoginPlugin->params);
}

$saml_joomla = new Saml_Joomla($plgParams, 'onelogin_saml_backend');

if (isset($_GET['metadata'])) {
    $saml_settings = $saml_joomla->getSamlSettings(true);
    $metadata = $saml_settings->getSPMetadata();
    $errors = $saml_settings->validateMetadata($metadata);
    if (empty($errors)) {
        header('Content-Type: text/xml');
        echo $metadata;
    } else {
        throw new OneLogin_Saml2_Error(
            'Invalid SP metadata: '.implode(', ', $errors),
            OneLogin_Saml2_Error::METADATA_SP_INVALID
        );
    }
} else {
    $saml_auth = $saml_joomla->getSamlAuth();
    if (isset($_GET['sso'])) {
        $saml_auth->login();
    } else if (isset($_GET['slo'])) {
        if ($plgParams->get('onelogin_saml_backend_slo')) {
            $session = JFactory::getSession();
            if ($session->get('saml_backend_login')) {
                $nameId = $session->get('saml_backend_nameid', null);
                $nameIdFormat = $session->get('saml_backend_nameid_format', null);
                $sessionIndex = $session->get('saml_backend_sessionindex', null);
                $nameIdNameQualifier = $session->get('saml_backend_nameid_namequalifier', null);
                $nameIdSPNameQualifier = $session->get('saml_backend_nameid_spnamequalifier', null);

                $saml_auth->logout(null, [], $nameId, $sessionIndex, false, $nameIdFormat, $nameIdNameQualifier, $nameIdSPNameQualifier);
            } else {
                sendMessage($app, $logout_url);
            }
        } else {
            sendMessage($app, $baseUrl, "SLO disabled", 'error');
        }
    } else if (isset($_GET['acs'])) {
        $saml_auth->processResponse();

        jimport('joomla.user.authentication');
        $authenticate = JAuthentication::getInstance();
        $response = new JAuthenticationResponse();
        if (!$saml_auth->isAuthenticated()) {
            $errorMsg = 'NO_AUTHENTICATED';
            $errors = $saml_auth->getErrors();

            if (!empty($errors)) {
                $errorMsg .= '<br>'.implode(', ', $errors);
                if ($plgParams->get('onelogin_saml_backend_advanced_settings_debug')) {
                    $errorMsg .= '<br>'.$saml_auth->getLastErrorReason();
                }
            }

            $response->status = JAuthentication::STATUS_FAILURE;
            $response->message = $errorMsg;
            sendMessage($app, $login_url, $errorMsg, 'error');
        }
        $attrs = $saml_auth->getAttributes();

        $username = '';
        $email = '';
        $name = '';

        if (empty($attrs)) {
            $username = $saml_auth->getNameId();
            $email = $username;
        } else {
            $nameMapping = $plgParams->get('onelogin_saml_backend_attr_mapping_name');
            $usernameMapping = $plgParams->get('onelogin_saml_backend_attr_mapping_username');
            $mailMapping = $plgParams->get('onelogin_saml_backend_attr_mapping_mail');
            $groupsMapping = $plgParams->get('onelogin_saml_backend_attr_mapping_groups');
            if (!empty($usernameMapping) && isset($attrs[$usernameMapping]) && !empty($attrs[$usernameMapping][0])) {
                $username = $attrs[$usernameMapping][0];
            }
            if (!empty($mailMapping) && isset($attrs[$mailMapping]) && !empty($attrs[$mailMapping][0])) {
                $email = $attrs[$mailMapping][0];
            }
            if (!empty($nameMapping) && isset($attrs[$nameMapping]) && !empty($attrs[$nameMapping][0])) {
                $name = $attrs[$nameMapping][0];
            }
            if (!empty($groupsMapping) && isset($attrs[$groupsMapping]) && !empty($attrs[$groupsMapping])) {
                $saml_groups = $attrs[$groupsMapping];
            } else {
                $saml_groups = array();
            }
        }

        $matcher = $plgParams->get('onelogin_saml_backend_account_matcher', 'username');

        if (empty($username) && $matcher == 'username') {
            $errorMsg = 'NO USERNAME';
            $response->status = JAuthentication::STATUS_FAILURE;
            $response->message = $errorMsg;
            sendMessage($app, $login_url, $errorMsg, 'error');
        }
        if (empty($email) && $matcher == 'mail') {
            $errorMsg = 'NO MAIL';
            $response->status = JAuthentication::STATUS_FAILURE;
            $response->message = $errorMsg;
            sendMessage($app, $login_url, $errorMsg, 'error');
        }

        $result = $saml_joomla->get_user_from_joomla($matcher, $username, $email);

        if (!$result) {
            // User not found, check if could be created
            $autocreate = $plgParams->get('onelogin_saml_backend_autocreate');

            if ($autocreate) {
                if (empty($username)) {
                    $username = $email;
                }

                // user data
                $data['name'] = (isset($name) && !empty($name)) ? $name : $username;
                $data['username'] = $username;
                $data['email'] = $data['email1'] = $data['email2'] = JStringPunycode::emailToPunycode($email);
                $data['password'] = $data['password1'] = $data['password2'] = null;

                $groups = $saml_joomla->get_mapped_groups($saml_groups);
                if ($plgParams->get('onelogin_saml_backend_require_group')) {
                    if (empty($groups)) {
                            $errorMsg = 'GROUP DATA REQUIRED';
                            $response->status = JAuthentication::STATUS_FAILURE;
                            $response->message = $errorMsg;
                            sendMessage($app, $login_url, $errorMsg, 'error');
                    } else {
                        $permission = false;
                        foreach ($groups as $group) {
                            if (JAccess::checkGroup($group, 'core.login.admin')) {
                                $permission = true;
                                break;
                            }
                        }

                        if (!$permission) {
                            $errorMsg = 'GROUP DATA PROVIDED DONT BELONG ADMIN AREA';
                            $response->status = JAuthentication::STATUS_FAILURE;
                            $response->message = $errorMsg;
                            sendMessage($app, $login_url, $errorMsg, 'error');
                        }
                    }
                }

                // Get the model and validate the data.
                jimport('joomla.application.component.model');

                if (!defined('JPATH_COMPONENT')) {
                    define('JPATH_COMPONENT', JPATH_BASE . '/components/');
                }

                JModelLegacy::addIncludePath(JPATH_COMPONENT . '/com_users/models');
                $model = JModelLegacy::getInstance('User', 'UsersModel');

                $return = $model->save($data);
                if ($return !== false) {
                    $result = $saml_joomla->get_user_from_joomla($matcher, $username, $email);
                }

                if ($return === false || !isset($result)) {
                    $errors = $model->getErrors();
                    $errorMsg = 'USER NOT EXISTS AND FAILED THE CREATION PROCESS.'.implode(', ', $errors);
                    $response->status = JAuthentication::STATUS_FAILURE;
                    $response->message = $errorMsg;
                    sendMessage($app, $login_url, $errorMsg, 'error');
                }

                $user = JUser::getInstance($result->id);

                $user->set('block', '0');
                $user->set('activation', '');
                $user->save();

                if (empty($groups)) {
                    $groups[] = $plgParams->get('onelogin_saml_backend_default_group');
                }

                $user->set('groups', $groups);
                $user->save();

                $response->status = JAuthentication::STATUS_SUCCESS;
                $response->message = "Welcome $user->username";
                $session->set('user', $user);

                // SSO SAML Login flag
                $session->set('saml_backend_login', 1);

                sendMessage($app, $login_url, "Welcome $user->username", 'message');
            } else {
                $errorMsg = 'USER DOES NOT EXIST AND NOT ALLOWED TO CREATE';
                $response->status = JAuthentication::STATUS_FAILURE;
                $response->message = $errorMsg;
                sendMessage($app, $login_url, $errorMsg, 'error');
            }
        } else {
            $user = JUser::getInstance($result->id);

            $groups = $saml_joomla->get_mapped_groups($saml_groups);

            // User found, check if data should be update
            $autoupdate = $plgParams->get('onelogin_saml_backend_updateuser');

            // Check if can login in admin
            $permission = $user->authorise('core.login.admin');

            if (!$permission) {
                if ($autoupdate) {
                    foreach ($groups as $group) {
                        if (JAccess::checkGroup($group, 'core.login.admin')) {
                            $permission = true;
                            break;
                        }
                    }
                }

                if (!$permission) {
                    $errorMsg = 'USER DOES NOT HAVE PRIVILEGES';
                    $response->status = JAuthentication::STATUS_FAILURE;
                    $response->message = $errorMsg;
                    sendMessage($app, $login_url, $errorMsg, 'error');
                }
            }

            if ($autoupdate) {
                if (isset($name) && !empty($name)) {
                    $user->set('name', $name);
                    $user->save();
                }

                $groups = $saml_joomla->get_mapped_groups($saml_groups);
                if (!empty($groups)) {
                    $user->set('groups', $groups);
                    $user->save();
                }
            }

            $response->status == JAuthentication::STATUS_SUCCESS;
            $session->set('user', $user);

            // SSO SAML Login flag
            $session->set('saml_backend_login', 1);

            $nameId = $saml_auth->getNameId();
            $session->set('saml_backend_nameid', $nameId);

            $nameIdFormat = $saml_auth->getNameIdFormat();
            $session->set('saml_backend_nameid_format', $nameIdFormat);

            $sessionIndex = $saml_auth->getSessionIndex();
            $session->set('saml_backend_sessionindex', $sessionIndex);

            $nameIdNameQualifier = $saml_auth->getNameIdNameQualifier();
            $session->set('saml_backend_nameid_namequalifier', $nameIdNameQualifier);

            $nameIdSPNameQualifier = $saml_auth->getNameIdSPNameQualifier();
            $session->set('saml_backend_nameid_spnamequalifier', $nameIdSPNameQualifier);

            sendMessage($app, $login_url, "Welcome $user->username", 'message');
        }
    } else if (isset($_GET['sls'])) {
        if ($plgParams->get('onelogin_saml_backend_slo')) {
            $session = JFactory::getSession();
            if ($session->get('saml_backend_login')) {
                $saml_auth->processSLO();
                $errors = $saml_auth->getErrors();
                if (empty($errors)) {
                    // TODO Do local logout
                    sendMessage($app, $login_url, 'Sucessfully logged out', 'message');
                } else {
                    sendMessage($app, $login_url, implode(', ', $errors), 'error');
                }
            } else {
                sendMessage($app, $logout_url);
            }
        } else {
            sendMessage($app, $baseUrl, "SLO disabled", 'error');
        }
    } else {
        throw new Exception("No action selected, set one of those GET parameters: 'sso', 'slo', 'acs', 'sls' or 'metadata' .");
    }
}


function sendMessage($app, $url, $message=null, $type=null) {
    if (!empty($message)) {
        $app->enqueueMessage($message, $type);
    }
    $app->redirect($url);
}