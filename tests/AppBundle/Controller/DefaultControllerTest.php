<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Link;

class DefaultControllerTest extends WebTestCase {

  public function testIndex() {
    $client = static::createClient();

    $crawler = $client->request('GET', '/');

    $this->assertEquals(200, $client->getResponse()->getStatusCode());
    $this->assertContains('You are not logged', $crawler->filter('#container h1')->text());
    $link = new Link('/login');
    $client->click($link);
    $this->assertEquals(200, $client->getResponse()->getStatusCode());

  }
}
