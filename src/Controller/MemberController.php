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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/member', name: 'app_member_')]
class MemberController extends AbstractController
{
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

    #[Route('/user/{id}/list', name: 'user_list', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function getUserMemberList(?User $user, MemberRepository $memberRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $memberList = $memberRepository->findByUser($user, $page, $limit);
        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMemberList = $serializerInterface->serialize($memberList, 'json', $context);

        return new JsonResponse($jsonMemberList, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $member = $serializerInterface->deserialize($request->getContent(), Member::class, 'json');

        $errors = $validator->validate($member);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $userId = $content['createdBy'] ?? -1;

        $member->setCreatedBy($userRepository->find($userId));

        $entityManager->persist($member);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMember = $serializerInterface->serialize($member, 'json', $context);

        $location = $urlGenerator->generate('app_member_detail', ['id' => $member->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonMember, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function getMemberDetail(Member $member, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['member:read']);
        $jsonMember = $serializerInterface->serialize($member, 'json', $context);
        return new JsonResponse($jsonMember, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function edit(
        Member $member,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $newMember = $serializerInterface->deserialize($request->getContent(), Member::class, 'json');

        $member->setFirstname($newMember->getFirstname());
        $member->setLastname($newMember->getLastname());
        $member->setEmail($newMember->getEmail());

        $errors = $validator->validate($member);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $userId = $content['userId'] ?? -1;

        $member->setUpdatedBy($userRepository->find($userId));
        
        $entityManager->persist($member);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[Security("is_granted('ROLE_USER') || is_granted('ROLE_ADMIN')")]
    public function deleteMember(Member $member, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($member);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
