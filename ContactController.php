<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactSearchType;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contacts', name: 'symfony_contacts_index')]
    public function index(Request $request, ContactRepository $repo)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(ContactSearchType::class, [
            'sort' => $request->query->get('sort', 'name'),
            'dir'  => $request->query->get('dir', 'ASC'),
        ]);
        $form->handleRequest($request);
        $data = $form->getData() ?? [];

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);

        $contacts = $repo->searchForUser(
            $data['q'] ?? null,
            $data['city'] ?? null,
            $data['tag'] ?? null,
            $user,
            $isAdmin,
            $data['sort'] ?? 'name',
            $data['dir'] ?? 'ASC'
        );

        return $this->render('contacts/index.html.twig', [
            'form' => $form->createView(),
            'contacts' => $contacts,
        ]);
    }

    #[Route('/contacts/{id}', name: 'symfony_contacts_show', requirements: ['id' => '\d+'])]
    public function show(Contact $contact)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->denyAccessUnlessGranted('CONTACT_VIEW', $contact);

        return $this->render('contacts/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/login', name: 'symfony_login')]
    public function login()
    {
        return $this->render('security/login.html.twig');
    }

    #[Route('/logout', name: 'symfony_logout')]
    public function logout() { /* géré par Symfony */ }
}
