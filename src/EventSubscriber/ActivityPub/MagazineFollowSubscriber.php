<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use App\Event\Magazine\MagazineSubscribedEvent;
use App\Message\ActivityPub\Outbox\FollowMessage;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MagazineFollowSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    #[ArrayShape([MagazineSubscribedEvent::class => 'string'])]
    public static function getSubscribedEvents(): array
    {
        return [
            MagazineSubscribedEvent::class => 'onMagazineFollow',
        ];
    }

    public function onMagazineFollow(MagazineSubscribedEvent $event): void
    {
        if ($event->magazine->apId && !$event->user->apId) {
            $this->bus->dispatch(
                new FollowMessage($event->user->getId(), $event->magazine->getId(), $event->unfollow, true)
            );
        }
    }
}
