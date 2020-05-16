<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTestControllerTest extends WebTestCase {

  /**
   * Test if route is accessable.
   */
  public function testIndex() {
    $client = static::createClient();

    $crawler = $client->request('GET', '/login');

    $this->assertEquals(200, $client->getResponse()->getStatusCode());
    $this->assertContains('Please log in', $crawler->filter('#container h1')->text());

  }
}
