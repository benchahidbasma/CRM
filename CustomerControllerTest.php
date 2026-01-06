<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerControllerTest extends WebTestCase
{
    public function testIndexPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/customer/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Clients');
    }

    public function testNewCustomerFormSubmission(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/customer/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau Client');

        $form = $crawler->selectButton('Créer')->form([
            'customer[customerName]' => 'Client Test',
            'customer[email]' => 'client@test.com',
            'customer[phone]' => '0600000000',
            'customer[city]' => 'Casablanca',
            'customer[country]' => 'Maroc',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('.table', 'Client Test');
    }

    public function testShowCustomerPage(): void
    {
        $client = static::createClient();
        // Suppose qu’un client avec ID 1 existe dans les fixtures
        $client->request('GET', '/customer/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testEditCustomer(): void
    {
        $client = static::createClient();
        // Suppose qu’un client avec ID 1 existe
        $crawler = $client->request('GET', '/customer/1/edit');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Mettre à jour')->form([
            'customer[customerName]' => 'Client Modifié',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('.table', 'Client Modifié');
    }

    public function testDeleteCustomer(): void
    {
        $client = static::createClient();
        // Suppose qu’un client avec ID 1 existe
        $client->request('GET', '/customer/1/delete');
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
$this->assertSelectorTextNotContains('.table', 'Client Modifié');
    }
}
