<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Service\SettingsManager;
use JetBrains\PhpStorm\Pure;
use Twig\Extension\RuntimeExtensionInterface;

class SettingsRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private SettingsManager $settings
    ) {
    }

    #[Pure] public function kbinDomain(): string
    {
        return $this->settings->get('KBIN_DOMAIN');
    }

    #[Pure] public function kbinTitle(): string
    {
        return $this->settings->get('KBIN_TITLE');
    }

    #[Pure] public function kbinDescription(): string
    {
        return $this->settings->get('KBIN_DESCRIPTION');
    }

    #[Pure] public function kbinKeywords(): string
    {
        return $this->settings->get('KBIN_KEYWORDS');
    }

    #[Pure] public function kbinMarkdownHowtoUrl(): string
    {
        return $this->settings->get('KBIN_MARKDOWN_HOWTO_URL');
    }

    #[Pure] public function kbinJsEnabled(): bool
    {
        return $this->settings->get('KBIN_JS_ENABLED');
    }
}
