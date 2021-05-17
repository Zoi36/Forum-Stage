<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Form\CommentType;
use App\Form\ConversationType;
use App\Form\MessageType;
use App\Repository\ActivityRepository;
use App\Repository\CommentRepository;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private $activityRepository;
    private $conversationRepository;

    function __construct(ActivityRepository $activityRepository, ConversationRepository $conversationRepository)
    {
        $this->activityRepository = $activityRepository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/", name="listActivities")
     */
    public function listActivities(ActivityRepository $activityRepository,CommentRepository $commentRepository,Request $request)
    {
        $listActivities = $activityRepository->findAll();
        $listComments = $commentRepository->findAll();

        $comment = new Comment();

        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment
                ->setUser($this->getUser())
                ->setDateComment(new \DateTime("now"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            return $this->redirect($this->generateUrl('listActivities'));
        }
        return $this->render('user/listActivities.html.twig', [
            'listActivities' => $listActivities,
            'listComments'=>$listComments,
            'form'=>$form
        ]);
    }

    /**
     * @Route("/createConversation/{id}", name="createConversation")
     * @IsGranted("ROLE_USER")
     */

    public function createConversation(Request $request, Activity $activity)
    {
        $conversation = new Conversation();
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $conversation
                ->setUser($this->getUser())
                ->setActivity($activity)
                ->setDateConversation(new \DateTime("now"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($conversation);
            $em->flush();
            return $this->redirect($this->generateUrl('listConversations',
                array('id' => $activity->getId())));
        }

        return $this->render('user/createConversation.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/listConversations/{id}", name="listConversations")
     * @return Response
     */
    public function listConversations($id, ConversationRepository $conversationRepository, PaginatorInterface $paginator,
                                      Request $request)
    {
        $activity = $this->activityRepository->findOneById($id);

        $listConversations = $conversationRepository->findByActivity($id);

        $pagination = $paginator->paginate(
            $conversationRepository->findByActivity($id),
            $request->query->getInt('page', 1), 3
        );

        return $this->render('user/listConversations.html.twig', [

            'activity' => $activity,
            'listConversations' => $listConversations,
            'pagination' => $pagination,
        ]);

    }

    /**
     * @Route("/createMessage/{id}", name="createMessage")
     * @IsGranted("ROLE_USER")
     */

    public function createMessage(Request $request, Conversation $conversation)
    {
        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message
                ->setUser($this->getUser())
                ->setConversation($conversation)
                ->setDateMessage(new \DateTime("now"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            return $this->redirect($this->generateUrl('listMessages', array('id' => $conversation->getId())));
        }

        return $this->render('user/createMessage.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/listMessages/{id}", name="listMessages")
     *
     */
    public function listMessages($id, MessageRepository $messageRepository, PaginatorInterface $paginator, Request $request)
    {
        $conversation = $this->conversationRepository->findOneById($id);

        $listMessages = $messageRepository->findByConversation($id);

        $pagination = $paginator->paginate(
            $messageRepository->findByConversation($id),
            $request->query->getInt('page', 1), 3
        );
        return $this->render('user/listMessages.html.twig', array(

            'conversation' => $conversation,
            'listMessages' => $listMessages,
            'pagination' => $pagination,
        ));


    }


}
