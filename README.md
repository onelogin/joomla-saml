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
http://<path to joomla/plugins/user/oneloginsaml/oneloginsaml.php?metadata
```
and for the Backend:
``` 
http://<path to joomla/plugins/user/oneloginsaml_backend/oneloginsaml_backend.php?metadata
```

How to add "SAML Login" link to the User Login
----------------------------------------------

The "SAML Login" link can be added in at least 2 different places:

* Add the link to the "Login Form module". At the admin interface, click on Extensions > Module Manager and search the
   word "Login", in the result you can find the active modules that currently are rendering a Login Form. Edit them and in the
   pre-text add the following:

```
   <a href="http://<path to joomla>/plugins/user/oneloginsaml/oneloginsaml.php?sso">SAML Login</a>
```

* The SAML Link at the main login form is handled by a flag of the option section of the OneLogin plugin


How to add "SAML Login" link to the Admin Login
-----------------------------------------------

At the admin interface, click on Extensions > Templates > Template. Switch to the Administrator templates. Select
the one you are using. Edit the login.php file:

After the line 122:
```
<jdoc:include type="component" />
```
Add:
```
<?php echo '<div ><center><hr><a class="btn btn-primary" href="'.JRoute::_(JUri::root().'plugins/user/oneloginsaml_backend/oneloginsaml_backend.php?sso', true).'">SAML Login</a><hr></center></div>'; ?>
```


Local Login
-----------

When SAML enabled, you can always continue login through other login backends.
Maybe we will disable the local login in future but will provide a way to rescue the system in case that something go wrong with SAML.

Contributors
------------

- Michael Andrzejewski <michael@jetskitechnologies.com>
  Refactored and packetized the plugins.

- Université du Québec à Montréal. 
  Sponsored backend integration.