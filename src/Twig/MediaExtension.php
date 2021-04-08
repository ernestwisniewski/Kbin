<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use App\Twig\Runtime\MediaRuntime;
use Twig\TwigFunction;

final class MediaExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [MediaRuntime::class, 'getPublicPath']),
        ];
    }

}
