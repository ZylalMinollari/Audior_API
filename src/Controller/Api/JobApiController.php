<?php

namespace App\Controller\Api;

use App\Entity\Job;
use App\Form\JobType;
use App\Entity\Schedule;
use App\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/job')]
class JobApiController extends AbstractController
{

    #[Route('/new', name: 'app_job_api_new', methods: ['GET', 'POST'])]

    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['title'], $data['description'], $data['createdAt'], $data['name'], $data['should_be_finished'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $job = new Job();
        $job->setTitle($data['title']);
        $job->setName($data['name']);
        $job->setDescription($data['description']);
        $job->setShouldBeFinished(new \DateTime($data['should_be_finished']));
        $job->setCreatedAt(new \DateTime($data['createdAt']));

        if (isset($data['assessment'])) {
            $job->setAssessment($data['assessment']);
        }

        $entityManager->persist($job);
        $entityManager->flush();

        return $this->json($job, Response::HTTP_CREATED, ['Job created successfully'], ['groups' => 'job']);
    }

    #[Route('/index', name: 'app_job_api_index', methods: ['GET'])]

    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $jobs = $entityManager->getRepository(Job::class)->createQueryBuilder('j')
            ->orderBy('j.id', 'DESC')
            ->setFirstResult(($page - 1) * 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $jobData = [];
        foreach ($jobs as $job) {
            $jobData[] = [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'name' => $job->getName(),
                'description' => $job->getDescription(),
                'created_at' => $job->getCreatedAt(),
                'should_be_finished' => $job->getShouldBeFinished(),
            ];
        }

        return new JsonResponse($jobData);
    }

    #[Route('/show/{id}', name: 'app_job_api_show', methods: ['GET'])]

    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $job = $entityManager->getRepository(Job::class)->find($id);

        if (!$job) {
            throw $this->createNotFoundException('No job found');
        }

        $data = [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'name' => $job->getName(),
            'description' => $job->getDescription(),
            'createdAt' => $job->getCreatedAt()->format('Y-m-d H:i:s'),
            'assessment' => $job->getAssessment(),
            'should_be_finished' => $job->getShouldBeFinished(),
        ];

        return $this->json($data);
    }

    #[Route('/{id}/edit', name: 'app_job_api_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $job = $entityManager->getRepository(Job::class)->find($id);

        if (!$job) {
            return new JsonResponse(['message' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $orginalValues = [
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'name' => $job->getName(),
            'createdAt' => $job->getCreatedAt()->format('Y-m-d H:i:s'),
            'should_be_finished' => $job->getAssessment(),
            'assessment' => $job->getAssessment()
        ];

        $data = json_decode($request->getContent(), true);
        $job->setTitle($data['title'] ?? $orginalValues['title']);
        $job->setName($data['name'] ?? $orginalValues['name']);
        $job->setDescription($data['description'] ?? $orginalValues['description']);
        $createdAt = $data['createdAt'] ?? $orginalValues['createdAt'];

        if ($createdAt instanceof \DateTime) {
            $createdAt = $createdAt->format('Y-m-d\TH:i:sP');
        }

        $createdAtDateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $createdAt);

        $job->setCreatedAt($createdAtDateTime);


        $shouldFinished = $data['should_be_finished'] ?? $orginalValues['should_be_finished'];
        if ($shouldFinished instanceof \DateTime) {
            $shouldFinished = $shouldFinished->format('Y-m-d\TH:i:sP');
        }
        var_dump($shouldFinished);
        die;
        $shouldFinishedDateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $shouldFinished);
        $job->setShouldBeFinished($shouldFinishedDateTime);

        $job->setAssessment($data['assessment'] ?? $orginalValues['assessment']);

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

        return $this->json(['message' => 'Job updated successfully'], Response::HTTP_OK);
    }

    #[Route('/delete/{id}', name: 'app_job_api_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {

        $job = $entityManager->getRepository(Job::class)->find($id);

        if (!$job) {
            return new JsonResponse(['message' => 'No Job Found'], Response::HTTP_BAD_REQUEST);
        }

        $schedules = $entityManager->getRepository(Schedule::class)->findBy(['job' => $job]);

        if (!empty($schedules)) {
            return new JsonResponse(['message' => 'Cannot delete job because it is scheduled'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($job);
        $entityManager->flush();
        return $this->json(['message' => 'Job deleted successfully'], Response::HTTP_OK);
    }
}
