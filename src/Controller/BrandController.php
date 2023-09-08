<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Repository\BrandRepository;
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
use OpenApi\Annotations as OA;

#[Route('/api/brand', name: 'app_brand_')]
class BrandController extends AbstractController
{
    /**
     * Get all brand list.
     * 
     * @OA\Tag(name="Brand")
     *
     * @param  BrandRepository        $brandRepository
     * @param  SerializerInterface    $serializerInterface
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function getBrandList(BrandRepository $brandRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $brandList = $brandRepository->findAll();
        $context = SerializationContext::create()->setGroups(['brand:read']);
        $jsonBrandList = $serializerInterface->serialize($brandList, 'json', $context);

        return new JsonResponse($jsonBrandList, Response::HTTP_OK, [], true);
    }

    /**
     * Create a brand. 
     * Exemple : 
     * {
     *     "name": "Brandname"
     * }
     * 
     * @OA\Tag(name="Brand")
     *
     * @param  EntityManagerInterface $entityManager
     * @param  SerializerInterface    $serializerInterface
     * @param  UrlGeneratorInterface  $urlGenerator
     * @param  ValidatorInterface     $validator
     * @param  Request                $request
     * @return JsonResponse
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une marque')]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $brand = $serializerInterface->deserialize($request->getContent(), Brand::class, 'json');

        $errors = $validator->validate($brand);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($brand);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['brand:read']);
        $jsonBrand = $serializerInterface->serialize($brand, 'json', $context);

        $location = $urlGenerator->generate('app_brand_detail', ['id' => $brand->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBrand, Response::HTTP_OK, ['location' => $location], true);
    }

    /**
     * Get a brand.
     * 
     * @OA\Tag(name="Brand")
     *
     * @param  Brand                  $brand
     * @param  SerializerInterface    $serializerInterface
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getBrand(Brand $brand, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['brand:read']);
        $jsonBrand = $serializerInterface->serialize($brand, 'json', $context);
        return new JsonResponse($jsonBrand, Response::HTTP_CREATED, [], true);
    }

    /**
     * Modify a brand.
     * Exemple : 
     * {
     *     "name": "Brandnamemodify"
     * }
     * 
     * @OA\Tag(name="Brand")
     *
     * @param  Brand                  $brand
     * @param  EntityManagerInterface $entityManager
     * @param  SerializerInterface    $serializerInterface
     * @param  ValidatorInterface     $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier une marque')]
    public function edit(
        Brand $brand,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $newBrand = $serializerInterface->deserialize($request->getContent(), Brand::class, 'json');
        
        $brand->setName($newBrand->getName());

        $errors = $validator->validate($brand);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $entityManager->persist($brand);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a brand.
     * 
     * @OA\Tag(name="Brand")
     *
     * @param  Brand                     $brand
     * @param  EntityManagerInterface    $entityManager
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une marque')]
    public function deleteBrand(Brand $brand, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($brand);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
