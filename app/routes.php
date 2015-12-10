<?php

use Symfony\Component\HttpFoundation\Request;
use WebLinks\Domain\Link;
use WebLinks\Form\Type\LinkType;

// Home page
$app->get('/', function () use ($app) {
    $links = $app['dao.link']->findAll();
    return $app['twig']->render('index.html.twig', array('links' => $links));
})->bind('home');

// Login form
$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');

// Add a new link
$app->match('/link', function (Request $request) use ($app){
    $linkFormView = null;
    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')){
        // A user is fully authenticated : he can add comments
        $link = new link();
        $user = $app['user'];
        $link->setAuthor($user);
        $linkForm = $app['form.factory']->create(new LinkType(), $link);
        $linkForm->handleRequest($request);
        if ($linkForm->isSubmitted() && $linkForm->isValid()) {
            $app['dao.link']->save($link);
            $app['session']->getFlashBag()->add('success', 'Your comment was succesfully added.');
        }
        $linkFormView = $linkForm->createView();
    }
    return $app['twig']->render('link.html.twig', array(
        //'links' => $links,
        'linkForm' => $linkFormView));
})->bind('link');

// Admin home page
$app->get('/admin', function() use ($app) {
    $links = $app['dao.link']->findAll();
    $users = $app['dao.user']->findAll();
    return $app['twig']->render('admin.html.twig', array(
        'links' => $links,
        'users' => $users));
})->bind('admin');

// Edit an existing link
$app->match('/admin/link/{id}/edit', function($id, Request $request) use ($app) {
    $link = $app['dao.link']->find($id);
    $linkForm = $app['form.factory']->create(new LinkType(), $link);
    $linkForm->handleRequest($request);
    if ($linkForm->isSubmitted() && $linkForm->isValid()) {
        $app['dao.link']->save($link);
        $app['session']->getFlashBag()->add('success', 'The link was succesfully updated.');
    }
    return $app['twig']->render('link_form.html.twig', array(
        'title' => 'Edit link',
        'linkForm' => $linkForm->createView()));
})->bind('admin_link_edit');

// Remove an link
$app->get('/admin/link/{id}/delete', function($id, Request $request) use ($app) {
    // Delete the link
    $app['dao.link']->delete($id);
    $app['session']->getFlashBag()->add('success', 'The link was succesfully removed.');
    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_link_delete');
