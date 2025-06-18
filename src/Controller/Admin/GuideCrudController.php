<?php

namespace App\Controller\Admin;

use App\Entity\Guide;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class GuideCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Guide::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $fields = [
            AssociationField::new('country'),
            TextField::new('lastName', 'Nom'),
            TextField::new('firstName', 'Prénom'),
            TextField::new('email', 'Email'),
            

            BooleanField::new('isActive', 'Actif')
                ->renderAsSwitch(false)
                ->onlyOnIndex(),

            BooleanField::new('isActive', 'Actif')
                ->renderAsSwitch()
                ->onlyOnForms(),

            TextareaField::new('imageFile')
                ->setFormType(VichImageType::class)
                ->onlyOnForms()
                ->setLabel('Photo de profil')
                ->setRequired(false), 
    
            ImageField::new('photoUrl', 'Photo')
                ->setBasePath('/uploads/guides')
                ->onlyOnIndex(),
        ];

        $passwordField = TextField::new('plainPassword', $pageName === 'new' ? 'Mot de passe' : 'Nouveau mot de passe (laisser vide pour ne pas changer)')
            ->onlyOnForms()
            ->setFormType(PasswordType::class)
            ->setRequired($pageName === 'new')
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('attr', ['autocomplete' => 'new-password', 'value' => '']);

        $fields[] = $passwordField;

        return $fields;
    }
}
