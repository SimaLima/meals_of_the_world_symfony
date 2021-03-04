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

/**
 * @Route("/meal", name="meal.")
 */
class MealController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        // $some_data = $request->query->get('filter_meals');
        // dump($some_data['category']);
        // $data['lang']->getLocale()

        $form = $this->createForm(FilterMealsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dump($data);
            // return $this->redirectToRoute('index');
        }

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
