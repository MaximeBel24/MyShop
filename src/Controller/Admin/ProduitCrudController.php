<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre'),
            TextField::new('description'),
            ColorField::new('couleur'),
            ChoiceField::new('taille')->setChoices(['Small' => 'Small', 'Medium' =>'Medium', 'Large'=>'Large', 'ExtraLarge'=>'ExtraLarge']),
            ChoiceField::new('collection')->setChoices(['Homme' => 'homme', 'Femme' =>'femme']),
            ImageField::new('photo')->setBasePath('images/produit')->setUploadDir('public/images/produit')->setUploadedFileNamePattern('[slug]-[timestamp].[extension]'),
            IntegerField::new('prix'),
            IntegerField::new('stock'),
            DateTimeField::new('dateEnregistrement')->setFormat('d/M/Y à H:m:s')->hideOnForm(),
            
        ];
    }
    
    public function createEntity(string $entityFqcn)
    {
        $article = new $entityFqcn;
        $article->setDateEnregistrement(new DateTime);
        return $article;
    }
}
