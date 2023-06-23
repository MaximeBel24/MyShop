<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Produit;
use App\Form\MembreType;
use App\Form\ProduitType;
use App\Repository\MembreRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

//###########################  MEMBRE ##################################

    #[Route('/admin/gestionMembre', name: 'gestion_membre')]
    public function adminMembre(MembreRepository $repo, EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Membre::class)->getFieldNames();

        $membres = $repo->findAll();
        return $this->render('admin/gestionMembre.html.twig', [
            "colonnes" => $colonnes,
            "membres" => $membres
        ]);
    }

    #[Route("/admin/membre/edit/{id}", name:"edit_membre")]
    public function formMembre(Request $request, EntityManagerInterface $entityManager, Membre $user = null): Response
    {
        if($user == null)
        {
            $user = new Membre();
        }       
        $form = $this->createForm(MembreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $statut = $form->get('statut')->getData();

            if ($statut == 1) {
                $role = 'ROLE_ADMIN';
            } elseif ($statut == 2) {
                $role = 'ROLE_USER';
            } else {
                
                $role = 'ROLE_USER';
            }

            $user->setRoles([$role]);

            $entityManager->persist($user);
            $entityManager->flush();
            

            return $this->redirectToRoute('gestion_membre');
        }

        return $this->render('admin/formMembre.html.twig', [
            'membreForm' => $form->createView(),
            "editMode" => $user->getId() !== null
        ]);
    }

    #[Route("/admin/membre/delete/{id}", name:'delete_membre')]
    public function deleteMembre(Membre $membre, EntityManagerInterface $manager)
    {
        $manager->remove($membre);
        $manager->flush();
        $this->addFlash('success', "Le membre a bien été supprimer");
        return $this->redirectToRoute('gestion_membre');
    } 

//########################### PRODUITS ##################################

#[Route('/admin/gestionProduit', name: 'gestion_produit')]
public function adminProduit(ProduitRepository $repo, EntityManagerInterface $manager)
{
    $colonnes = $manager->getClassMetadata(Produit::class)->getFieldNames();

    $produits = $repo->findAll();
    return $this->render('admin/gestionProduit.html.twig', [
        "colonnes" => $colonnes,
        "produits" => $produits
    ]);
}

#[Route("/admin/produit/edit/{id}", name:"edit_produit")]
    #[Route('/admin/produit/new', name:'new_produit')]
    public function formProduit(Request $globals, EntityManagerInterface $manager, Produit $produit = null)
    {
        if($produit == null)
        {
            $produit = new Produit;
        }        
        $form= $this->createForm(ProduitType::class, $produit );

        $form->handleRequest($globals);

        if($form->isSubmitted() && $form->isValid())
        {
            $produit->setDateEnregistrement(new \DateTime);
            $manager->persist($produit);
            $manager->flush();
            $this->addFlash('success', "Le produit a bien été ajouté");          
            return $this->redirectToRoute('gestion_produit');
        }

        return $this->render("admin/formProduit.html.twig", [
            "formProduit" => $form,
            "editMode" => $produit->getId() !== null
        ]);
    }

    #[Route("/admin/produit/delete/{id}", name:'delete_produit')]
    public function deleteproduit(Produit $produit, EntityManagerInterface $manager)
    {
        $manager->remove($produit);
        $manager->flush();
        $this->addFlash('success', "Le produit a bien été supprimer");
        return $this->redirectToRoute('gestion_produit');
    }

}



