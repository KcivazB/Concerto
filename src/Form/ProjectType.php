<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class)
        ->add('description', TextType::class)
        ->add('tags', EntityType::class, [
            'class' => Tag::class,
            'choice_label' => 'title',
            'group_by' => function($tag) {
                return $tag->getCategory() ? $tag->getCategory()->getTitle() : 'Other';
            },
            'multiple' => true,
            'attr' => ['class' => 'form-select mb-3', 'aria-label' => 'Select tags'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
