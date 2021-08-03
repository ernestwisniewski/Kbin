<?php declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\PostDeletedNotificationMessage;
use App\Repository\PostRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentPostDeletedNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(PostDeletedNotificationMessage $message): void
    {
        $post = $this->repository->find($message->postId);

        if (!$post) {
            throw new UnrecoverableMessageHandlingException('Post not found');
        }

        $this->manager->sendDeleted($post);
    }
}
