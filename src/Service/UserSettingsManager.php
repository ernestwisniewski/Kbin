<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createDto(User $user): UserSettingsDto
    {
        return new UserSettingsDto(
            $user->notifyOnNewEntry,
            $user->notifyOnNewEntryReply,
            $user->notifyOnNewEntryCommentReply,
            $user->notifyOnNewPost,
            $user->notifyOnNewPostReply,
            $user->notifyOnNewPostCommentReply,
            $user->theme === User::THEME_DARK,
            $user->mode === User::MODE_TURBO
        );
    }

    public function update(User $user, UserSettingsDto $dto)
    {
        $user->notifyOnNewEntry             = $dto->notifyOnNewEntry;
        $user->notifyOnNewPost              = $dto->notifyOnNewPost;
        $user->notifyOnNewPostReply         = $dto->notifyOnNewPostReply;
        $user->notifyOnNewEntryCommentReply = $dto->notifyOnNewEntryCommentReply;
        $user->notifyOnNewEntryReply        = $dto->notifyOnNewEntryReply;
        $user->notifyOnNewPostCommentReply  = $dto->notifyOnNewPostCommentReply;
        if ($dto->darkTheme) {
            $user->theme = User::THEME_DARK;
        } else {
            $user->theme = User::THEME_LIGHT;
        }
        if($dto->turboMode) {
            $user->mode = User::MODE_TURBO;
        } else {
            $user->mode = User::MODE_NORMAL;
        }

        $this->entityManager->flush();
    }
}
