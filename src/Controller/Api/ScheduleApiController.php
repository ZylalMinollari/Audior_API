<?php

namespace App\Controller\Api;

use App\Entity\Job;
use App\Entity\Auditor;
use App\Entity\Schedule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/schedule')]
class ScheduleApiController extends AbstractController
{

    #[Route('/new', name: 'app_schedule_api_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $auditor = $entityManager->getRepository(Auditor::class)->findOneBy(['username' => $data['auditor_id']]);

        if (!$auditor) {
            return new JsonResponse(['error' => 'Auditor not found'], Response::HTTP_NOT_FOUND);
        }

        $job = $entityManager->getRepository(Job::class)->findOneBy(['name' => $data['job_id']], ['id' => 'ASC'], 1);
        if (!$job) {
            return new JsonResponse(['error' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $existingSchedule = $entityManager->getRepository(Schedule::class)->findOneBy([
            'auditor' => $auditor,
            'job' => $job
        ]);

        if ($existingSchedule) {

            return new JsonResponse(['error' => 'Auditor already has this job assigned'], Response::HTTP_CONFLICT);
        }

        $expectedCompletionDate = $job->getShouldBeFinished();

        $expectedCompletionDate->setTimezone(new \DateTimeZone($auditor->getTimezone()->format('P')));

        $currentDate = new \DateTime('now', new \DateTimeZone($auditor->getTimezone()->format('P')));


        if ($expectedCompletionDate < $currentDate) {
            return new JsonResponse(['error' => 'Job completion time has already passed for the auditor'], Response::HTTP_BAD_REQUEST);
        }

        $assignedDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['assigned_date']);

        if ($assignedDate === false) {
            return new JsonResponse(['error' => 'Invalid assigned date format'], Response::HTTP_BAD_REQUEST);
        }

        $completionDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['completion_date']);

        if ($completionDate === false) {
            return new JsonResponse(['error' => 'Invalid completion date format'], Response::HTTP_BAD_REQUEST);
        }

        $schedule = new Schedule();
        $schedule->setAuditor($auditor);
        $schedule->setJob($job);
        $schedule->setAssignedDate($assignedDate);
        $schedule->setCompletionDate($completionDate);

        $entityManager->persist($schedule);

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($schedule);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Schedule created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/index', name: 'app_schedule_api_index', methods: ['GET'])]

    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $schedules = $entityManager->getRepository(Schedule::class)->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC')
            ->setFirstResult(($page - 1) * 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $scheduleData = [];
        foreach ($schedules as $schedule) {
            $scheduleData[] = [
                'id' => $schedule->getId(),
                'auditor' => $schedule->getAuditor()->getUsername(),
                'assignedDate' => $schedule->getAssignedDate(),
                'completionDate' => $schedule->getCompletionDate(),
            ];
        }

        return new JsonResponse($scheduleData);
    }

    #[Route('show/{id}', name: 'app_schedule_api_show', methods: ['GET'])]
    public function show(string $id, EntityManagerInterface $entityManager): JsonResponse
    {

        $id = intval($id);
        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid schedule ID');
        }


        $schedule = $entityManager->getRepository(Schedule::class)->find($id);

        if (!$schedule) {
            throw $this->createNotFoundException('No schedule found');
        }
        $auditorUsername = $schedule->getAuditor()->getUsername();
        $jobTitle = $schedule->getJob()->getTitle();

        $data = [
            'id' => $schedule->getId(),
            'auditor' => $auditorUsername,
            'job' => $jobTitle,
            'assignedDate' => $schedule->getAssignedDate()->format('Y-m-d H:i:s'),
            'completionDate' => $schedule->getCompletionDate()->format('Y-m-d H:i:s'),
        ];
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_schedule_api_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id, EntityManagerInterface $entityManager): Response
    {
        $id = intval($id);
        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid schedule ID');
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($id);
        if (!$schedule) {
            throw $this->createNotFoundException('No schedule found');
        }

        $originalValues = [
            'auditor' => $schedule->getAuditor(),
            'job' => $schedule->getJob(),
            'assignedDate' => $schedule->getAssignedDate(),
            'completionDate' => $schedule->getCompletionDate(),
        ];

        $data = json_decode($request->getContent(), true);

        $auditor = $entityManager->getRepository(Auditor::class)->findOneBy(['username' => $data['auditor_id'] ?? $originalValues['auditor']->getUsername()]);
        if (!$auditor) {
            return new JsonResponse(['error' => 'Auditor not found'], Response::HTTP_NOT_FOUND);
        }

        $job = $entityManager->getRepository(Job::class)->findOneBy(['title' => $data['job_id'] ?? $originalValues['job']->getTitle()]);
        if (!$job) {
            return new JsonResponse(['error' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $schedule->setAuditor($auditor);
        $schedule->setJob($job);
        $assignedDate = $data['assigned_date'] ?? $originalValues['assignedDate'];

        if ($assignedDate instanceof \DateTime) {
            $assignedDate = $assignedDate->format('Y-m-d\TH:i:sP');
        }

        $assignedDateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $assignedDate);
        $schedule->setAssignedDate($assignedDateTime);

        $completionDate = $data['completion_date'] ?? $originalValues['completionDate'];

        if ($completionDate instanceof \DateTime) {
            $completionDate = $completionDate->format('Y-m-d\TH:i:sP');
        }

        $completionDateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $completionDate);
        $schedule->setCompletionDate($completionDateTime);

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($job);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Schedule updated successfully'], Response::HTTP_OK);
    }

    #[Route('delete/{id}', name: 'app_schedule_api_delete', methods: ['DELETE'])]
    public function delete(string $id, EntityManagerInterface $entityManager): Response
    {   
        $id = intval($id);
        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid schedule ID');
        }

        $schedule = $entityManager->getRepository(Schedule::class)->find($id);

        if (!$schedule) {
            return new JsonResponse(['message' => 'No Schedule Found'], Response::HTTP_BAD_REQUEST);
        }

        if ($schedule->getAssignedDate() && !$schedule->getCompletionDate()) {
            return new JsonResponse(['message' => 'Cannot delete Schedule: Job is still in progress'], Response::HTTP_BAD_REQUEST);
        }
        $entityManager->remove($schedule);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Schedule deleted successfully'], Response::HTTP_OK);
    }
}
