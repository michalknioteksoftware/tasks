<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\DomainFactory;
use App\Application\Task\CreateTaskCommandHandler;
use App\Application\Task\ListCommandHandler;
use App\Application\Task\ListHistoryCommandHandler;
use App\Application\Task\UpdateTaskStatusCommandHandler;
use App\Infrastructure\Doctrine\TaskRepository;
use App\Domain\Task\TaskStatus;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskController extends AbstractWebController
{
    #[Route('/tasks', name: 'web_tasks', methods: ['GET'])]
    public function index(Request $request, ListCommandHandler $handler, DomainFactory $factory): Response
    {
        $user = $this->getDomainUser($factory);

        if (null === $user) {
            return new RedirectResponse($this->generateUrl('web_home'));
        }

        $tasks = $handler->handle($user);

        $statusCases = TaskStatus::cases();

        return $this->render('web/tasks.html.twig', [
            'user' => $user,
            'tasks' => $tasks,
            'statuses' => $statusCases,
        ]);
    }

    #[Route('/tasks/create', name: 'web_tasks_create', methods: ['GET', 'POST'])]
    public function create(Request $request, CreateTaskCommandHandler $createHandler, DomainFactory $factory): Response
    {
        $user = $this->getDomainUser($factory);

        if (null === $user) {
            return new RedirectResponse($this->generateUrl('web_home'));
        }

        $statusChoices = [];
        foreach (TaskStatus::cases() as $status) {
            $statusChoices[$status->value] = $status->value;
        }

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Name',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => $statusChoices,
                'placeholder' => 'Choose status',
                'constraints' => [new NotBlank()],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Create task'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $status = TaskStatus::from((string) $data['status']);

            $createHandler->handle(
                (string) $data['name'],
                (string) $data['description'],
                $status,
                $user,
            );

            return new RedirectResponse($this->generateUrl('web_tasks'));
        }

        return $this->render('web/task_create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/tasks/{id}/status', name: 'web_tasks_update_status', methods: ['POST'])]
    public function updateStatus(
        int $id,
        Request $request,
        UpdateTaskStatusCommandHandler $handler,
        DomainFactory $factory,
    ): Response {
        $user = $this->getDomainUser($factory);

        if (null === $user) {
            return new RedirectResponse($this->generateUrl('web_home'));
        }

        $statusValue = (string) $request->request->get('status', '');

        try {
            $newStatus = TaskStatus::from($statusValue);
        } catch (\ValueError) {
            $this->addFlash('error', 'Invalid status value.');

            return new RedirectResponse($this->generateUrl('web_tasks'));
        }

        try {
            $handler->handle($id, $newStatus, $user);
            $this->addFlash('success', 'Task status updated.');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return new RedirectResponse($this->generateUrl('web_tasks'));
    }

    #[Route('/tasks/{id}/history', name: 'web_tasks_history', methods: ['GET'])]
    public function history(
        int $id,
        ListHistoryCommandHandler $handler,
        TaskRepository $taskRepository,
        DomainFactory $factory,
    ): Response {
        $user = $this->getDomainUser($factory);

        if (null === $user) {
            return new RedirectResponse($this->generateUrl('web_home'));
        }

        $doctrineTask = $taskRepository->find($id);
        if ($doctrineTask === null) {
            $this->addFlash('error', 'Task not found.');

            return new RedirectResponse($this->generateUrl('web_tasks'));
        }

        $assignedUser = $doctrineTask->getAssignedUser();
        if ($assignedUser === null || $assignedUser->getId() !== $user->getId()) {
            $this->addFlash('error', 'You are not allowed to view this task.');

            return new RedirectResponse($this->generateUrl('web_tasks'));
        }

        $task = $factory->fromDoctrineTask($doctrineTask);
        $history = $handler->handle($task, $user);

        return $this->render('web/task_history.html.twig', [
            'task' => $task,
            'history' => $history,
            'user' => $user,
        ]);
    }
}

