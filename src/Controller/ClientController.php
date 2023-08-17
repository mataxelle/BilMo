<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/client', name: 'app_client_')]
class ClientController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour afficher la liste de clients')]
    public function getClientList(ClientRepository $clientRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $clientList = $clientRepository->findAllWithPagination($page, $limit);
        $context = SerializationContext::create()->setGroups(['client:read']);
        $jsonClientList = $serializerInterface->serialize($clientList, 'json', $context);

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $client = $serializerInterface->deserialize($request->getContent(), Client::class, 'json');

        $errors = $validator->validate($client);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $password = $content['password'];

        $client->setPassword($userPasswordHasher->hashPassword($client, $password));

        $entityManager->persist($client);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['client:read']);
        $jsonClient = $serializerInterface->serialize($client, 'json', $context);

        $location = $urlGenerator->generate('app_client_detail', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonClient, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getClient(Client $client, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['client:read']);
        $jsonClient = $serializerInterface->serialize($client, 'json', $context);
        return new JsonResponse($jsonClient, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(
        Client $client,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $newClient = $serializerInterface->deserialize($request->getContent(), Client::class, 'json');

        $client->setName($newClient->getName());
        $client->setEmail($newClient->getEmail());
        $client->setPassword($newClient->getPassword());
        $client->setPhone($newClient->getPhone());
        $client->setDescription($newClient->getDescription());

        $errors = $validator->validate($client);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        if ($content['password']) {
            $password = $content['password'];
            $client->setPassword($userPasswordHasher->hashPassword($client, $password));
        }
        
        $entityManager->persist($client);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteClient(Client $client, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($client);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
