<?php

namespace WSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SubscriberControllerTest extends WebTestCase
{
    public function testUnique($id)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());

        //Test #1 :
        //on connait l'id
        //alors on reçoit toutes les informations de l'adhérent

        //Test #2 :
        //on ne connait pas l'id
        //on retourne un message pour préciser qu'on ne connait pas l'adhérent
        //==> non implémenté
    }

    public function testSubscribers()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());

        //Test #1 :
        //le ws retourne tous les adhérents

        //Test #2 :
        //le ws retourne un message pour indiquer qu'il n'y a aucun adhérent
    }

    public function testImport()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());

        //Test #1 :
        //le fichier n'est pas présent
        //le ws retourne un message pour l'indiquer
    }
}
