<?php

/*
 * This file is part of Contao Firefighter Bundle.
 * 
 * (c) Ronald Boda 2022 <info@coboda.at>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/skipman/firefighter-bundle
 */

 namespace Skipman\FirefighterBundle\EventListener;
 
 use Contao\BackendUser;
 use Contao\CoreBundle\ServiceAnnotation\Hook;
 use Contao\CoreBundle\ServiceAnnotation\Callback;
 use Symfony\Component\HttpKernel\Event\RequestEvent;
 use Symfony\Component\HttpKernel\HttpKernelInterface;
 
 class BackendAssetsListener
 {
     public function onKernelRequest(RequestEvent $event): void
     {
         $request = $event->getRequest();
 
         // Check if request is for the backend
         if ($request->get('_scope') === 'backend') {
             $GLOBALS['TL_CSS'][] = 'bundles/skipmanfirefighter/css/backend_custom.css|static';
         }
     }
 }
 
