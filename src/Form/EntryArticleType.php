<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\EntryDto;
use App\Form\Autocomplete\MagazineAutocompleteField;
use App\Form\Constraint\ImageConstraint;
use App\Form\DataTransformer\TagTransformer;
use App\Form\EventListener\DisableFieldsOnEntryEdit;
use App\Form\EventListener\ImageListener;
use App\Form\Type\BadgesType;
use App\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryArticleType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextareaType::class)
            ->add('body', TextareaType::class)
            ->add('magazine', MagazineAutocompleteField::class)
            ->add('tags', TextType::class, [
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'create' => true,
                    'createOnBlur' => true,
                    'delimiter' => ',',
                ],
            ])
            ->add(
                'badges',
                BadgesType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'image',
                FileType::class,
                [
                    'required' => false,
                    'constraints' => ImageConstraint::default(),
                    'mapped' => false,
                ]
            )
            ->add('imageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('imageAlt', TextType::class, [
                'required' => false,
            ])
            ->add('isAdult', CheckboxType::class, [
                'required' => false,
            ])
            ->add('lang', ChoiceType::class, [
                'choices' => [
                    'english' => 'en',
                    'spanish' => 'es',
                    'polish' => 'pl',
                    'ukrainian' => 'uk',
                ],
                'required' => true,
                'autocomplete' => false,
                'tom_select_options' => [
                    'allowEmptyOption' => false,
                ],
            ])
            ->add('isOc', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class);

        $builder->get('tags')->addModelTransformer(
            new TagTransformer()
        );
        $builder->addEventSubscriber(new DisableFieldsOnEntryEdit());
        $builder->addEventSubscriber($this->imageListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => EntryDto::class,
            ]
        );
    }
}
