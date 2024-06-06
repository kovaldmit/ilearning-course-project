<?php

namespace App\Controller;

use App\Form\JiraTicketType;
use App\Service\JiraService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket')]
class JiraController extends AbstractController
{
    private JiraService $jiraService;

    public function __construct(JiraService $jiraService)
    {
        $this->jiraService = $jiraService;
    }

    #[Route('/', name: 'ticket_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        try {
            $tickets = $this->jiraService->getTicketsByUser($user);
        } catch (Exception $e) {
            $this->addFlash('error', 'Could not retrieve tickets: ' . $e->getMessage());
            $tickets = [];
        }

        return $this->render('jira/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/new', name: 'ticket_new', methods: ['POST', 'GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request): Response
    {
        $priorities = $this->jiraService->getPriorities();
        $currentUrl = $request->headers->get('referer');

        $form = $this->createForm(JiraTicketType::class, null, [
            'priorities' => $priorities,
            'current_url' => $currentUrl
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $description = $form->get('description')->getData();
            $priority = $form->get('priority')->getData();
            $url = $form->get('url')->getData();

            $jiraUser = $this->jiraService->getUser($user);
            if (empty($jiraUser)) {
                $jiraUser = $this->jiraService->createUser($user);
            }

            $this->jiraService->createIssue(
                'Support Request from ' . $user->getFullname(),
                $description,
                $priority,
                $jiraUser,
                $url
            );

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('jira/new.html.twig', [
            'form' => $form,
        ]);
    }
}
