<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour afficher la liste')]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializerInterface->serialize($userList, 'json', ['groups' => 'user:read']);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/client/{id}/list', name: 'client_list', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function getClientUserList(?Client $client, UserRepository $userRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $userList = $userRepository->findByClient($client);
        $jsonUserList = $serializerInterface->serialize($userList, 'json', ['groups' => 'user:read']);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        ClientRepository $clientRepository,
        Request $request): JsonResponse
    {
        $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json');

        $content = $request->toArray();
        $clientId = $content['createdBy'] ?? -1;

        $user->setCreatedBy($clientRepository->find($clientId));

        $entityManager->persist($user);
        $entityManager->flush();

        $jsonUser = $serializerInterface->serialize($user, 'json', ['groups' => 'user:read']);

        $location = $urlGenerator->generate('app_user_detail', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getUserDetail(User $user, SerializerInterface $serializerInterface): JsonResponse
    {
        $jsonUser = $serializerInterface->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function edit(
        User $user,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        ClientRepository $clientRepository,
        Request $request): JsonResponse
    {
        $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

        $content = $request->toArray();
        $clientId = $content['updatedBy'] ?? -1;

        $user->setUpdatedBy($clientRepository->find($clientId));
        
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
