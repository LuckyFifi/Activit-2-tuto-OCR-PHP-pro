<?php

namespace WebLinks\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use WebLinks\Domain\Link;
use WebLinks\Form\Type\LinkType;


class HomeController {

    /**
     * Home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        $links = $app['dao.link']->findAll();
        return $app['twig']->render('index.html.twig', array('links' => $links));
    }
	
	/**
     * Add Link controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function linkAction(Request $request, Application $app) {
        $linkFormView = null;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            // A user is fully authenticated : he can add link
            $link = new link();
            $user = $app['user'];
            $link->setAuthor($user);
            $linkForm = $app['form.factory']->create(new linkType(), $link);
            $linkForm->handleRequest($request);
            if ($linkForm->isSubmitted() && $linkForm->isValid()) {
                $app['dao.link']->save($link);
                $app['session']->getFlashBag()->add('success', 'Your comment was succesfully added.');
            }
            $linkFormView = $linkForm->createView();
        }
        return $app['twig']->render('link.html.twig', array(
            'linkForm' => $linkFormView));
    }


    /**
     * User login controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function loginAction(Request $request, Application $app) {
        return $app['twig']->render('login.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
            ));
    }
}
