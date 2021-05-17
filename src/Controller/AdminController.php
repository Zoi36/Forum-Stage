<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{

    private $activityRepository;
    private $conversationRepository;
    private $messageRepository;

    function __construct(ActivityRepository $activityRepository, ConversationRepository $conversationRepository,
                         MessageRepository $messageRepository)
    {
        $this->activityRepository = $activityRepository;
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @Route("/admin/login", name="admin_login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();


        return $this->render('admin/login.html.twig', [

            'error' => $error,

        ]);
    }

    /**
     * @Route("/admin/logout", name="admin_logout")
     * @return void
     */
    public function logout()
    {

    }

    /**
     * @Route("/admin/createActivity", name="createActivity")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createActivity(Request $request)
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();
            return $this->redirectToRoute('adminListActivities');
        }
        return $this->render('admin/createActivity.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/admin/deleteActivity/{id}", name="deleteActivity")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteActivity(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $activity = $this->activityRepository->findOneById($id);


        if (!$activity) {
            throw $this->createNotFoundException('Activité n\'exist pas.');
        }

        $entityManager->remove($activity);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('adminListActivities'));
    }

    /**
     * @Route("/admin/deleteConversation/{id}", name="deleteConversation")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteConversation(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $conversation = $this->conversationRepository->findOneById($id);

        if (!$conversation) {
            throw $this->createNotFoundException('Conversation n\'exist pas.');
        }

        $entityManager->remove($conversation);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('adminListConversations', array('id' => $conversation->getActivity()->getId())));
    }

    /**
     * @Route("/admin/deleteMessage/{id}", name="deleteMessage")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteMessage(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $message = $this->messageRepository->findOneById($id);
        $conversationId = $message->getConversation()->getId();

        if (!$message) {
            throw $this->createNotFoundException('Message n\'existe pas.');
        }

        $entityManager->remove($message);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('adminListMessages', array('id' => $conversationId)));
    }

    /**
     * @Route("/admin/adminListActivities", name="adminListActivities")
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminListActivities(ActivityRepository $activityRepository)
    {
        $adminListActivities = $activityRepository->findAll();

        return $this->render('admin/adminListActivities.html.twig', [

            'adminListActivities' => $adminListActivities
        ]);

    }

    /**
     * @Route("/adminListConversations/{id}", name="adminListConversations")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminListConversations($id, ConversationRepository $conversationRepository,
                                           PaginatorInterface $paginator, Request $request)
    {
        $activity = $this->activityRepository->findOneById($id);

        $adminListConversations = $conversationRepository->findByActivity($id);

        $pagination = $paginator->paginate(
            $conversationRepository->findByActivity($id),
            $request->query->getInt('page', 1), 3
        );

        return $this->render('admin/adminListConversations.html.twig', [

            'activity' => $activity,
            'adminListConversations' => $adminListConversations,
            'pagination'=> $pagination,
        ]);

    }

    /**
     * @Route("/adminListMessages/{id}", name="adminListMessages")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminListMessages($id, MessageRepository $messageRepository,PaginatorInterface $paginator,Request $request)
    {
        $conversation = $this->conversationRepository->findOneById($id);

        $adminListMessages = $messageRepository->findByConversation($id);

        $pagination = $paginator->paginate(
            $messageRepository->findByConversation($id),
            $request->query->getInt('page', 1), 3
        );

        return $this->render('admin/adminListMessages.html.twig', [

            'conversation' => $conversation,
            'adminListMessages' => $adminListMessages,
            'pagination'=> $pagination,
        ]);
    }
}
