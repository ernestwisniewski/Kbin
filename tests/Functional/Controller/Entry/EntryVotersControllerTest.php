<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Vote\VoteCreate;
use App\Tests\WebTestCase;

class EntryVotersControllerTest extends WebTestCase
{
    public function testUserCanSeeUpVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(VotableInterface::VOTE_UP, $entry, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->click($crawler->filter('.options-activity')->selectLink('boosts (1)')->link());

        $this->assertSelectorTextContains('#main .users-columns', 'JaneDoe');
    }

    public function testUserCanSeeDownVoters(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(VotableInterface::VOTE_DOWN, $entry, $this->getUserByUsername('JaneDoe'));

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/test-entry-1");

        $client->click($crawler->filter('.options-activity')->selectLink('reduces (1)')->link());

        $this->assertSelectorTextContains('#main .users-columns', 'JaneDoe');
    }
}
