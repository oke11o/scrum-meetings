<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class LoginFormAuthenticatorTest
 * @package App\Tests\Unit\Security
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class LoginFormAuthenticatorTest extends TestCase
{
    /**
     * @var LoginFormAuthenticator
     */
    private $authenticator;
    /**
     * @var RouterInterface|ObjectProphecy
     */
    private $router;
    /**
     * @var FormFactoryInterface|ObjectProphecy
     */
    private $formFactory;
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    private $em;
    /**
     * @var UserPasswordEncoderInterface|ObjectProphecy
     */
    private $encoder;

    public function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->encoder = $this->prophesize(UserPasswordEncoderInterface::class);

        $this->authenticator = new LoginFormAuthenticator(
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->em->reveal(),
            $this->encoder->reveal()
        );
    }

    /**
     * @test
     * @dataProvider supportsExamples
     */
    public function supports($route, $method, $result)
    {
        $request = new Request();
        $request->setMethod($method);

        $attrs = new ParameterBag();
        $attrs->set('_route', $route);
        $request->attributes = $attrs;

        $this->assertEquals($result, $this->authenticator->supports($request));
    }

    public function supportsExamples()
    {
        return [
            'not allow route' => [
                'route' => 'login__',
                'method' => 'POST',
                'result' => false,
            ],
            'not allow method' => [
                'route' => 'login',
                'method' => 'GET',
                'result' => false,
            ],
            'supports' => [
                'route' => 'login',
                'method' => 'POST',
                'result' => true,
            ],
        ];
    }

    /**
     * @test
     */
    public function getLoginUrl()
    {
        $url = 'real_login_path';
        $exceptionMessage = 'Authentication exception message';
        $this->router->generate(LoginFormAuthenticator::LOGIN_ROUTE)->shouldBeCalled()->willReturn($url);

        $request = new Request();
        $session = $this->prophesize(SessionInterface::class);
        $request->setSession($session->reveal());

        $exception = new AuthenticationException($exceptionMessage);
        $session->set(Security::AUTHENTICATION_ERROR, $exception)->shouldBeCalled();

        $result = $this->authenticator->onAuthenticationFailure($request, $exception);

        $this->assertEquals($url, $result->getTargetUrl());
    }

    /**
     * @test
     */
    public function getCredentials()
    {
        $request = new Request();
        $session = $this->prophesize(SessionInterface::class);
        $request->setSession($session->reveal());
        $username = 'username';
        $session->set(Security::LAST_USERNAME, $username);

        $formType = $this->prophesize(FormInterface::class);
        $this->formFactory->create(LoginFormType::class)->shouldBeCalled()->willReturn($formType->reveal());

        $formType->handleRequest($request)->shouldBeCalled();
        $result = [
            '_username' => $username,
            '_password' => 'padd',

        ];
        $formType->getData()->shouldBeCalled()->willReturn($result);

        $this->assertEquals($result, $this->authenticator->getCredentials($request));
    }

    /**
     * @test
     */
    public function getUser()
    {
        $userRepo = $this->prophesize(UserRepository::class);
        $this->em->getRepository(User::class)->shouldBeCalled()->willReturn($userRepo);

        $username = 'email';
        $credentials = [
            '_username' => $username,
            '_password' => 'pass',
        ];
        $user = new User();

        $userRepo->findOneBy(['email' => $username])->shouldBeCalled()->willReturn($user);

        $userProvider = $this->prophesize(UserProviderInterface::class);
        $this->assertEquals($user, $this->authenticator->getUser($credentials, $userProvider->reveal()));
    }

    /**
     * @test
     */
    public function checkCredentials()
    {
        $credentials = [
            '_username' => '_username',
            '_password' => 'pass',
        ];
        $user = new User();
        $result = true;
        $this->encoder->isPasswordValid($user, 'pass')->shouldBeCalled()->willReturn($result);

        $this->assertEquals($result, $this->authenticator->checkCredentials($credentials, $user));
    }

    /**
     * @test
     */
    public function onAuthenticationSuccess()
    {
        $request = new Request();
        $token = $this->prophesize(TokenInterface::class);
        $providerKey = 'key';

        $url = 'url';
        $this->router->generate('homepage')->shouldBeCalled()->willReturn($url);

        /** @var RedirectResponse $result */
        $result = $this->authenticator->onAuthenticationSuccess($request, $token->reveal(), $providerKey);

        $this->assertEquals($url, $result->getTargetUrl());
    }
}
