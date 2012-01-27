<?php

/*
 * This file is part of the AnalyticsTrackerBundle.
 *
 * (c) 2011 Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jirafe\Bundle\AnalyticsTrackerBundle;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * Listener an analytics tracker to the response
 *
 * The onKernelResponse method must be connected to the kernel.response event.
 *
 * The tracker is only injected on well-formed HTML (with a proper </body> tag).
 * This means that the tracker is never included in sub-requests or ESI requests.
 */
class AnalyticsTrackerListener
{
    protected $templating;
    protected $template;
    protected $params;

    /**
     * Constructor
     *
     * @param  TwigEngine $templating  A TwigEngine instance
     * @param  string     $template    The template of the tracker
     * @param  array      $params      An array of parameters for the template
     */
    public function __construct(TwigEngine $templating, $template, array $params = array())
    {
        $this->templating  = $templating;
        $this->template    = $template;
        $this->params      = $params;
    }

    /**
     * Defines the template
     *
     * @param  string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Defines the parameters
     *
     * @param  array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Defines a parameter
     *
     * @param  string $name
     * @param  mixid  $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Listen to the kernel.response event
     *
     * @param  FilterResponseEvent A FilterResponseEvent instance
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ('3' === substr($response->getStatusCode(), 0, 1)
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
            || $request->isXmlHttpRequest()
        ) {
            return;
        }

        $this->injectTracker($response);
    }

    /**
     * Injects the Analytics tracker into the given Response
     *
     * @param Response $response A Response instance
     */
    protected function injectTracker(Response $response)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction = 'strripos';
            $substrFunction = 'substr';
        }

        $content = $response->getContent();

        $pos = $posrFunction($content, '</body>');
        if (false !== $pos) {
            $toolbar = $this->templating->render($this->template, $this->params);
            $toolbar = "\n" . str_replace("\n", '', $toolbar) . "\n";

            $content = $substrFunction($content, 0, $pos).$toolbar.$substrFunction($content, $pos);
            $response->setContent($content);
        }
    }
}
