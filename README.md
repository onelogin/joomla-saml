joomla-onelogin
===============

Joomla 3.3 SAML Authentication plugin based on OneLogin PHP SAML Toolkit.

This plugin enables your Joomla users to log in through SAML.


Features
--------

* Single sign on
* Single log out
* Just on time provisioning
* Supports groups


Pre-requisites
--------------

Take a look on the php saml toolkit dependences:
https://github.com/onelogin/php-saml#dependences


Installation
------------

At the admin interface, click on Extensions > Extension Manager. 
plg_user_oneloginsaml.zip (the plugin) and onelogin.zip (the library) must be installed.


Settings
--------

At the admin interface, click on Extensions > Plugin Manager. Search "Onelogin SAML". Click on the name of the plugin. 
At the "Description" tab you will find info describing the rest of the tabs. Once the settings are set, turn enable the plugin.  

The metadata of the Joomla SP will be available at
``` 
http://<path to joomla/plugins/user/oneloginsaml/oneloginsaml.php?metadata
```

How to add "SAML Login" link
----------------------------

The "SAML Login" link can be added in at least 2 different places:

* Add the link to the "Login Form module". At the admin interface, click on Extensions > Module Manager and search the
   word "Login", in the result you can find the active modules that currently are rendering a Login Form. Edit them and in the
   pre-text add the following:

```
   <a href="http://<path to joomla>/plugins/user/oneloginsaml/oneloginsaml.php?sso">SAML Login</a>
```

* Add the link to the main login form (Component User, View login). At the admin interface, click on Extensions > Module Manager
   and edit the "Site" templates that are currently used. Click on "Create Overrides" and select at "Components" the "com_users" > "login". Later click on the editor and edit html > com_users > login > default_login.php. You will see the a mix of php and html, search the line around 78 and after the JLOGIN button set:

``` 
   <a href="http://<path to joomla>/plugins/user/oneloginsaml/oneloginsaml.php?sso" style="padding-left:20px;">SAML Login</a>
```

Local Login
-----------

When SAML enabled, you can always continue login through other login backends.
Maybe we will disable the local login in future but will provide a way to rescue the system in case that something go wrong with SAML.
