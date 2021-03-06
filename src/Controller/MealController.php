<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilder;
use App\Entity\Tag;
use App\Entity\Meal;
use App\Entity\Category;
use App\Entity\Ingredient;
use App\Form\FilterMealsType;
use App\Repository\MealRepository;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
// use Doctrine\ORM\EntityManagerInterface;

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
        // $meta = ['currentPage' => '', 'totalItems' => '', 'itemsPerPage' => '', 'totalPages' => ''];
        // $links = ['first' => '', 'last' => '', 'prev' => '', 'next' => '', 'self' => ''];
        // $response = ['meta' => $meta, 'data' => $data, 'links' => $links];

        $form = $this->createForm(FilterMealsType::class);
        $form->handleRequest($request);
        $data = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data = $mealRepository->filter($data);
        }

        dump($data);

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView(),
            'response' => $data
        ]);
    }

}



// $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
// $jsonContent = $serializer->serialize($data, 'json');
// $json_response =  new JsonResponse($response);

// $rep = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
// $translat = $rep->findTranslations($mealRepository->find(1));
// dump($translat);


// $em = $this->getEntityManager();
// foreach ($results as $key => $result) {
//     $result->setTranslatableLocale($lang);
//     $em->refresh($result);
//     $item = [
//         'id' => $result->getId(),
//         'title' => $result->getTitle(),
//         'description' => $result->getDescription(),
//         'status' => 'status',
//     ];
//     dump($item);
// }
