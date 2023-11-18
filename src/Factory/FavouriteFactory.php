<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Favourite;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class FavouriteFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createFromEntity(User $user, FavouriteInterface $subject): Favourite
    {
        $className = $this->entityManager->getClassMetadata(\get_class($subject))->name.'Favourite';

        return new $className($user, $subject);
    }
}
