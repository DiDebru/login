<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController {

  /**
   * @Route("/login", name="app_login")
   */
  public function login(AuthenticationUtils $authenticationUtils, Request $request) {
    // Redirect to homepage if user is logged in.
    if ($request->getSession()->get('user')) {
      return new RedirectResponse($this->generateUrl('homepage'));
    }
    // get the login error if there is one.
    $error = $authenticationUtils->getLastAuthenticationError();
    // last username entered by the user.
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('login/login.html.twig', [
      'last_username' => $lastUsername,
      'error' => $error,
    ]);
  }
}
