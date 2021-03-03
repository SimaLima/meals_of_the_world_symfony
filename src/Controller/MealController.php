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



/**
 * @Route("/meal", name="meal.")
 */
class MealController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('meal/index.html.twig', [
            // 'form' => $form->createView(),
            // 'post' => $posts
        ]);
    }

}
