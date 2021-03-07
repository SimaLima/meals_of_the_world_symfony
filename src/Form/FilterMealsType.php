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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;


class FilterMealsType extends AbstractType
{
    private $categoryRepository;
    private $tagRepository;

    public function __construct(CategoryRepository $categoryRepository, TagRepository $tagRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $language = $options['language'] ?? 'en_US';
        // if () $language = '';

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
                'choices' => $this->categoryRepository->getCategoryOptions($language),
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
            ->add('tags', ChoiceType::class, [
                'required' => false,
                'label' => 'Tags:',
                'row_attr' => ['class' => 'multiselect-type tag-options'],
                'multiple' => true,
                'choices' => $this->tagRepository->getTagOptions($language),
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
            // ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'])
        ;


        // $builder->get('lang')->addEventListener(
        //     FormEvents::POST_SUBMIT,
        //     function(FormEvent $event) {
        //         $form = $event->getForm();
        //         $data = $event->getData();
        //         dump($options['language']);
        //
        //         // if ($data) {
        //         //     $choices = $this->tagRepository->getTagOptions('hr_HR');
        //         // } else {
        //         //     $choices = $this->tagRepository->getTagOptions('en_US');
        //         // }
        //         //
        //         $form->getParent()->add('tags', ChoiceType::class, [
        //             'required' => false,
        //             'label' => 'Tags:',
        //             'row_attr' => ['class' => 'multiselect-type tag-options'],
        //             'multiple' => true,
        //             'choices' => $choices,
        //         ]);
        //     }
        // );


        // $builder->addEventListener(
        //     FormEvents::PRE_SET_DATA,
        //     function (FormEvent $event) {
        //         /** @var Article|null $data */
        //         $data = $event->getData();
        //         if (!$data) {
        //             return;
        //         }
        //         $this->setupSpecificLocationNameField(
        //             $event->getForm(),
        //             $data->getLocation()
        //         );
        //     }
        // );
        // $builder->get('location')->addEventListener(
        //     FormEvents::POST_SUBMIT,
        //     function(FormEvent $event) {
        //         $form = $event->getForm();
        //         $this->setupSpecificLocationNameField(
        //             $form->getParent(),
        //             $form->getData()
        //         );
        //     }
        // );

        // $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
        //     $data = $event->getData();
        //     $form = $event->getForm();
        //
        //     if (isset($data['language'])) {
        //         // $field2 = $this->container->get('repository')->find($data['field1'])->getValue();
        //         $data['language'] = 'hr_HR';
        //         $event->setData($data);
        //     }
        //     // dump($data);
        // });
    }

    public function onPreSetData(FormEvent $event)
    {
        // $data = $event->getForm()->getConfig()->getOptions()['language'];
        // $language = $event->getForm()->getConfig()->setAction(['language' => 'end']);
        // $this->language = (isset($data['lang'])) ? $data['lang']->getLocale() : 'en_US';
    }

    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        // $lang = $form->get('lang')->getData();

        // $data['language'] = '123';
        // $event->setData($data);
        // dump($event->getData());

        // if ($data['lang']->getLocale() == 'en_US') {
        //     return;
        // }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'language' => 'en_US'
        ]);
    }
}
