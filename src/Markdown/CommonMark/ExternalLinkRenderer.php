<?php

namespace App\Markdown\CommonMark;

use Exception;
use InvalidArgumentException;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Util\ConfigurationInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\HtmlElement;
use App\Service\ImageManager;
use App\Utils\Embed;
use function get_class;

final class ExternalLinkRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function __construct(private Embed $embed)
    {
    }

    public function render(
        AbstractInline $inline,
        ElementRendererInterface $htmlRenderer
    ): HtmlElement {
        if (!$inline instanceof Link) {
            throw new InvalidArgumentException(
                sprintf(
                    'Incompatible inline type: %s',
                    get_class($inline)
                )
            );
        }

        $url = $title = $inline->getUrl();

        if ($inline->firstChild() instanceof Text) {
            $title = $htmlRenderer->renderInline($inline->firstChild());
        }

        try {
            $embed = $this->embed->fetch($url)->getHtml();
        } catch (Exception $e) {
            $embed = null;
        }

        if (ImageManager::isImageUrl($url) || $embed) {
            return EmbedElement::buildEmbed($url, $title);
        }

        return new HtmlElement('a', ['href' => $url] + ['class' => 'kbin-media-link'], $title);
    }

    public function setConfiguration(
        ConfigurationInterface $configuration
    ) {
        $this->config = $configuration;
    }
}
