<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Member;
use App\Repository\UserRepository;
use App\Repository\MemberRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api/member', name: 'app_member_')]
class MemberController extends AbstractController
{
    /**
     * Get all member list.
     *
     * @OA\Response(
     *     response=200,
     *     description="Return all member list",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Member::class, groups={"member:read"}))
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
     * @OA\Tag(name="Member")
     *
     * @Security(name="Bearer")
     *
     * @param  MemberRepository    $memberRepository    MemberRepository
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @param  Request             $request             Request
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour afficher la liste')]
    public function getMemberList(MemberRepository $memberRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $memberList = $memberRepository->findAllWithPagination($page, $limit);
        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMemberList = $serializerInterface->serialize($memberList, 'json', $context);

        return new JsonResponse($jsonMemberList, Response::HTTP_OK, [], true);
    }

    /**
     * Get one user member list.
     *
     * @OA\Response(
     *     response=200,
     *     description="Return one user member list",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Member::class, groups={"member:read"}))
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
     * @OA\Tag(name="Member")
     *
     * @Security(name="Bearer")
     *
     * @param  User                $user                User
     * @param  MemberRepository    $memberRepository    MemberRepository
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @param  Request             $request             Request
     * @return JsonResponse
     */
    #[Route('/user/{id}/list', name: 'user_list', methods: ['GET'])]
    //#[Security("is_granted('ROLE_USER') and user === member.getCreatedBy() || is_granted('ROLE_ADMIN')")]
    public function getUserMemberList(?User $user, MemberRepository $memberRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $memberList = $memberRepository->findByUser($user, $page, $limit);
        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMemberList = $serializerInterface->serialize($memberList, 'json', $context);

        return new JsonResponse($jsonMemberList, Response::HTTP_OK, [], true);
    }

    /**
     * Create a member. 
     * Exemple : 
     * {
     *     "firstname": "memeber firstname",
     *     "lastname": "member lastname",
     *     "email": "Product description."
     * }
     *
     * @OA\Tag(name="Member")
     *
     * @Security(name="Bearer")
     *
     * @param  EntityManagerInterface $entityManager       EntityManager
     * @param  SerializerInterface    $serializerInterface SerializerInterface
     * @param  UrlGeneratorInterface  $urlGenerator        UrlGenerator
     * @param  ValidatorInterface     $validator           Validator
     * @param  Request                $request             Request
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    //#[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        Request $request
    ): JsonResponse {
        $member = $serializerInterface->deserialize($request->getContent(), Member::class, 'json');

        $errors = $validator->validate($member);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $member->setCreatedBy($this->getUser());

        $entityManager->persist($member);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMember = $serializerInterface->serialize($member, 'json', $context);

        $location = $urlGenerator->generate('app_member_detail', ['id' => $member->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonMember, Response::HTTP_OK, ['location' => $location], true);
    }

    /**
     * Get a member.
     *
     * @OA\Tag(name="Member")
     *
     * @Security(name="Bearer")
     *
     * @param  Member              $member              Member
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    //#[Security("is_granted('ROLE_USER') and user === member.getCreatedBy() || is_granted('ROLE_ADMIN')")]
    public function getMemberDetail(Member $member, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMember = $serializerInterface->serialize($member, 'json', $context);
        return new JsonResponse($jsonMember, Response::HTTP_CREATED, [], true);
    }

    /**
     * Modify a member. 
     * Exemple : 
     * {
     *     "firstname": "memeber firstnamemodify",
     *     "lastname": "member lastname",
     *     "email": "Product description."
     * }
     *
     * @OA\Tag(name="Member")
     *
     * @Security(name="Bearer")
     *
     * @param  EntityManagerInterface $entityManager       EntityManager
     * @param  SerializerInterface    $serializerInterface SerializerInterface
     * @param  UrlGeneratorInterface  $urlGenerator        UrlGenerator
     * @param  ValidatorInterface     $validator           Validator
     * @param  Request                $request             Request
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    //#[Security("is_granted('ROLE_USER') and user === member.getCreatedBy() || is_granted('ROLE_ADMIN')")]
    public function edit(
        Member $member,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        ValidatorInterface $validator,
        Request $request
    ): JsonResponse {
        $newMember = $serializerInterface->deserialize($request->getContent(), Member::class, 'json');

        $member->setFirstname($newMember->getFirstname());
        $member->setLastname($newMember->getLastname());
        $member->setEmail($newMember->getEmail());
        $member->setUpdatedBy($this->getUser());

        $errors = $validator->validate($member);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($member);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a member.
     *
     * @OA\Tag(name="Member")
     *
     * @Security(name="Bearer")
     *
     * @param  Member                 $member        Member
     * @param  EntityManagerInterface $entityManager EntityManager
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    //#[Security("is_granted('ROLE_USER') and user === member.getCreatedBy() || is_granted('ROLE_ADMIN')")]
    public function deleteMember(Member $member, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($member);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
