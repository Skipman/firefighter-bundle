<?php

/*
 * This file is part of Contao Firefighter Bundle.
 * 
 * (c) Ronald Boda 2022 <info@coboda.at>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/skipman/contao-firefighter-bundle
 */

 namespace Skipman\ContaoFirefighterBundle\EventSubscriber;
 
 use Contao\CoreBundle\Routing\ScopeMatcher;
 use Symfony\Component\EventDispatcher\EventSubscriberInterface;
 use Symfony\Component\HttpKernel\Event\RequestEvent;
 use Symfony\Component\HttpKernel\KernelEvents;
 
 class KernelRequestSubscriber implements EventSubscriberInterface
 {
     protected $scopeMatcher;
 
     public function __construct(ScopeMatcher $scopeMatcher)
     {
         $this->scopeMatcher = $scopeMatcher;
     }
 
     public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
 
     public function onKernelRequest(RequestEvent $e): void
     {
         $request = $e->getRequest();
 
         if ($this->scopeMatcher->isBackendRequest($request)) {
            $GLOBALS['TL_CSS'][] = 'bundles/contaofirefighter/backend.css|static';
         }
     }
 }
 
