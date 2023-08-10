<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour afficher la liste')]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $userList = $userRepository->findAllWithPagination($page, $limit);
        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUserList = $serializerInterface->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/client/{id}/list', name: 'client_list', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function getClientUserList(?Client $client, UserRepository $userRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $userList = $userRepository->findByClient($client, $page, $limit);
        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUserList = $serializerInterface->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        ClientRepository $clientRepository,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $clientId = $content['createdBy'] ?? -1;

        $user->setCreatedBy($clientRepository->find($clientId));

        $entityManager->persist($user);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUser = $serializerInterface->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('app_user_detail', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getUserDetail(User $user, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUser = $serializerInterface->serialize($user, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function edit(
        User $user,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        ClientRepository $clientRepository,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $newUser = $serializerInterface->deserialize($request->getContent(), User::class, 'json');

        $user->setFirstname($newUser->getFirstname());
        $user->setLastname($newUser->getLastname());
        $user->setEmail($newUser->getEmail());

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $clientId = $content['clientId'] ?? -1;

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
