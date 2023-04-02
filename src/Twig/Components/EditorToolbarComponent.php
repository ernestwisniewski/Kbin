<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('editor_toolbar')]
final class EditorToolbarComponent
{
    public string $id;
}
