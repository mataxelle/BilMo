<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
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

#[Route('/api/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function getProductList(ProductRepository $productRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $productList = $productRepository->findAllWithPagination($page, $limit);
        $context = SerializationContext::create()->setGroups(['product:read']);
        $jsonProductList = $serializerInterface->serialize($productList, 'json', $context);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter un produit')]
    public function create(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        UrlGeneratorInterface $urlGenerator,
        BrandRepository $brandRepository,
        CategoryRepository $categoryRepository,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $product = $serializerInterface->deserialize($request->getContent(), Product::class, 'json');

        $errors = $validator->validate($product);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $brandId = $content['brandId'] ?? -1;
        $categoryId = $content['categoryId'] ?? -1;

        $product->setBrand($brandRepository->find($brandId));
        $product->setCategory($categoryRepository->find($categoryId));

        $entityManager->persist($product);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['product:read']);
        $jsonProduct = $serializerInterface->serialize($product, 'json', $context);

        $location = $urlGenerator->generate('app_product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializerInterface): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['product:read']);
        $jsonProduct = $serializerInterface->serialize($product, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un produit')]
    public function edit(
        Product $product,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializerInterface,
        BrandRepository $brandRepository,
        CategoryRepository $categoryRepository,
        ValidatorInterface $validator,
        Request $request): JsonResponse
    {
        $newProduct = $serializerInterface->deserialize($request->getContent(), Product::class, 'json');

        $product->setName($newProduct->getName());
        $product->setDescription($newProduct->getDescription());
        $product->setPrice($newProduct->getPrice());
        $product->setSku($newProduct->getSku());

        // Errors verifications
        $errors = $validator->validate($product);
        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $brandId = $content['brandId'] ?? -1;
        $categoryId = $content['categoryId'] ?? -1;

        $product->setBrand($brandRepository->find($brandId));
        $product->setCategory($categoryRepository->find($categoryId));

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un produit')]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

