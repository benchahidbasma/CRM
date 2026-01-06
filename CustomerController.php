<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer')]
class CustomerController extends AbstractController
{
    #[Route('/', name: 'customer_index')]
    public function index(CustomerRepository $customerRepository): Response
    {
        // Liste tous les clients
        return $this->render('customer/index.html.twig', [
            'customers' => $customerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'customer_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Crée un nouveau client
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($customer);
            $em->flush();
            return $this->redirectToRoute('customer_index');
        }

        return $this->render('customer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'customer_show', requirements: ['id' => '\d+'])]
    public function show(Customer $customer): Response
    {
        // Affiche les détails d’un client
        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'customer_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $em): Response
    {
        // Modifie un client existant
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('customer_index');
        }

        return $this->render('customer/edit.html.twig', [
            'form' => $form->createView(),
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/delete', name: 'customer_delete', requirements: ['id' => '\d+'])]
    public function delete(Customer $customer, EntityManagerInterface $em): Response
    {
        // Supprime un client
        $em->remove($customer);
        $em->flush();

        return $this->redirectToRoute('customer_index');
    }
}
