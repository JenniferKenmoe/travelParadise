<?php
namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Visitor;

class VisitorParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('visitor', EntityType::class, [
                'class' => Visitor::class,
                'choice_label' => function ($visitor) {
                    return $visitor->__toString();
                },
                'label' => 'Visiteur',
            ]);
        if ($options['display_comment']) {
            $builder->add('present', CheckboxType::class, [
                'label' => 'Présent',
                'required' => false,
            ]);
            $builder->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\VisitorParticipation::class,
            'display_comment' => true,
        ]);
    }
}