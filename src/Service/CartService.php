<?php

namespace App\Service;

use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class CartService
{
    private $repo;
    private $rs;
    private $entityManager;

    //injection de dépendances hors d'un controller : constructeur
    public function __construct(ProduitRepository $repo, RequestStack $rs, EntityManagerInterface $entityManager)
    {
        $this->rs = $rs;
        $this->repo = $repo;
        $this->entityManager = $entityManager;
    }

    public function add($id)
    {
        // Nous récupérons ou créons une session grâce à la classe RequestStack (service)
        $session = $this->rs->getSession();

        $cart = $session->get('cart', []);
        $qt = $session->get('qt', 0);

        // Je récupère le produit correspondant à l'ID
        $produit = $this->repo->find($id);

        if (!$produit) {
            // Gérer le cas où le produit n'est pas trouvé
            // Par exemple, afficher une erreur ou rediriger l'utilisateur
            return;
        }

        // Vérifier s'il y a suffisamment de stock disponible
        if ($produit->getStock() > 0) {
            // Décrémenter la quantité disponible du produit
            $produit->setStock($produit->getStock() - 1);

            // Sauvegarder les modifications en utilisant votre gestionnaire d'entités
            $this->entityManager->persist($produit);
            $this->entityManager->flush();

            // Ajouter le produit au panier
            if (!empty($cart[$id])) {
                $cart[$id]++;
                $qt++;
            } else {
                $cart[$id] = 1;
                $qt++;
            }
        }

        // Mettre à jour les valeurs de la session
        $session->set('qt', $qt);
        $session->set('cart', $cart);
    }


    public function less($id)
    {
        $session = $this->rs->getSession();
    
        $cart = $session->get('cart', []);
        $qt = $session->get('qt', 0);
    
        if (!empty($cart[$id])) {
            $cart[$id]--;
            $qt--;
    
            if ($cart[$id] === 0) {
                unset($cart[$id]);
    
                $produit = $this->repo->find($id);
    
                if ($produit) {
                    $produit->setStock($produit->getStock() + 1);
    
                    $this->entityManager->persist($produit);
                    $this->entityManager->flush();
                }
            }
        }
    
        $session->set('qt', $qt);
        $session->set('cart', $cart);
    }
    


    public function remove($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);
        $qt = $session->get('qt', 0);

        if (!empty($cart[$id])) {
            $quantityToRemove = $cart[$id];
            $cart[$id] = 0;
            $qt -= $quantityToRemove;

            // Récupérer le produit correspondant à l'ID
            $produit = $this->repo->find($id);

            if ($produit) {
                // Augmenter la quantité disponible du produit
                $produit->setStock($produit->getStock() + $quantityToRemove);

                // Sauvegarder les modifications en utilisant le gestionnaire d'entités
                $this->entityManager->persist($produit);
                $this->entityManager->flush();
            }
        }

        if ($qt < 0) {
            $qt = 0;
        }

        $session->set('qt', $qt);
        $session->set('cart', $cart);
    }

    public function cartWithData()
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);


        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $produit = $this->repo->find($id);

            if ($produit && $quantity > 0) {
                $cartWithData[] = [
                    'product' => $produit,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartWithData;
    }


    public function total()
    {
        $cartWithData = $this->cartWithData();
        $total = 0 ;

        foreach($cartWithData as $item)
        {
            $totalItem = $item['product']->getPrix() * $item['quantity'];
            $total += $totalItem;
        }

        return $total;
    }

    public function viderPanier(): void
    {
        $session = $this->rs->getSession();
        $session->remove('cart');
        $session->remove('qt');
    }

    public function clearCart(): void
    {
        $session = $this->rs->getSession();
        $session->remove('cart');
        $session->remove('qt');
    }

}
