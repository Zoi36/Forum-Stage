<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class AccountController extends AbstractController
{
    /**
     * @Route("/login", name="account_login")
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('account/login.html.twig', [

            'error' => $error,
            'lastUsername' => $lastUsername

        ]);
    }
    
    /**
     * @Route("/register", name = "account_register")
     *
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, TokenGeneratorInterface $tokenGenerator,
                             \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);


            $token = $tokenGenerator->generateToken();
            $user->setToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();


            $message = (new\Swift_Message('Activation de votre compte'))
                ->setFrom('sotiriadou.zoi@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/activation.html.twig', ['token' => $user->getToken()]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash(
                'success',
                "Un email d'activation vous a été envoyer!  "
            );


            return $this->render('account/register.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('account/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/activation/{token}", name="activation")
     */
    public function activation($token, UserRepository $userRepository)
    {

        $user = $userRepository->findOneBy(['token' => $token]);

        if (!$user) {

            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }
        $user->setToken(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "Bienvenue {$user->getUsername()}, vous avez bien activé votre compte!Veuillez se connecté pour continue.."
        );

        return $this->redirectToRoute('listActivities');

    }

    /**
     * @Route("/logout", name="account_logout")
     * @return void
     */
    public function logout()
    {

    }

    /**
     * @Route("/forgotPass", name="forgotPass")
     */
    public function forgotPass(Request $request, UserRepository $userRepository, \Swift_Mailer $mailer,
                               TokenGeneratorInterface $tokenGenerator)
    {
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $user = $userRepository->findOneByEmail($data['email']);

            if (!$user) {

                $this->addFlash('danger', 'Email n\'existe pas');
                $this->redirectToRoute('account_login');
            }

            $token = $tokenGenerator->generateToken();
            try {
                $user->setTokenReset($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('worning', 'Une erreur est survenue' . $e->getMessage());
                return $this->redirectToRoute('account_login');
            }

            $url = $this->generateUrl('resetPass', ['token' => $token],UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new\Swift_Message('Mot de passe oublié'))
                ->setFrom('sotiriadou.zoi@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "<p>Vous avez demander de reinitialisation de votre mot de passe. : " . $url . '</p>',
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash(
                'success',
                "Un email de modification de mot de passe vous a été envoyé."
            );
            return $this->redirectToRoute('account_login');
        }
        return $this->render('account/forgotPass.html.twig', ['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/resetPass/{token}", name="resetPass")
     *
     */
    public function resetPass($token, Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {

        $user = $userRepository->findOneBy(['tokenReset' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Token inconnu');
            return $this->redirectToRoute('account_login');

        }
        if ($request->isMethod('POST')) {

            $user->setTokenReset(null);

            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            try {
                $user->setTokenReset($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('worning', 'Une erreur est survenue' . $e->getMessage());
                return $this->redirectToRoute('account_login');
            }

            $this->addFlash('success', 'Mot de passe modifier avec success!');
            return $this->redirectToRoute('account_login');

        } else {


            return $this->render('account/resetPass.html.twig', ['token' => $token]);
        }
    }

}
