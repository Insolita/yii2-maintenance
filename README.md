Custom mantenance component
===========================

 * Component for maintenance mode behavior, focused on retrieve status from external configuration
 * Support ability for allow exclusive access in maintenance mode for specified ips
 * Support preliminar notice about soon maintenance works
 * Provide observable events when  maintenance process or preliminar modes

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist insolita/yii2-maintenance "*"
```

or add

```
"insolita/yii2-maintenance": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, add in config components section, and also bootstrap section  :

```php
     'bootstrap'=>['log','config','maintenance'],
     'container'=>[
     ...
         'definitions'=>[
             '\insolita\maintenance\IConfig'=>'your\config\component\class'
         ]
     ],
     ...
     'components'=>[
          'maintenance'=>[
              'class'=>'\insolita\maintenance\Maintenance',
              'enabledKey'=>'config key for maintennce indication',
              'preliminarKey'=>'config key for preliminar maintenance indication',
              'ipSkippedKey'=>'config key for comma-seperated ips with exclusive access',
              'catchRoute'=>['site/maintenance'], //- route catched all requests in maintenance mode
              //possible events use cases
              'on maintenance_process'=>function($event){
                  //$event->sender is maintenance component!
                  if($event->sender->isSkipForIp===true){
                     Yii::$app->getSession()->setFlash('warning','Site in maintenance mode!');
                  }
              },
              'on maintenance_soon'=>function($event){
                  Yii::$app->getSession()->setFlash('warning',
                  'After 5 minutes, the site will be serviced, please finish or save the undelivered messages');
               }
          ]
     ],

```