<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Facture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de quelques clients
        for ($i = 1; $i <= 5; $i++) {
            $customer = new Customer();
            $customer->setCustomerId('C00'.$i);
            $customer->setCustomerName('Client '.$i);
            $customer->setSegment('Segment '.$i);
            $customer->setCountry('Maroc');
            $customer->setCity('Casablanca');
            $customer->setState('Casablanca-Settat');
            $customer->setRegion('Nord Afrique');
            $customer->setPostalCode('20000');

            $manager->persist($customer);

            // Création d'une facture pour chaque client
            $facture = new Facture();
            $facture->setMontant(1000 * $i);
            $facture->setSentAt(new \DateTime());
            $facture->setStatut('Payée');
            $facture->setCustomer($customer);

            $manager->persist($facture);
        }

        // Envoi en base
        $manager->flush();
    }
}
