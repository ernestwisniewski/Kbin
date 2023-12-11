<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\MessageBus;

use App\Kbin\MessageBus\Contracts\AsyncMessageInterface;

class UserDeleteMessage implements AsyncMessageInterface
{
    public function __construct(public int $id, public bool $purge, public bool $contentOnly)
    {
    }
}
