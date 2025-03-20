<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManagerInterface;


    #[Route('/', name: 'app_product', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        try {
            $products = $productRepository->findAll();

            return $this->json([$products, 200]);

        } catch (\Throwable $th) {
            return $this->json(["Something wrong. Please contact administrator.",500]);
        }
    }

    #[Route('/{id}', name: 'get_product', methods: ['GET'])]
    public function get(int $id, ProductRepository $productRepository): JsonResponse
    {
        try {
            $product = $productRepository->find($id);

            if(!$product){
                return $this->json(["Product not found",401]);
            }

            return $this->json([$product, 201]);
        } catch (\Throwable $th) {
            return $this->json(["Something wrong. Please contact administrator.",500]);
        }
    }

    #[Route('/create', name: 'create_product', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if(!isset($data['name'], $data['price'])){
            return $this->json(['error' => "Insuficient data"]);
        }
        $description = isset($data['description']) ? $data['description'] : "";

        $product = new Product();
        $product->name($data['name']);
        $product->description($description);
        $product->price($data['price']);

        $this->entityManager->persist($product);
        $this->entityManager->flush();


        return $this->json([$product, 201]);
    }

    #[Route('/update/{id}', name: 'update_product', methods: ['PUT'])]
    public function update(int $id, Request $request, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $product = $productRepository->find($id);
        
        if(!$product){
            return $this->json(["Product not found",401]);
        }

        if(!isset($data['name'], $data['price'])){
            return $this->json(['error' => "Insuficient data"]);
        }
        $description = isset($data['description']) ? $data['description'] : "";

        $product->name($data['name']);
        $product->description($description);
        $product->price($data['price']);

        //save data
        $this->entityManager->persist($product);
        $this->entityManager->flush();


        return $this->json([$product, 201]);
    }
    
    #[Route('/delete/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function delete(int $id, ProductRepository $productRepository): JsonResponse
    {
        try {
            $product = $productRepository->find($id);

            if(!$product){
                return $this->json(["Product not found",401]);
            }

            $this->entityManagerInterface->remove($product);
            $this->entityManager->flush();

            return $this->json(["Product was deleted",200]);
            
        } catch (\Throwable $th) {
            return $this->json(["Something wrong. Please contact administrator.",500]);
        }
    }
}
