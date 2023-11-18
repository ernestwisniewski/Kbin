<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\EntryDeletedNotificationMessage;
use App\Repository\EntryRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SentEntryDeletedNotificationHandler
{
    public function __construct(
        private readonly EntryRepository $repository,
        private readonly NotificationManager $manager
    ) {
    }

    public function __invoke(EntryDeletedNotificationMessage $message)
    {
        $entry = $this->repository->find($message->entryId);

        if (!$entry) {
            throw new UnrecoverableMessageHandlingException('Entry not found');
        }

        $this->manager->sendDeleted($entry);
    }
}
