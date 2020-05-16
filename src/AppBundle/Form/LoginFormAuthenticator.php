<?php


namespace AppBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements AuthenticatorInterface {

  use TargetPathTrait;

  /** @var string The login route */
  private const LOGIN_ROUTE = 'app_login';

  /** @var \Doctrine\ORM\EntityManagerInterface  */
  private $entityManager;

  /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface  */
  private $urlGenerator;

  /** @var \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface  */
  private $csrfTokenManager;

  /** @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface  */
  private $passwordEncoder;

  /**
   * LoginFormAuthenticator constructor.
   *
   * @param \Doctrine\ORM\EntityManagerInterface $entityManager
   * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
   * @param \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $csrfTokenManager
   * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
   */
  public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder) {
    $this->entityManager = $entityManager;
    $this->urlGenerator = $urlGenerator;
    $this->csrfTokenManager = $csrfTokenManager;
    $this->passwordEncoder = $passwordEncoder;
  }

  /**
   * Check if route should be supported.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return bool
   */
  public function supports(Request $request) {
    return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('POST');
  }

  /**
   * Get credentials from from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function getCredentials(Request $request) {
    $credentials = [
      'username' => $request->request->get('username'),
      'password' => $request->request->get('password'),
      'csrf_token' => $request->request->get('_csrf_token'),
    ];
    $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);

    return $credentials;
  }

  /**
   * Register and get user.
   *
   * @param $credentials
   * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
   *
   * @return mixed
   */
  public function getUser($credentials, UserProviderInterface $userProvider) {
    $token = new CsrfToken('authenticate', $credentials['csrf_token']);
    if (!$this->csrfTokenManager->isTokenValid($token)) {
      throw new InvalidCsrfTokenException();
    }

    // Define users.
    $user_properties = [
      'admin' => [
        'password' => 'admin',
      ],
      'freitag-test' => [
        'password' => '123Geheim',
      ]
    ];

    // Create Users.
    $inMemoryProvider = new InMemoryUserProvider($user_properties);

    // Get user by name.
    $user = $inMemoryProvider->loadUserByUsername($credentials['username']);

    if (!$user) {
      throw new AuthenticationException('User with %username was not found', ['%username' => $credentials['username']]);
    }

    return $user;
  }

  /**
   * @param $credentials
   * @param \Symfony\Component\Security\Core\User\UserInterface $user
   *
   * @return mixed
   */
  public function checkCredentials($credentials, UserInterface $user) {
    if ($user->getPassword() === $credentials['password']) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Redirect user to homepage after succesful log in.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
   * @param $providerKey
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   * @throws \Exception
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
    // Provide user object in session.
    $request->getSession()->set('user', $token->getUser());
    if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
      return new RedirectResponse($targetPath);
    }

    // redirect to some "homepage" route - or wherever you want
    return new RedirectResponse($this->urlGenerator->generate('homepage'));
  }

  /**
   * @return mixed
   */
  protected function getLoginUrl() {
    return $this->urlGenerator->generate(self::LOGIN_ROUTE);
  }

}
