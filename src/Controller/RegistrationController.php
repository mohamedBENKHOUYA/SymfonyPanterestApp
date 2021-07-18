<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;
    private $guardHandler;
    private $authenticator;

    public function __construct(
        EmailVerifier $emailVerifier,
        GuardAuthenticatorHandler $guardHandler
)
    {
        $this->emailVerifier = $emailVerifier;
        $this->guardHandler = $guardHandler;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface  $passwordHasher,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage

    ): Response
    {

        if ($this->getUser()) {
            $this->addFlash('info', 'you are log in. logout first and register');
            return $this->redirectToRoute('app_home');
        }
//        $user = new User();
        $form = $this->createForm(RegistrationFormType::class);
        // if $user(formType or here), so in handleRequest we create a user from form
        // submitted (in case of: getData())and update "$user = new User()"
        // but, all fields need to be same with User class unless ("mapped" => false)

        // because of "@UniqueEntity", if post, there will be : select all... where
        // email = ... to verify the email unicity
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            // encode the plain password
            $user->setPassword(
                // we pass $user to know what algo to use to hash(security.yaml)
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

//            $entityManager = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
//                    ->from(new Address($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']))
                    ->from(new Address(
                        $this->getParameter('app.mail_from_address'),
                        $this->getParameter('app.mail_from_name')))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('emails/registration/confirmation.html.twig')
                    ->context([
                        'expiration_date' => new \DateTime('+7 days')
                    ])
            );

            // do anything else you need here, like send an email
            // authenticate user manually after register
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $request->getSession()->set(Security::MANUALLY_AUTHENTICATE, serialize($token));

            $request->getSession()->getFlashBag()->add('success', 'you need to confirm your email');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
//            $this->addFlash('verify_email_error', $exception->getReason());
            $this->addFlash('info', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}

