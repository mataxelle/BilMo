<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/brand', name: 'app_brand_')]
class BrandController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function getBrandList(BrandRepository $brandRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $brandList = $brandRepository->findAll();
        $jsonBrandList = $serializerInterface->serialize($brandList, 'json', ['groups' => 'brand:read']);

        return new JsonResponse($jsonBrandList, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une marque')]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        Request $request): JsonResponse
    {
        $brand = $serializerInterface->deserialize($request->getContent(), Brand::class, 'json');
        $entityManager->persist($brand);
        $entityManager->flush();

        $jsonBrand = $serializerInterface->serialize($brand, 'json', ['groups' => 'brand:read']);

        $location = $urlGenerator->generate('app_brand_detail', ['id' => $brand->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBrand, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getBrand(Brand $brand, SerializerInterface $serializerInterface): JsonResponse
    {
        $jsonBrand = $serializerInterface->serialize($brand, 'json', ['groups' => 'brand:read']);
        return new JsonResponse($jsonBrand, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier une marque')]
    public function edit(
        Brand $brand,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        Request $request): JsonResponse
    {
        $brand = $serializerInterface->deserialize($request->getContent(), Brand::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $brand]);
        
        $entityManager->persist($brand);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une marque')]
    public function deleteBrand(Brand $brand, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($brand);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
