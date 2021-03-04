<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTime;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Language;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;




class FilterMealsType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
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
            ->add('category', ChoiceType::class, [
                'required' => false,
                'label' => 'Category:',
                'row_attr' => ['class' => 'select-type category-select'],
                'choices' => $this->getCategoryOptions(),
                'constraints' => [new Range(['min'=> -1, 'max' => 20,])],
            ])
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
                // 'constraints' => '',
            ])
            ->add('lang', EntityType::class, [
                'required' => true,
                'label' => 'Language:',
                'row_attr' => ['class' => 'select-type language-select'],
                'class' => Language::class,
                'choice_label' => 'title',
                // 'constraints' => '',
            ])
            ->add('diff_time', DateType::class, [
                'required' => false,
                'label' => 'Select date:',
                'row_attr' => ['class' => 'date-type date-picker'],
                'widget' => 'single_text',
                // 'constraints' => '',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter',
                'row_attr' => ['class' => 'submit-type submit-button'],
            ])
            ->setMethod('GET')
        ;
    }

    protected function getCategoryOptions()
    {
        // add "null" and "!null" options to categories
        $options = [];
        $category_results = $this->entityManager->getRepository(Category::class)->findAll();

        foreach ($category_results as $category) {
            $options[$category->getTitle()] = $category->getId();
        }

        $options['null'] = 0;
        $options['!null'] = -1;
        return $options;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
