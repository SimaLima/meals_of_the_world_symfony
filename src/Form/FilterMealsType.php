<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Language;
use App\Entity\Category;
use App\Entity\Tag;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\LanguageRepository;


class FilterMealsType extends AbstractType
{
    private $categoryRepository;
    private $tagRepository;
    private $languageRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        LanguageRepository $languageRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->languageRepository = $languageRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // set language to use it in form (let it be like this for now...)
        $language = $options['data']['language'];

        if (is_numeric($language)) {
            if (in_array((int) $language, [2,3,4])) { // 'en' is fallback, no need to check
                $language = $this->languageRepository->find($language)->getLocale();
            }
        }

        $builder
            ->add('per_page', IntegerType::class, [
                'label' => 'Per page:',
                'required' => false,
                'row_attr' => [
                    'class' => 'input-type',
                    'title' => 'min: 1, max: 50',
                ],
                'attr' => [
                    'min' => 1,
                    'max' => 50,
                ],
                'constraints' => [
                    new Range([
                        'min'=> 1,
                        'max' => 50,
                    ])
                ],
            ])
            ->add('page', IntegerType::class, [
                'label' => 'Page:',
                'required' => false,
                'row_attr' => [
                    'class' => 'input-type',
                    'title' => 'min: 1, max: 10',
                ],
                'attr' => [
                    'min' => 1,
                    'max' => 10
                ],
                'constraints' => [
                    new Range([
                        'min'=> 1,
                        'max' => 10
                    ])
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category:',
                'required' => false,
                'row_attr' => [
                    'class' => 'select-type',
                    'title' => 'Odaberite kategoriju/null/!null.',
                ],
                'choices' => $this->categoryRepository
                                  ->getCategoryOptions($language),
            ])
            ->add('with', ChoiceType::class, [
                'label' => 'With:',
                'required' => false,
                'multiple' => true,
                'row_attr' => [
                    'class' => 'multiselect-type',
                    'title' => 'Odaberite dodatna svojstva za svako jelo. (multiselect)',
                ],
                'choices' => [
                    'tags' => 1,
                    'category' => 2,
                    'ingredients' => 3,
                ],
                'constraints' => [new Choice([
                    'multiple' => true,
                    'choices' => [1, 2, 3]
                ])],
            ])
            ->add('tags', ChoiceType::class, [
                'label' => 'Tags:',
                'required' => false,
                'multiple' => true,
                'row_attr' => [
                    'class' => 'multiselect-type',
                    'title' => 'Odaberite tagove. (multiselect)'
                ],
                'choices' => $this->tagRepository
                                  ->getTagOptions($language),
            ])
            ->add('lang', EntityType::class, [
                'label' => 'Language:',
                'required' => true,
                'class' => Language::class,
                'choice_label' => 'title',
                'row_attr' => [
                    'class' => 'select-type',
                    'title' => 'Odaberite jezik',
                ],
            ])
            ->add('diff_time', DateType::class, [
                'label' => 'Select date:',
                'required' => false,
                'widget' => 'single_text',
                'row_attr' => [
                    'class' => 'date-type',
                    'title' => 'Jela su kreirana 20 dana unazad od dana popunjavanja tablica. 1 jelo po danu. (20 ukupno)',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter',
                'row_attr' => [
                    'class' => 'submit-type',
                    'title' => 'Podnesi formu',
                ],
            ])
            ->setMethod('GET')
            // ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            // ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'])
        ;

    }

    // public function onPreSetData(FormEvent $event)
    // {
    // }
    //
    // public function onPostSubmit(FormEvent $event)
    // {
    //     $form = $event->getForm();
    //     $data = $event->getData();
    // }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'language' => 'en_US'
        ]);
    }
}
