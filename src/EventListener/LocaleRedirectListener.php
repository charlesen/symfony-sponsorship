<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\LocaleSwitcher;

final class LocaleRedirectListener
{
    public function __construct(
        private readonly Security $security,
        private LocaleSwitcher $localeSwitcher,
    ) {}

    #[AsEventListener(event: 'kernel.request', priority: 255)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();
        $supportedLocales = ['fr', 'en'];

        // Ne pas rediriger les assets ou outils de debug
        if (preg_match('#^/(build|_wdt|_profiler|assets|favicon|robots|logout)($|/)#', $path)) {
            return;
        }

        // Si la locale est déjà dans l'URL et supportée, ne rien faire
        $segments = explode('/', ltrim($path, '/'));
        if (isset($segments[0]) && in_array($segments[0], $supportedLocales)) {
            return;
        }

        // Détecte la locale cible : user connecté > navigateur > fr
        /** @var User $user */
        $user = $this->security->getUser();
        $locale = 'fr';
        if ($user && method_exists($user, 'getLocale')) {
            $userLocale = $user->getLocale();
            if ($userLocale && in_array($userLocale, $supportedLocales)) {
                $locale = $userLocale;
            }
        } else {
            $preferred = $request->getPreferredLanguage($supportedLocales);
            if ($preferred && in_array($preferred, $supportedLocales)) {
                $locale = $preferred;
            }
        }

        // Redirige vers /<locale>/<path> (en supprimant un éventuel / initial)
        $redirectPath = ltrim($path, '/');
        $redirectUrl = '/' . $locale . ($redirectPath ? '/' . $redirectPath : '');

        // Set locale for translation
        $this->localeSwitcher->setLocale($locale);

        $event->setResponse(new RedirectResponse($redirectUrl));
    }
}
