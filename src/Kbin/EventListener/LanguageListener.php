<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LanguageListener
{
    public function __construct(public string $lang)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->cookies->has('kbin_lang')) {
            $request->setLocale($request->cookies->get('kbin_lang'));

            return;
        }

        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $request->setLocale($this->lang);

            return;
        }

        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        $request->setLocale($lang);
        $request->setDefaultLocale($lang);
    }
}
