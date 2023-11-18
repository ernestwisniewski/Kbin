<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\EntryComment;

use App\Event\EntryComment\EntryCommentEditedEvent;
use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Message\Notification\EntryCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class EntryCommentEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInterface $cache, private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentEditedEvent::class => 'onEntryCommentEdited',
        ];
    }

    public function onEntryCommentEdited(EntryCommentEditedEvent $event): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()]);

        $this->bus->dispatch(new EntryCommentEditedNotificationMessage($event->comment->getId()));
        if ($event->comment->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->comment->body));
        }

        if (!$event->comment->apId) {
            $this->bus->dispatch(new UpdateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }
}
