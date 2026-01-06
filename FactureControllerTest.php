<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FactureControllerTest extends WebTestCase
{
    public function testIndexPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/facture/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Factures');
    }

    public function testNewFactureFormSubmission(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/facture/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une Nouvelle Facture');

        $form = $crawler->selectButton('Créer')->form([
            'facture[montant]' => 1500,
            'facture[sentAt]' => '2026-01-06T10:00',
            'facture[statut]' => 'En attente',
            'facture[customer]' => 1, // suppose qu’un client avec ID 1 existe
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('.table', '1500');
    }

    public function testShowFacturePage(): void
    {
        $client = static::createClient();
        // Suppose qu’une facture avec ID 1 existe dans les fixtures
        $client->request('GET', '/facture/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testEditFacture(): void
    {
        $client = static::createClient();
        // Suppose qu’une facture avec ID 1 existe
        $crawler = $client->request('GET', '/facture/1/edit');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Mettre à jour')->form([
            'facture[montant]' => 2000,
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('.table', '2000');
    }

    public function testDeleteFacture(): void
    {
        $client = static::createClient();
        // Suppose qu’une facture avec ID 1 existe
        $client->request('GET', '/facture/1/delete');
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('.table', '2000');
    }
}
