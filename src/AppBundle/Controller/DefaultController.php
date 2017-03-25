<?php

namespace AppBundle\Controller;



use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Ekreative\RedmineLoginBundle\Form\Type\LoginType;
use Symfony\Component\Security\Core\Security;



class DefaultController extends Controller
{


    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array
     */
    public function loginAction(Request $request) {
        $session = $request->getSession();
        $form = $this->createForm(new LoginType(), [
            'username' => $session->get(Security::LAST_USERNAME)
        ], [
            'action' => $this->generateUrl('login_check')
        ]);
        $form->add('submit', 'submit', ['label' => 'Sign In']);
        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }
        return $this->render('@App/User/login.html.twig', [
            'last_username' => $session->get(Security::LAST_USERNAME),
            'error' => $error,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction() {

    }



    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        $sites = $this->get('sitesmanager.hosts_manager')->getAvailableSitesDetailed();
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            'sites' => $sites
        ]);
    }
}
