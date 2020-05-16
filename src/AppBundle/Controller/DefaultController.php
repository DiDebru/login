<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\User;

class DefaultController extends Controller {

  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request) {
    /** @var \Symfony\Component\Security\Core\User\User $user */
    $user = $request->getSession()->get('user');
    $username = NULL;
    if ($user instanceof User) {
      $username = ['name' => $user->getUsername()];
    }
    // replace this example code with whatever you need
    return $this->render('default/index.html.twig', [
      'user' => $username,
      'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
    ]);
  }
}
