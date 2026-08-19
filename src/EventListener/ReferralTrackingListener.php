<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ReferralTrackingListener
{
    public const COOKIE_NAME = 'ref_code';
    public const SESSION_KEY = '_referral_code';
    public const COOKIE_LIFETIME = 2592000; // 30 days in seconds

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $refParam = $request->query->get('ref') ?? $request->query->get('r');

        if ($refParam && is_string($refParam) && preg_match('/^[a-zA-Z0-9_-]{4,32}$/', $refParam)) {
            $sanitizedRef = trim($refParam);

            // Store in session if session is started
            if ($request->hasSession()) {
                $request->getSession()->set(self::SESSION_KEY, $sanitizedRef);
            }

            // Flag for response listener to set cookie
            $request->attributes->set(self::SESSION_KEY, $sanitizedRef);
        }
    }

    #[AsEventListener(event: KernelEvents::RESPONSE, priority: 0)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $refCode = $request->attributes->get(self::SESSION_KEY);

        if ($refCode && is_string($refCode)) {
            $response = $event->getResponse();
            
            $cookie = Cookie::create(
                name: self::COOKIE_NAME,
                value: $refCode,
                expire: time() + self::COOKIE_LIFETIME,
                path: '/',
                domain: null,
                secure: $request->isSecure(),
                httpOnly: true,
                raw: false,
                sameSite: Cookie::SAMESITE_LAX
            );

            $response->headers->setCookie($cookie);
        }
    }
}
