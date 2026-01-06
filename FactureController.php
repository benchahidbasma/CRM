<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Form\FactureType;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/facture')]
class FactureController extends AbstractController
{
    #[Route('/', name: 'facture_index')]
    public function index(FactureRepository $factureRepository): Response
    {
        // Liste toutes les factures
        return $this->render('facture/index.html.twig', [
            'factures' => $factureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'facture_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Crée une nouvelle facture
        $facture = new Facture();
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($facture);
            $em->flush();
            return $this->redirectToRoute('facture_index');
        }

        return $this->render('facture/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'facture_show', requirements: ['id' => '\d+'])]
    public function show(Facture $facture): Response
    {
        // Affiche les détails d’une facture
        return $this->render('facture/show.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/edit', name: 'facture_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        // Modifie une facture existante
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('facture_index');
        }

        return $this->render('facture/edit.html.twig', [
            'form' => $form->createView(),
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/delete', name: 'facture_delete', requirements: ['id' => '\d+'])]
    public function delete(Facture $facture, EntityManagerInterface $em): Response
    {
        // Supprime une facture
        $em->remove($facture);
        $em->flush();

        return $this->redirectToRoute('facture_index');
    }
}
