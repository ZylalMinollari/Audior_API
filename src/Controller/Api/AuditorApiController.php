<?php

namespace App\Controller\Api;

use DateTimeImmutable;
use App\Entity\Auditor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/auditor')]
class AuditorApiController extends AbstractController
{
    #[Route('/register', name: 'app_auditor_api_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $existingAuditor = $entityManager->getRepository(Auditor::class)->findOneBy(['username' => $data['username']]);

        if ($existingAuditor !== null) {
            return new JsonResponse(['message' => 'Username already exists'], Response::HTTP_BAD_REQUEST);
        }

        $auditor = new Auditor();
        $auditor->setName($data['name']);
        $auditor->setUsername($data['username']);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $auditor->setPassword($hashedPassword);
        $timezone = new DateTimeImmutable($data['timezone']);
        $auditor->setTimezone($timezone);

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($auditor);

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
        $entityManager->persist($auditor);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Auditor registerd', 'username' => $auditor->getUsername()], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'app_auditor_api_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $password = $data['password'];

        if (!$username || !$password) {
            return new JsonResponse(['message' => 'Username and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $auditor = $entityManager->getRepository(Auditor::class)->findOneBy(['username' => $username]);

        if (!$auditor) {
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        if (!password_verify($password, $auditor->getPassword())) {
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $session->set('auditor_id', $auditor->getId());

        return new JsonResponse(['message' => 'Login successful'], Response::HTTP_OK);
    }

    #[Route('/logout', name: 'app_auditor_api_logout', methods: ['POST'])]
    public function logout(SessionInterface $session): JsonResponse
    {

        $session->invalidate();
        return new JsonResponse(['message' => 'Logout successful'], Response::HTTP_OK);
    }

    #[Route('/index', name: 'app_auditor_api_index', methods: ['GET'])]

    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $auditors = $entityManager->getRepository(Auditor::class)->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $auditorData = [];
        foreach ($auditors as $auditor) {
            $auditorData[] = [
                'id' => $auditor->getId(),
                'name' => $auditor->getName(),
                'username' => $auditor->getUsername(),
                'timezone' => $auditor->getTimezone(),
            ];
        }

        return new JsonResponse($auditorData);
    }
    #[Route('/show/{id}', name: 'app_auditor_api_show', methods: ['GET'])]
    public function show(string $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = intval($id);
        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid auditor ID');
        }

        $auditor = $entityManager->getRepository(Auditor::class)->find($id);

        if (!$auditor) {
            throw $this->createNotFoundException('No auditor found for id ' . $id);
        }

        $data = [
            'id' => $auditor->getId(),
            'name' => $auditor->getName(),
            'username' => $auditor->getUsername(),
            'timezone' => $auditor->getTimezone(),
        ];
        return new JsonResponse($data);
    }


    #[Route('/{id}/edit', name: 'app_auditor_api_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id, EntityManagerInterface $entityManager): Response
    {
        $id = intval($id);
        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid auditor ID');
        }

        $auditor = $entityManager->getRepository(Auditor::class)->find($id);

        if (!$auditor) {
            return new JsonResponse(['message' => 'Auditor not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $originalValues = [
            'name' => $auditor->getName(),
            'username' => $auditor->getUsername(),
            'password' => $auditor->getPassword(),
            'timezone' => $auditor->getTimezone(),
        ];

        $auditor->setName($data['name'] ?? $originalValues['name']);
        $auditor->setUsername($data['username'] ?? $originalValues['username']);

        $timeZone = $data['timezone'] ?? $originalValues['timezone'];

        if ($timeZone instanceof \DateTime) {
            $timeZone = $timeZone->format('Y-m-d\TH:i:sP');
        }

        $timeZoneDate = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $timeZone);
        $auditor->setTimezone($timeZoneDate);

        $newPassword = $data['password'] ?? null;

        if ($newPassword !== null) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $auditor->setPassword($hashedPassword);
        }


        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($auditor);

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

        return new JsonResponse(['message' => 'Auditor updated', 'id' => $auditor->getId()]);
    }

    #[Route('/delete/{id}', name: 'app_auditor_api_delete', methods: ['DELETE'])]
    public function delete(string $id, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $id = intval($id);
        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid auditor ID');
        }

        $loggedInAuditorId = $session->get('auditor_id');

        if (!$loggedInAuditorId) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $auditor = $entityManager->getRepository(Auditor::class)->find($id);

        if (!$auditor) {
            return new JsonResponse(['message' => 'No auditor found'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($auditor);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Auditor deleted', 'username' => $auditor->getUsername()]);
    }
}
