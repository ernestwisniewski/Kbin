<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\EntryComment;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('entry_comments_nested')]
class EntryCommentsNestedComponent
{
    public EntryComment $comment;
    public int $level;

    public function __construct(private Environment $twig, private CacheInterface $cache, private Security $security)
    {
    }

    public function getHtml(): string
    {
        $comment = $this->comment->root?->getId() ?? $this->comment->getId();
        $user    = $this->security->getUser()?->getId();

        return $this->cache->get('nested_entry_comment_'.$comment.'_'.$user, function (ItemInterface $item) use ($comment, $user) {
            $item->expiresAfter(3600);
            $item->tag(['entry_comments_user_'.$user]);
            $item->tag(['entry_comment_'.$comment]);

            return $this->twig->render(
                'entry/comment/_nested.html.twig',
                [
                    'comment' => $this->comment,
                    'level'   => $this->level,
                ]
            );
        });
    }
}
