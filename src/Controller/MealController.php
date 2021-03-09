<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MealRepository;
use App\Form\FilterMealsType;


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
        // pass language to form (caution: before validation)
        $language = ($request->get('filter_meals')) ? $request->get('filter_meals')['lang'] : 'en_US';

        // create form & handle request
        $form = $this->createForm(FilterMealsType::class, ['language' => $language]);
        $form->handleRequest($request);
        $response = [];

        // validate & filter data
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $response = $mealRepository->filter($data, $request);
        }

        // create json response
        $json_response =  new JsonResponse($response);

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView(),
            'response' => $response, // formatted in twig (and {#commented#})
            'json_response' => $json_response // already formatted and minified
        ]);
    }
}
