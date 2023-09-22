<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api/user', name: 'app_user_')]
class UserController extends AbstractController
{
    /**
     * Get all user list.
     *
     * @OA\Response(
     *     response=200,
     *     description="Return all user list",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"user:read"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The page we want",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="The number of elements we want to retrive",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @param  UserRepository      $userRepository      UserRepository
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @param  Request             $request             Request
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour afficher la liste de clients')]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $userList = $userRepository->findAllWithPagination($page, $limit);
        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUserList = $serializerInterface->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /**
     * Create a user. 
     * Exemple : 
     * {
     *     "name": "User name",
     *     "password": "password",
     *     "phone": "0987654321",
     *     "description": "User description",
     * }
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @param  EntityManagerInterface      $entityManager       EntityManager
     * @param  SerializerInterface         $serializerInterface SerializerInterface
     * @param  UrlGeneratorInterface       $urlGenerator        UrlGenerator
     * @param  UserPasswordHasherInterface $userPasswordHasher  userPasswordHasher
     * @param  ValidatorInterface          $validator           Validator
     * @param  Request                     $request             Request
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        Request $request
    ): JsonResponse {
        $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $user->setRoles(['ROLE_USER']);
        $password = $content['password'];

        $user->setPassword($userPasswordHasher->hashPassword($user, $password));

        $entityManager->persist($user);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUser = $serializerInterface->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('app_user_detail', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_OK, ['location' => $location], true);
    }

    /**
     * Get a user.
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @param  User                $user2               User2
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    //[Security("is_granted('ROLE_USER') and user === user2 || is_granted('ROLE_ADMIN')", message: 'Vous n\'avez pas les droits suffisants pour afficher ce contenu')]
    public function getUserDetails(User $user2, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['user:read']);
        $jsonUser = $serializerInterface->serialize($user2, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    /**
     * Modify a user. 
     * Exemple : 
     * {
     *     "name": "User namemodify",
     *     "password": "password",
     *     "phone": "0987654321",
     *     "description": "User description",
     * }
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @param  User                   $user                User
     * @param  EntityManagerInterface $entityManager       EntityManager
     * @param  SerializerInterface    $serializerInterface SerializerInterface
     * @param  ValidatorInterface     $validator           Validator
     * @param  Request                $request             Request
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(
        User $user,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        Request $request
    ): JsonResponse {
        $newUser = $serializerInterface->deserialize($request->getContent(), User::class, 'json');

        $user->setName($newUser->getName());
        $user->setEmail($newUser->getEmail());
        $user->setPassword($newUser->getPassword());
        $user->setPhone($newUser->getPhone());
        $user->setDescription($newUser->getDescription());

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        if ($content['password']) {
            $password = $content['password'];
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a user.
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @param  User                   $user          User
     * @param  EntityManagerInterface $entityManager EntityManager
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
