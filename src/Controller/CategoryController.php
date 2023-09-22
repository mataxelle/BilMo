<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route('/api/category', name: 'app_category_')]
class CategoryController extends AbstractController
{
    /**
     * Get all category list.
     *
     * @OA\Tag(name="Category")
     *
     * @Security(name="Bearer")
     *
     * @param  CategoryRepository  $categoryRepository  CategoryRepository
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function getCategoryList(CategoryRepository $categoryRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $categoryList = $categoryRepository->findAll();
        $context = SerializationContext::create()->setGroups(['category:read']);
        $jsonCategoryList = $serializerInterface->serialize($categoryList, 'json', $context);

        return new JsonResponse($jsonCategoryList, Response::HTTP_OK, [], true);
    }

    /**
     * Create a category. 
     * Exemple : 
     * {
     *     "name": "Categoryname"
     * }
     *
     * @OA\Tag(name="Category")
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
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une catégorie')]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        Request $request
    ): JsonResponse {
        $category = $serializerInterface->deserialize($request->getContent(), Category::class, 'json');

        $errors = $validator->validate($category);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['category:read']);
        $jsonCategory = $serializerInterface->serialize($category, 'json', $context);

        $location = $urlGenerator->generate('app_category_detail', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCategory, Response::HTTP_OK, ['location' => $location], true);
    }

    /**
     * Get a category.
     *
     * @OA\Tag(name="Category")
     *
     * @Security(name="Bearer")
     *
     * @param  Category            $category            Category
     * @param  SerializerInterface $serializerInterface SerializerInterface
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getCategory(Category $category, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['category:read']);
        $jsonCategory = $serializerInterface->serialize($category, 'json', $context);
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }

    /**
     * Modify a brand. 
     * Exemple : 
     * {
     *     "name": "Categorynamemodify"
     * }
     *
     * @OA\Tag(name="Category")
     *
     * @Security(name="Bearer")
     *
     * @param  Category               $category            Category
     * @param  EntityManagerInterface $entityManager       EntityManager
     * @param  SerializerInterface    $serializerInterface SerializerInterface
     * @param  ValidatorInterface     $validator           Validator
     * @param  Request                $request             Request
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier une catégorie')]
    public function edit(
        Category $category,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        ValidatorInterface $validator,
        Request $request
    ): JsonResponse {
        $newCategory = $serializerInterface->deserialize($request->getContent(), Category::class, 'json');

        $category->setName($newCategory->getName());

        $errors = $validator->validate($category);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a category.
     *
     * @OA\Tag(name="Category")
     *
     * @Security(name="Bearer")
     *
     * @param  Category               $category      Category
     * @param  EntityManagerInterface $entityManager EntityManager
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une catégorie')]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($category);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
