<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
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

#[Route('/api/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function getProductList(ProductRepository $productRepository, SerializerInterface $serializerInterface, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $productList = $productRepository->findAllWithPagination($page, $limit);
        $jsonProductList = $serializerInterface->serialize($productList, 'json', ['groups' => 'product:read']);

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
        Request $request): JsonResponse
    {
        $product = $serializerInterface->deserialize($request->getContent(), Product::class, 'json');

        $content = $request->toArray();
        $brandId = $content['brandId'] ?? -1;
        $categoryId = $content['categoryId'] ?? -1;

        $product->setBrand($brandRepository->find($brandId));
        $product->setCategory($categoryRepository->find($categoryId));

        $entityManager->persist($product);
        $entityManager->flush();

        $jsonProduct = $serializerInterface->serialize($product, 'json', ['groups' => 'product:read']);

        $location = $urlGenerator->generate('app_product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['location' => $location], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializerInterface): JsonResponse
    {
        $jsonProduct = $serializerInterface->serialize($product, 'json', ['groups' => 'product:read']);
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
        Request $request): JsonResponse
    {
        $product = $serializerInterface->deserialize($request->getContent(), Product::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $product]);

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

