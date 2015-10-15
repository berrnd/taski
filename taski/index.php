<?php

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'Taski.php';

$webApp = new \Slim\Slim();

$webApp->add(new \Slim\Middleware\HttpBasicAuthentication([
    'users' => [
        HTTP_USER => HTTP_PASSWORD
    ],
    'realm' => 'taski',
]));

$webApp->post('/api/add-task', function () use ($webApp)
{
    $taski = new \Taski\Taski();

    $app = $webApp->request->post('app');
    $params = $webApp->request->post('params');

    if (empty($params))
        $params = '';
    
    if ($taski->app_defined($app))
        $taski->execute($app, urldecode($params), 'Task enqueued');
    else
    {
        $webApp->response->headers->set('Content-Type', 'application/json');
        echo json_encode(array('result' => 'Error, application is not a known application'));
        $webApp->response()->status(500);
    }
});

$webApp->get('/api/get-tasks', function () use ($webApp)
{
    $taski = new \Taski\Taski();
    $tasks = $taski->get_tasks();

    $webApp->response->headers->set('Content-Type', 'application/json');
    echo json_encode($tasks);
});

$webApp->get('/', function () use ($webApp)
{
    $taski = new \Taski\Taski();
    $tasks = $taski->get_tasks();
    $apps = $taski->get_apps();

    $data = array('tasks' => $tasks, 'apps' => $apps);
    include 'frontend.php';
});

$webApp->run();

?>