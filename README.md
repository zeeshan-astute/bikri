<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Advanced Project Template</h1>
    <br>
</p>

Yii 2 Advanced Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-advanced.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-advanced)

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
-----------------------------------------------------------------------------------------------
Add-Ons included in this Package.
================================

Image Moderation Add-on: 
-----------------------
In Frontend Folder,
    1. Replace file frontend/controller/usercontroller.php
    2. Replace file frontend/controller/productcontroller.php
    3. Replace file frontend/web/js/front.js

In Backend Folder,
    1. Replace file backend/controller/productcontroller.php
    2. Replace file backend/web/admin/js/front.js


Google Adsense Verification Add-on:
----------------------------------

In Frontend Folder,
    1. Edit file frontend/views/products/view.php
    2. Edit file frontend/views/message/index.php
    3. Edit file frontend/views/user/sidebar.php

In Backend Folder,
    1. Edit file backend/controller/sitesettingscontroller.php
    2. Edit file backend/views/admin/sidebar.php
    3. Edit file backend/views/roles/add.php
    4. Edit file backend/views/roles/form.php
    5. Add file backedn/views/sitesettings/adsensesettings.php

In Common Folder,
    1. Edit file common/models/Sitesettings.php

Google Captcha Verification Add-on:
----------------------------------
In Frontend Folder,
    1. Edit file frontend/controller/sitecontroller.php
    2. Edit file frontend/views/site/signup.php
    3. Edit file frontend/views/site/header.php

RTL - Arabic Add-on:
----------------------------------
In Frontend Folder,
    1. Edit file frontend/views/site/footer.php
    2. Edit file frontend/assets/appasset.php
    3. Edit file frontend/assets/styleasset.php

Add the ar folder in frontend/messages.


Login with PhoneNumber Add-on:
------------------------------
In Frontend Folder,
    1. Edit file frontend/controller/sitecontroller.php
    2. Edit file frontend/views/site/signup.php
    3. Edit file frontend/views/site/header.php
    4. Edit file frontend/views/site/login.php
    5. Edit file frontend/models/signupform.php

Need to Add the phonelogin page details in frontend/config/main.php 

Single Country Add-on:
------------------------------
In Frontend Folder,
    1. Edit file frontend/views/products/form.php
    2. Edit file frontend/views/layouts/main.php
    3. Edit file frontend/views/user/message.php


MapBox Add-on:
-----------------

In Frontend Folder,
    1. Replace file frontend/views/layouts/main.php
    2. Replace file frontend/views/layouts/chat.php
    3. Replace file frontend/views/site/header.php
    4. Replace file frontend/views/user/message.php
    5. Replace file frontend/views/message/index.php
    6. Replace file frontend/views/message/message.php
    7. Replace file frontend/views/products/form.php
    8. Replace file frontend/views/products/updateform.php
    9. Replace file frontend/views/products/view.php
    10. Replace file frontend/web/js/front.js

In Backend Folder,
    1. Replace file backend/views/products/update.php

Need to Add the js and css files in the corresponding folder.

User Limitations:
-----------------

In Backend Folder,
    1. Add file backend/controllers/FreelistingController.php
    2. Add file backend/models/FreelistingSearch.php
    3. Edit file backend/views/admin/sidebar.php
    4. Edit file backend/views/sitesettings/defaultsettings.php

    Add folder backend/views/freelisting folder

In Common Folder,
    1. Add file common/models/Freelisting.php
    2. Add file common/models/Subscriptionsdetails.php
    3. Add file common/models/Subscriptiontransaction.php
    4. Edit file common/models/Sitesettings.php
    5. Edit file common/components/MyClass.php

In Frontend Folder,
    1. Add file frontend/views/products/subscriptionpayment.php
    2. Add file frontend/views/products/subscriptionpaymentprocess.php
    3. Edit file frontend/controller/productscontroller.php
    3. Edit file frontend/controller/usercontroller.php
    4. Edit file frontend/views/site/header.php
    5. Add file frontend/views/user/mysubscription.php

Review & Rating Add-on:
-----------------

In Common Folder,
    1. Edit file common/components/MyClass.php

In Frontend Folder,
    1. Edit file frontend/views/products/form.php
    2. Edit file frontend/views/products/updateform.php
    3. Edit file frontend/views/products/update.php
    4. Edit file frontend/views/products/create.php
    5. Edit file frontend/web/js/front.js
    6. Edit file frontend/controllers/productscontroller.php
    7. Edit file frontend/views/user/notification.php
    8. Edit file frontend/config/main.php


Amazon S3 Add-on:
-----------------
Amazon AWS S3 files are Included.

In Common Folder,
    1. Add file common/components/MyAws.php

In Frontend Folder,
    1. Replace file frontend/controller/productcontroller.php
    2. Replace file frontend/views/products/view.php
    3. Replace file frontend/views/site/indexload.php
    4. Replace file frontend/views/site/loadresults.php
    5. Replace file frontend/views/user/profiles.php
    6. Replace file frontend/views/user/loadliked.php
    7. Replace file frontend/controller/UserController.php
    8. Replace file frontend/views/user/sidebar.php
    9. Replace file frontend/views/site/header.php

In Backend Folder,
    1. Replace file backend/controller/productcontroller.php
    2. Replace file backend/views/products/view.php

Pages are Mentioned Below. 


Need to add the AWS SDK for PHP includes a ZIP file in Vendor folder, it contains the classes and dependencies you need to run the SDK.

Need to add the aws-sdk-php in Composer.json file.
--------------------------------------------------------------------------------------------------
