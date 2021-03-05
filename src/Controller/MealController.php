<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilder;
use App\Entity\Tag;
use App\Entity\Meal;
use App\Entity\Category;
use App\Entity\Ingredient;
use App\Form\FilterMealsType;
use App\Repository\MealRepository;

/**
 * @Route("/", name="meal.")
 */
class MealController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, MealRepository $mealRepository): Response
    {
        $response = '';
        $form = $this->createForm(FilterMealsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $results = $mealRepository->filter($data)
                                      ->getQuery()
                                      ->getResult();

            dump($results);
        }

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView(),
            'response' => $response
        ]);
    }

}
