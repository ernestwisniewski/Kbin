<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Service\FavouriteManager;
use App\Tests\WebTestCase;

class EntryFavouriteControllerTest extends WebTestCase
{
    public function testLoggedUserCanAddToFavouritesEntry(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            null,
            $this->getUserByUsername('JaneDoe')
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->submit(
            $crawler->filter('#main .entry')->selectButton('favourites')->form([])
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .entry', 'favourites (1)');
    }
}
