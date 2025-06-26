<?php

namespace App\Controller\Admin;

use App\Entity\VisitorParticipation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class VisitorParticipationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorParticipation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('visite', 'Visite'),
            AssociationField::new('visitor', 'Visiteur')
                ->formatValue(function ($value, $entity) {
                    if ($entity instanceof VisitorParticipation && $entity->getVisitor()) {
                        return $entity->getVisitor()->__toString();
                    }
                    return $value;
                }),
            BooleanField::new('present', 'Présent'),
            TextareaField::new('comment', 'Commentaire'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('visite'));
    }
}
