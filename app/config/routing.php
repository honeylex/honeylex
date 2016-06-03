<?php

// everything in here will be mounted at the top level of the apps urls namespace

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig');
});
