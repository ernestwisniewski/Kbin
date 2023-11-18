<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Magazine;

use App\Event\Magazine\MagazineBanEvent;
use App\Message\Notification\MagazineBanNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MagazineBanSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MagazineBanEvent::class => 'onBan',
        ];
    }

    public function onBan(MagazineBanEvent $event): void
    {
        $this->bus->dispatch(new MagazineBanNotificationMessage($event->ban->getId()));
    }
}
