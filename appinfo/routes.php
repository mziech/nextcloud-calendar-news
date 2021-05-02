<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\CalendarNews\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'page#simple', 'url' => '/simple', 'verb' => 'GET'],
        ['name' => 'page#lastNewsletter', 'url' => '/last-newsletter', 'verb' => 'GET'],
        ['name' => 'page#nextNewsletter', 'url' => '/next-newsletter', 'verb' => 'GET'],
        ['name' => 'config#getCalendars', 'url' => '/calendars', 'verb' => 'GET'],
        ['name' => 'config#preview', 'url' => '/preview', 'verb' => 'POST'],
        ['name' => 'config#get', 'url' => '/config', 'verb' => 'GET'],
        ['name' => 'config#post', 'url' => '/config', 'verb' => 'POST'],
        ['name' => 'schedule#sendNow', 'url' => '/send-now', 'verb' => 'POST'],
        ['name' => 'schedule#removeLast', 'url' => '/remove-last', 'verb' => 'POST'],
        ['name' => 'schedule#get', 'url' => '/schedule', 'verb' => 'GET'],
        ['name' => 'schedule#previewNext', 'url' => '/schedule/preview', 'verb' => 'POST'],
        ['name' => 'schedule#post', 'url' => '/schedule', 'verb' => 'POST'],
    ]
];
