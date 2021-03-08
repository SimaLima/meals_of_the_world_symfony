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
        $form = $this->createForm(FilterMealsType::class);
        $form->handleRequest($request);
        $response = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $response = $mealRepository->filter($data, $request);
        }

        $json_response =  new JsonResponse($response);

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView(),
            'response' => $response, // formatted in twig (and {#commented#})
            'json_response' => $json_response // already formatted and minified
        ]);
    }
}
