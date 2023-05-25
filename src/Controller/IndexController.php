<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategorySearch;
use App\Entity\Product;
use App\Entity\PropertySearch;
use App\Form\CategorySearchType;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Form\PropertySearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    public function index(ManagerRegistry $doctrine, Request $request)
    {
        $property_search = new PropertySearch();

        $form = $this->createForm(PropertySearchType::class, $property_search);

        $form->handleRequest($request);

        $products = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $property_search->getName();

            if ($name!="") {
                $products = $doctrine->getRepository(Product::class)->findBy(['name' => $name]);
            } else {
                $products = $doctrine->getRepository(Product::class)->findAll();
            }
        }

        return $this->render('index/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

    #[Route('/product/save', name:'app_save')]
    public function save(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName('iPad Mini');
        $product->setPrice(2500);
        $product->setQuantity(10);

        $entityManager->persist($product);
        $entityManager->flush();

        return new Response ('Product added with ID : '.$product->getId());
    }

    #[Route('/product/add', name:'add_app', methods:['GET', 'POST'])]
    public function add(Request $request, ManagerRegistry $doctrine)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_index');
        }

        return $this->render('index/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/product/edit/{id}', name:'edit_app', methods:['GET', 'POST'])]
    public function edit($id, Request $request, ManagerRegistry $doctrine)
    {
        $product = new Product();
        $product = $doctrine->getRepository(Product::class)->find($id);

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('app_index');
        }

        return $this->render('index/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/product/{id}', name:'add')]
    public function show($id, ManagerRegistry $doctrine)
    {
        $product = $doctrine->getRepository(Product::class)->find($id);
        return $this->render('index/show.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/product/delete/{id}', name:'delete_app', methods: ['GET','DELETE'])]
    public function destroy($id, ManagerRegistry $doctrine)
    {
        $product = $doctrine->getRepository(Product::class)->find($id);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('app_index');
    }

    #[Route('category/add', name:'add_category', methods:['GET', 'POST'])]
    public function addCategory(Request $request, ManagerRegistry $doctrine) 
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_index');
        }

        return $this->render('index/add_category.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('productsByCategory/', name:'productsByCategory', methods:['GET', 'POST'])]
    public function productsByCategory(Request $request, ManagerRegistry $doctrine)
    {
        $categorySearch = new CategorySearch();

        $form = $this->createForm(CategorySearchType::class, $categorySearch);

        $form->handleRequest($request);

        $products = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();

            if ($category!=null) {
                $products = $category->getProducts();
            } else {
                $products = $doctrine->getRepository(Product::class)->findAll();
            }
        }

        return $this->render('index/productsByCategory.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

    // #[Route('productsByPrice', name:'productsByPrice', methods:['GET', 'POST'])]
    // public function productsByPrice(Request $request, ManagerRegistry $doctrine)
    // {
    //     $priceSearch = new PriceSearch();

    //     $form = $this->createForm(PriceSearchType::class, $priceSearch);

    //     $form->handleRequest($request);

    //     $products = [];

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $min = $priceSearch->getMinPrice();
    //         $max = $priceSearch->getMaxPrice();

    //         if ($min!=null && $max!=null) {
    //             $products = $doctrine->getRepository(Product::class)->findByPriceRange($min, $max);
    //         } else {
    //             $products = $doctrine->getRepository(Product::class)->findAll();
    //         }
    //     }

    //     return $this->render('index/productsByPrice.html.twig', [
    //         'products' => $products,
    //         'form' => $form->createView()
    //     ]);
    // }
}
