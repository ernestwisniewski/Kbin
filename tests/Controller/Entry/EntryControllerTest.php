<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryControllerTest extends WebTestCase
{
    public function testCanCreateArticle()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/nowaTresc/artykul');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'przykladowa tresc',
                    'entry_article[body]'     => 'Lorem ipsum',
                    'entry_article[magazine]' => $magazine->getId(),
                ]
            )
        );

        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-entry-title', 'przykladowa tresc');
    }

    public function testCanCreateLink()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('polityka');
        $this->getEntryByTitle('test1');
        $this->getEntryByTitle('test2');

        $crawler = $client->request('GET', '/nowaTresc');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'przykladowa tresc',
                    'entry_link[url]'      => 'https://example.pl',
                    'entry_link[magazine]' => $magazine->getId(),
                ]
            )
        );

        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-entry-title', 'przykladowa tresc');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Treści 3');
    }

    public function testCanEditLink()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc', 'https://wp.pl');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/komentarze");
        $crawler = $client->click($crawler->filter('.kbin-entry-meta')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $entry->getMagazine()->getId(),
                ]
            )
        );

        $this->assertResponseRedirects("/m/polityka/t/{$entry->getId()}/zmieniona-tresc/komentarze");

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
    }

    public function testCanEditArticle()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc', null, 'przykładowa treść wpisu');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'zmieniona treść',
                    'entry_article[body]'     => 'zmieniona treść wpisu',
                    'entry_article[magazine]' => $entry->magazine->getId(),
                ]
            )
        );

        $this->assertResponseRedirects("/m/polityka/t/{$entry->getId()}/zmieniona-tresc/komentarze");

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
        $this->assertSelectorTextContains('.kbin-entry-content p', 'zmieniona treść wpisu');
    }

    public function testCannotEditEntryMagazine()
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $this->getMagazineByName('test')->getId(),
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testUnauthorizedUserCannotEditEntry()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser2'));

        $entry = $this->getEntryByTitle('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testCanDeleteEntry()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $entry = $this->getEntryByTitle('przykladowa tresc', null, 'przykładowa treść wpisu');
        $this->getEntryByTitle('test1');
        $this->getEntryByTitle('test2');

        $comment1 = $this->createEntryComment('test', $entry);
        $comment2 = $this->createEntryComment('test2', $entry, $user2, $comment1);

        $this->createVote(1, $entry, $user2);
        $this->createVote(1, $comment1, $user2);
        $this->createVote(1, $comment2, $user2);
        $this->createVote(1, $comment2, $user1);

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->selectButton('Usuń')->form()
        );

        $this->assertResponseRedirects("/m/polityka");

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('.kbin-entry-title', 'przykladowa tresc');
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine .kbin-magazine-stats-links', 'Treści 2');
    }

    public function testUnauthorizedUserCannotEditOrPurgeEntry()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('secondUser'));
        $client->catchExceptions(false);

        $entry = $this->getEntryByTitle('przykładowy wpis');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/komentarze");

        $this->assertEmpty($crawler->filter('.kbin-entry-meta')->selectLink('edytuj'));

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $this->assertTrue($client->getResponse()->isForbidden());
    }
}
