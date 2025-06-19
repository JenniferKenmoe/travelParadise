<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id', 'ID')->onlyOnIndex(),
            TextField::new('lastName', 'Nom'),
            TextField::new('firstName', 'Prénom'),
            EmailField::new('email', 'Email'),
            TextField::new('identityNumber', 'Numéro d\'identité'),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices(),
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
