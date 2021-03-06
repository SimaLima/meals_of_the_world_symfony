<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Choice;
use App\Entity\Tag;
use App\Entity\Language;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class FilterMealsType extends AbstractType
{
    private $em;
    private $categ_rep;
    private $language;

    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $this->em = $entityManager;
        $this->categ_rep = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('per_page', IntegerType::class, [
                'required' => false,
                'label' => 'Per page:',
                'row_attr' => ['class' => 'input-type per-page-input'],
                'attr' => ['min' => 1, 'max' => 50],
                'constraints' => [new Range(['min'=> 1, 'max' => 50])],
            ])
            ->add('page', IntegerType::class, [
                'required' => false,
                'label' => 'Page:',
                'row_attr' => ['class' => 'input-type page-input'],
                'attr' => ['min' => 1, 'max' => 10],
                'constraints' => [new Range(['min'=> 1, 'max' => 10])],
            ])
            // ->add('category', ChoiceType::class, [
            //     'required' => false,
            //     'label' => 'Category:',
            //     'row_attr' => ['class' => 'select-type category-select'],
            //     'choices' => $this->getCategoryOptions(),
            //     'choice_label' => function($category) {
            //         if ($category == 'null') return 'null';
            //         if ($category == 'not_null') return '!null';
            //         // translation
            //         $category = $this->categ_rep->find($category);
            //         $category->setTranslatableLocale($this->language);
            //         $this->em->refresh($category);
            //         return $category->getTitle();
            //     },
            // ])
            ->add('with', ChoiceType::class, [
                'required' => false,
                'label' => 'With:',
                'row_attr' => ['class' => 'multiselect-type with-options'],
                'multiple' => true,
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
            ->add('tags', EntityType::class, [
                'required' => false,
                'label' => 'Tags:',
                'row_attr' => ['class' => 'multiselect-type tag-options'],
                'multiple' => true,
                'class' => Tag::class,
                'choice_label' => 'title',
                // 'query_builder' => function (TagRepository $tag) {
                //     return $tag->createQueryBuilder('tag')->getQuery()->setHint(
                //         \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                //         'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                //     )
                //     ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'hr_HR');
                //     // ->orderBy('tag.id', 'ASC');
                // },
                // 'choice_label' => function($tag) {
                //     // translation
                //     $tag->setTranslatableLocale($this->language);
                //     $this->em->refresh($tag);
                //     return $tag->getTitle();
                // },
            ])
            ->add('lang', EntityType::class, [
                'required' => true,
                'label' => 'Language:',
                'row_attr' => ['class' => 'select-type language-select'],
                'class' => Language::class,
                'choice_label' => 'title',
            ])
            ->add('diff_time', DateType::class, [
                'required' => false,
                'label' => 'Select date:',
                'row_attr' => ['class' => 'date-type date-picker'],
                'widget' => 'single_text',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter',
                'row_attr' => ['class' => 'submit-type submit-button'],
            ])
            ->setMethod('GET')
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $this->language = (isset($data['lang'])) ? $data['lang']->getLocale() : 'en_US';
            })
        ;
    }

    public function getCategoryOptions()
    {
        $options = [];
        $categories = $this->categ_rep->findAll();
        foreach ($categories as $category) {
            $options[$category->getTitle()] = $category->getId();
        }
        $options['null'] = 'null';
        $options['!null'] = 'not_null';
        return $options;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
