joomla-onelogin
===============

Joomla 3.3 SAML Authentication plugin based on OneLogin PHP SAML Toolkit.

This plugin enables your Joomla users to log in through SAML.

joomlsa-saml version 2.0.0 updates php-saml library to 3.5.1 and add support for Admin login

joomlsa-saml version 1.6.0 updates php-saml library to 2.15.0 (it includes XEE attack prevention).
Previous versions are vulnerable.

joomlsa-saml version 1.3.0 updates php-saml library to 2.10.0 (it includes SAML Signature Wrapping attack prevention and other security improvements).
Previous versions are vulnerable.

Features
--------

* Single sign on
* Single log out
* Just on time provisioning
* Supports groups
* User and Admin Logins
* SAML Login Link injection
* Force SAML Login


Pre-requisites
--------------

Take a look on the php saml toolkit dependences:
https://github.com/onelogin/php-saml#dependences


Installation
------------

At the admin interface, click on Extensions > Extension Manager. 
Unlike previous versions there is now a single pkg_onelogin_php-saml_joomla.zip to upload and install.


Settings
--------

At the admin interface, click on Extensions > Plugin Manager. Search "Onelogin". 
Enable both plugins by clicking on the applicable boxes. Click on the name of the user plugin. 
At the "Description" tab you will find info describing the rest of the tabs. Once the settings are set, turn enable the plugin.

The metadata of the Joomla SP will be available at
``` 
http://<path to joomla/plugins/system/oneloginsaml/oneloginsaml.php?metadata
```
and for the Backend:
``` 
http://<path to joomla/plugins/system/oneloginsaml_backend/oneloginsaml_backend.php?metadata
```

Inject SAML Link
----------------

In the Option section, there is a flag that you can enable in
order to inject a SAML Link 


Force SAML Login
----------------

You can force users and admins to execute SAML logins by enabling a flag at the Option section.

When this flag is enabled, if users try to access any Joomla
view and there is no session, it will be redirected to the IdP.


Contributors
------------

- Michael Andrzejewski <michael@jetskitechnologies.com>
  Refactored and packetized the plugins.

- Université du Québec à Montréal. 
  Sponsored backend integration.