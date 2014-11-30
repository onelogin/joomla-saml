<?php

// No direct access
defined('_JEXEC') or die;

function onelogin_saml_instance($saml_params)
{
    $saml_auth = false;
    require_once '_toolkit_loader.php';
    require_once 'settings.php';

    $saml_auth = new Onelogin_Saml2_Auth($saml_settings);
    return $saml_auth;
}

function get_user_from_joomla($matcher, $username, $email)
{
    // Get a database object
    $db    = JFactory::getDbo();
    
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

function get_mapped_groups($saml_params, $saml_groups)
{
    $groups = array();

    if (!empty($saml_groups)) {
        $saml_mapped_groups = array();
        $i=1;
        while ($i < 21) {
            $saml_mapped_groups_value = $saml_params->get('group'.$i.'_map');
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
                    $groups[] = $saml_params->get('group'.$i);
                    $found = true;
                }
                $i++;
            }
        }
    }

    return array_unique($groups);
}
