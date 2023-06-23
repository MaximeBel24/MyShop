<?php

namespace App\Controller\Admin;

use App\Entity\Membre;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Controller\Admin\MembreCrudController;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\ProduitCrudController;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\CommandeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class adminController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {

    }

    #[Route('/yametekudasai', name: 'yametekudasai')]
    public function index(): Response
    {
        // return parent::index();
        $url = $this->adminUrlGenerator->setController(MembreCrudController::class)->generateUrl();
        return $this->redirect($url);

        
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Myshop');
    }

    public function configureMenuItems(): iterable
    {
        return [
        MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
        MenuItem::linkToCrud('Membre', 'fas fa-user', Membre::class),
        MenuItem::subMenu('MyShop', 'fa fa-newspaper')->setSubItems([        
            MenuItem::linkToCrud('Produit', 'fas fa-list', Produit::class),
            MenuItem::linkToCrud('Commande', 'fas fa-book', Commande::class)
        ]),
        MenuItem::section('Retour au site'),
        MenuItem::linkToRoute('Accueil du site', 'fa fa-igloo', 'home'),
        ];
    }
}
