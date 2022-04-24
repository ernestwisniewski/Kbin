<?php declare(strict_types=1);

namespace App\Tests\Controller\User\Profile;

use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class UserNotificationControllerTest extends WebTestCase
{
    public function testUserReceiveNotifications()
    {
        $client = $this->createClient();
        $client->loginUser($owner = $this->getUserByUsername('owner'));

        $actor = $this->getUserByUsername('actor');

        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $owner);
        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $actor);

        $this->loadNotificationsFixture();

        $crawler = $client->request('GET', '/profil/notyfikacje');
        $this->assertCount(2, $crawler->filter('.kbin-notifications .toast-header'));

        $client->restart();
        $client->loginUser($actor);

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $this->assertCount(3, $crawler->filter('.kbin-notifications .toast-header'));

        $client->restart();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $this->assertCount(2, $crawler->filter('.kbin-notifications .toast-header'));
    }

    public function testUserCanReadAllNotifications()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('owner'));
        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('actor'));

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));

        $crawler = $client->request('GET', '/profil/notyfikacje');
        $crawler = $client->request('GET', '/profil/notyfikacje');

        $this->assertCount(2, $crawler->filter('.table-responsive .toast-header'));
        $this->assertCount(0, $crawler->filter('.table-responsive .opacity-50 .toast-header'));

        $client->submit($crawler->selectButton('odczytaj wszystkie')->form());

        $crawler = $client->followRedirect();

        $this->assertCount(2, $crawler->filter('.kbin-notifications .opacity-50 .toast-header'));
    }

    public function testUserCanDeleteAllNotifications()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('owner'));
        (static::getContainer()->get(MagazineManager::class))->subscribe($this->getMagazineByName('polityka'), $this->getUserByUsername('actor'));

        $this->loadNotificationsFixture();

        $client->loginUser($this->getUserByUsername('owner'));
        $crawler = $client->request('GET', '/profil/notyfikacje');
        $crawler = $client->request('GET', '/profil/notyfikacje');

        $this->assertCount(2, $crawler->filter('.table-responsive .toast-header'));

        $client->submit($crawler->selectButton('wyczyść')->form());

        $crawler = $client->followRedirect();

        $this->assertCount(0, $crawler->filter('.table-responsive .toast-header'));
    }
}
