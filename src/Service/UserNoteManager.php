<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserNote;
use App\Kbin\User\DTO\UserNoteDto;
use App\Repository\UserNoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserNoteManager
{
    public function __construct(private UserNoteRepository $repository, private EntityManagerInterface $entityManager)
    {
    }

    public function save(User $user, User $target, string $body): UserNote
    {
        $this->clear($user, $target);

        $note = new UserNote($user, $target, $body);

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return $note;
    }

    public function clear(User $user, User $target): void
    {
        $note = $this->repository->findOneBy([
            'user' => $user,
            'target' => $target,
        ]);

        if ($note) {
            $this->entityManager->remove($note);
            $this->entityManager->flush();
        }
    }

    public function createDto(User $user, User $target): UserNoteDto
    {
        $dto = new UserNoteDto();
        $dto->target = $target;

        $note = $this->repository->findOneBy([
            'user' => $user,
            'target' => $target,
        ]);

        if ($note) {
            $dto->body = $note->body;
        }

        return $dto;
    }
}
