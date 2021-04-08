<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Entry;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\EntryEmbedMessage;
use App\Repository\ImageRepository;
use App\Repository\EntryRepository;
use App\Service\ImageManager;
use App\Utils\Embed;

class AttachEntryEmbedHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryRepository $entryRepository,
        private Embed $embed,
        private ImageManager $imageManager,
        private ImageRepository $imageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(EntryEmbedMessage $entryCreatedMessage)
    {
        $entry = $this->entryRepository->find($entryCreatedMessage->getEntryId());
        if (!$entry || !$entry->url) {
            return;
        }

        $embed = $this->embed->fetch($entry->url);

        $cover    = null;
        $tempFile = null;
        if ($embed->getImage()) {
            $tempFile = $this->fetchImage($embed->getImage());
        } elseif ($embed->isImageUrl()) {
            $tempFile = $this->fetchImage($entry->url);
        }

        if ($tempFile) {
            $cover = $this->imageRepository->findOrCreateFromPath($tempFile);
        }

        $html    = $embed->getHtml();
        $type    = $embed->getType();
        $isImage = $embed->isImageUrl();

        if (!$html && !$cover && !$isImage) {
            return;
        }

        $this->entityManager->transactional(
            static function () use ($entry, $cover, $html, $isImage, $type): void {
                $entry->type     = $type;
                $entry->hasEmbed = $html || $isImage;
                $entry->image    = $cover;
            }
        );
    }

    private function fetchImage(string $url): ?string
    {
        return $this->imageManager->download($url);
    }
}
