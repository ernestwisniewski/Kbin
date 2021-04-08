<?php declare(strict_types=1);

namespace App\Service;

use App\Event\EntryDeletedEvent;
use App\Event\EntryPinEvent;
use App\Kernel;
use App\Service\Contracts\ContentManager;
use App\Utils\UrlCleaner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EntryRepository;
use App\Exception\BadUrlException;
use App\Event\EntryCreatedEvent;
use App\Event\EntryUpdatedEvent;
use App\Event\EntryBeforePurgeEvent;
use App\Factory\EntryFactory;
use Webmozart\Assert\Assert;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

class EntryManager implements ContentManager
{
    public function __construct(
        private EntryFactory $entryFactory,
        private EntryRepository $entryRepository,
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
        private HttpClientInterface $client,
        private Kernel $kernel,
        private BadgeManager $badgeManager,
        private UrlCleaner $urlCleaner,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryDto $entryDto, User $user): Entry
    {
        // @todo
        if ($this->security->getUser() && !$this->security->isGranted('create_content', $entryDto->getMagazine())) {
            throw new AccessDeniedHttpException();
        }

        if ($entryDto->getUrl()) {
            $entryDto->setUrl(($this->urlCleaner)($entryDto->getUrl()));
            $this->validateUrl($entryDto->getUrl());
        }

        $entry    = $this->entryFactory->createFromDto($entryDto, $user);
        $magazine = $entry->getMagazine();

        $this->assertType($entry);

        if ($entry->getUrl()) {
            $entry->setType(Entry::ENTRY_TYPE_LINK);
        }

        if ($entryDto->getBadges()) {
            $this->badgeManager->assign($entry, $entryDto->getBadges());
        }

        $magazine->addEntry($entry);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCreatedEvent($entry));

        return $entry;
    }

    public function edit(Entry $entry, EntryDto $entryDto): Entry
    {
        Assert::same($entry->getMagazine()->getId(), $entryDto->getMagazine()->getId());

        $entry->setTitle($entryDto->getTitle());
        $entry->setUrl($entryDto->getUrl());
        $entry->setBody($entryDto->getBody());
        $entry->setIsAdult($entryDto->isAdult());

        if ($entryDto->getImage()) {
            $entry->setImage($entryDto->getImage());
        }

        if ($entry->getUrl()) {
            $entry->setType(Entry::ENTRY_TYPE_LINK);
        }

        if ($entryDto->getBadges()) {
            $this->badgeManager->assign($entry, $entryDto->getBadges());
        }

        $this->badgeManager->assign($entry, $entryDto->getBadges());

        $this->assertType($entry);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryUpdatedEvent($entry)));

        return $entry;
    }

    public function delete(Entry $entry, bool $trash = false): void
    {
        $trash ? $entry->trash() : $entry->softDelete();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryDeletedEvent($entry, $this->security->getUser())));
    }

    public function purge(Entry $entry): void
    {
        $this->eventDispatcher->dispatch((new EntryBeforePurgeEvent($entry)));

        $entry->getMagazine()->removeEntry($entry);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }

    public function pin(Entry $entry): Entry
    {
        $entry->setSticky(!$entry->isSticky());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch((new EntryPinEvent($entry)));

        return $entry;
    }

    public function createDto(Entry $entry): EntryDto
    {
        return $this->entryFactory->createDto($entry);
    }

    private function assertType(Entry $entry): void
    {
        if ($entry->getUrl()) {
            Assert::null($entry->getBody());
        } else {
            Assert::null($entry->getUrl());
        }
    }

    private function validateUrl(string $url): void
    {
        if (!$url) {
            return;
        }

        // @todo checkdnsrr?
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadUrlException($url);
        }
    }
}
