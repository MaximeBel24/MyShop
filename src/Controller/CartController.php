<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Service\CartService;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(ProduitRepository $repo, CartService $cs, RequestStack $requestStack): Response
    {
        $cartWithData = $cs->cartWithData();
        $total = $cs->total();
        
        $showModal = $requestStack->getCurrentRequest()->query->get('showModal', false);

        return $this->render('cart/index.html.twig',[
            'items' => $cartWithData,
            'total' => $total,
            'showModal' => true
        ]);
    }

    #[Route('/cart/show-modal', name: 'app_cart_show_modal')]
    public function showModal(): Response
    {
        return $this->redirectToRoute('app_cart', ['showModal' => true]);
    }

    #[Route('/cart/ad/{id}', name:'cart_ad')]
    public function ad($id, CartService $cs)
    {
        
        $cs->add($id);
        return $this->redirectToRoute('home');
    }

    #[Route('/cart/add/{id}', name:'cart_add')]
    public function add($id, CartService $cs)
    {
        
        $cs->add($id);
        return $this->redirectToRoute('produit');
    }

    #[Route('/cart/addd/{id}', name:'cart_addd')]
    public function addd($id, CartService $cs)
    {
        
        $cs->add($id);
        return $this->redirectToRoute('app_cart');

    }

    #[Route('/cart/less/{id}', name:'cart_less')]
    public function less($id, CartService $cs)
    {
        
        $cs->less($id);
        return $this->redirectToRoute('app_cart');

    }

    
    #[Route('/cart/remove/{id}', name:'cart_remove')]
    public function remove($id, CartService $cs)
    {
        
        $cs->remove($id);
        
        return $this->redirectToRoute('app_cart');
    }

    
    #[Route('/cart/commande/', name: 'cart_commande')]
    public function cartCommande(EntityManagerInterface $manager, Request $request, Commande $commande = null, CartService $cs, ProduitRepository $repo): Response {
    $cartWithData = $cs->cartWithData();
    $total = $cs->total();
    $errors = [];

    foreach ($cartWithData as $item) {
        $produit = $repo->find($item['product']->getId());
        $quantiteCommandee = $item['quantity'];

        $montant = $produit->getPrix() * $quantiteCommandee;

        $commande = new Commande();
        $commande
            ->setMembre($this->getUser())
            ->setProduit($produit)
            ->setQuantite($quantiteCommandee)
            ->setMontant($montant)
            ->setEtat('En cours de traitement')
            ->setDateEnregistrement(new \DateTime());

        $manager->persist($produit);
        $manager->persist($commande);
        }

        $manager->flush();

        $cs->clearCart();

        return $this->redirectToRoute('cart_commandes_user');
    }

    #[Route('/cart/commandes', name: 'cart_commandes_user')]
    public function commandesUtilisateur(): Response
    {
        $user = $this->getUser();
    
        $commandes = $user->getCommandes();
    
        return $this->render('cart/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    } 

    #[Route('/cart/commandesClear', name: 'cart_commandes_clear')]
    public function viderPanier(CartService $cartService)
    {
        $cartService->viderPanier();

        return $this->redirectToRoute('app_cart');


    }

    #[Route('/cart/commande/{id}/delete', name: 'cart_commande_delete')]
    public function deleteCommande($id, EntityManagerInterface $manager, ProduitRepository $produitRepository): Response
    {
        $commande = $manager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            // Gérer le cas où la commande n'est pas trouvée
            // Par exemple, afficher une erreur ou rediriger l'utilisateur
            // ...
        }

        // Récupérer la quantité commandée et le produit associé à la commande
        $quantiteCommandee = $commande->getQuantite();
        $produit = $commande->getProduit();

        // Incrémenter la quantité commandée au stock du produit
        $nouveauStock = $produit->getStock() + $quantiteCommandee;
        $produit->setStock($nouveauStock);

        // Supprimer la commande
        $manager->remove($commande);
        $manager->flush();

        // Ajouter un message flash pour indiquer que la commande a été supprimée avec succès
        $this->addFlash('success', 'La commande a été supprimée avec succès.');

        return $this->redirectToRoute('cart_commandes_user');
    }

}
