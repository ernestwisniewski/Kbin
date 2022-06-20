<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\ContactDto;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactManager
{
    public function __construct(
        private SettingsManager $settings,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private RateLimiterFactory $contactLimiter
    ) {
    }

    public function send(ContactDto $dto)
    {
        $limiter = $this->contactLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@mg.karab.in', $this->settings->get('KBIN_DOMAIN')))
            ->to($this->settings->get('KBIN_CONTACT_EMAIL'))
            ->subject($this->translator->trans('contact').' - '.$this->settings->get('KBIN_DOMAIN'))
            ->htmlTemplate('_email/contact.html.twig')
            ->context([
                'name'        => $dto->name,
                'senderEmail' => $dto->email,
                'message'     => $dto->message,
            ]);

        $this->mailer->send($email);
    }
}
