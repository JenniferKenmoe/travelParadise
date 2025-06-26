<?php

namespace App\Controller\Admin;

use App\Entity\Visitor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

class VisitorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Visitor::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastName', 'Nom'),
            TextField::new('firstName', 'Prénom'),
            EmailField::new('email', 'Email'),
            TextField::new('identityNumber', 'Numéro'),
        ];
    }
}
