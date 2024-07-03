<?php 

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Idea;
use App\Entity\Status;
use App\Entity\TagCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class IdeaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'title',
                'group_by' => function($tag) {
                    return $tag->getCategory() ? $tag->getCategory()->getTitle() : 'Other';
                },
                'multiple' => true,
                'attr' => ['class' => 'form-select mb-3', 'aria-label' => 'Select tags'],
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-select mb-3', 'aria-label' => 'Select status'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Idea::class,
        ]);
    }
}
