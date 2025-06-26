<?php

namespace App\Controller\Admin;

use App\Entity\Visite;
use App\Form\VisitorParticipationType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;

class VisiteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Visite::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextareaField::new('imageFile', 'Photo')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
                
            ImageField::new('photoUrl', 'Image')
                ->setBasePath('/uploads/visites')
                ->onlyOnIndex(),

            AssociationField::new('country', 'Pays'),

            AssociationField::new('assignedGuide', 'Nom du guide'),

            TextField::new('placeToVisit', 'Lieu'),
            DateField::new('visitDate', 'Date'),
            TimeField::new('startTime', 'Heure de début'),
            NumberField::new('duration', 'Durée (en heure)')
                ->setNumDecimals(2),

            TimeField::new('endTime', 'Heure de fin')
                ->setFormTypeOption('disabled', true),

            // Affichage du statut avec couleur
            TextField::new('statusLabel', 'Statut')
                ->formatValue(function ($value, $entity) {
                    if ($entity instanceof Visite) {
                        $status = $entity->getStatusLabel();
                        $color = match ($entity->getStatus()) {
                            'en_cours' => 'green',
                            'terminee' => 'blue',
                            default => 'gray'
                        };
                        return sprintf('<span style="color:%s;font-weight:bold;">%s</span>', $color, $status);
                    }
                    return $value;
                })
                ->onlyOnIndex(),

            IntegerField::new('visitorCount', 'Visiteurs')
                ->formatValue(function ($value, $entity) {
                    if ($entity instanceof Visite) {
                        $id = $entity->getId();
                        return sprintf(
                            '<b href="%d">%d</b>',
                            $id,
                            $entity->getVisitorCount()
                        );
                    }
                    return $value;
                })
                ->onlyOnIndex(),

            // Formulaire d’édition/création
            CollectionField::new('visitorParticipations', 'Liste des visiteurs')
                ->setEntryType(VisitorParticipationType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'entry_options' => [
                        'display_comment' => $pageName !== 'new'
                    ],
                ])
                ->onlyOnForms(),

            TextareaField::new('visitComment', 'Commentaire sur la visite')->hideOnIndex(),
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
    }
}
