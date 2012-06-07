<?php

namespace Jirafe\Bundle\AnalyticsTrackerBundle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AnalyticsTrackerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getInjectTrackerTests
     */
    public function testInjectTracker($content, $expected)
    {
        $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
        $m = new \ReflectionMethod($listener, 'injectTracker');
        $m->setAccessible(true);

        $response = new Response($content);

        $m->invoke($listener, $response);
        $this->assertEquals($expected, $response->getContent());
    }

    public function getInjectTrackerTests()
    {
        return array(
            array('<html><head></head><body></body></html>', "<html><head></head><body>\nTRACKER\n</body></html>"),
            array('<html>
            <head></head>
            <body>
            <textarea><html><head></head><body></body></html></textarea>
            </body>
            </html>', "<html>
            <head></head>
            <body>
            <textarea><html><head></head><body></body></html></textarea>
            \nTRACKER\n</body>
            </html>"),
        );
    }

    public function testTrackerIsInjected()
    {
        $response = new Response('<html><head></head><body></body></html>');

        $event = new FilterResponseEvent($this->getKernelMock(), $this->getRequestMock(), HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
        $listener->onKernelResponse($event);

        $this->assertEquals("<html><head></head><body>\nTRACKER\n</body></html>", $response->getContent());
    }

    /**
     * @depends testTrackerIsInjected
     */
    public function testTrackerIsNotInjectedOnRedirection()
    {
        foreach (array(301, 302) as $statusCode) {
            $response = new Response('<html><head></head><body></body></html>', $statusCode);
            $event = new FilterResponseEvent($this->getKernelMock(), $this->getRequestMock(), HttpKernelInterface::MASTER_REQUEST, $response);

            $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
            $listener->onKernelResponse($event);

            $this->assertEquals('<html><head></head><body></body></html>', $response->getContent());
        }
    }

    /**
     * @depends testTrackerIsInjected
     */
    public function testTrackerIsNotInjectedWhenOnSubRequest()
    {
        $response = new Response('<html><head></head><body></body></html>');

        $event = new FilterResponseEvent($this->getKernelMock(), $this->getRequestMock(), HttpKernelInterface::SUB_REQUEST, $response);

        $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
        $listener->onKernelResponse($event);

        $this->assertEquals('<html><head></head><body></body></html>', $response->getContent());
    }

    /**
     * @depends testTrackerIsInjected
     */
    public function testTrackerIsNotInjectedOnUncompleteHtmlResponses()
    {
        $response = new Response('<div>Some content</div>');

        $event = new FilterResponseEvent($this->getKernelMock(), $this->getRequestMock(), HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
        $listener->onKernelResponse($event);

        $this->assertEquals('<div>Some content</div>', $response->getContent());
    }

    /**
     * @depends testTrackerIsInjected
     */
    public function testTrackerIsNotInjectedOnXmlHttpRequests()
    {
        $response = new Response('<html><head></head><body></body></html>');

        $event = new FilterResponseEvent($this->getKernelMock(), $this->getRequestMock(true), HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
        $listener->onKernelResponse($event);

        $this->assertEquals('<html><head></head><body></body></html>', $response->getContent());
    }

    /**
     * @depends testTrackerIsInjected
     */
    public function testTrackerIsNotInjectedOnNonHtmlRequests()
    {
        $response = new Response('<html><head></head><body></body></html>');

        $event = new FilterResponseEvent($this->getKernelMock(), $this->getRequestMock(false, 'json'), HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new AnalyticsTrackerListener($this->getTemplatingMock(), '');
        $listener->onKernelResponse($event);

        $this->assertEquals('<html><head></head><body></body></html>', $response->getContent());
    }

    protected function getRequestMock($isXmlHttpRequest = false, $requestFormat = 'html')
    {
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session', array(), array(), '', false);
        $request = $this->getMock(
            'Symfony\Component\HttpFoundation\Request',
            array('getSession', 'isXmlHttpRequest', 'getRequestFormat'),
            array(), '', false
        );
        $request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue($isXmlHttpRequest));
        $request->expects($this->any())
            ->method('getRequestFormat')
            ->will($this->returnValue($requestFormat));
        $request->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));

        return $request;
    }

    protected function getTemplatingMock()
    {
        $templating = $this->getMock('Symfony\Bundle\TwigBundle\TwigEngine', array(), array(), '', false);
        $templating->expects($this->any())
            ->method('render')
            ->will($this->returnValue('TRACKER'));

        return $templating;
    }

    protected function getKernelMock()
    {
        return $this->getMock('Symfony\Component\HttpKernel\Kernel', array(), array(), '', false);
    }
}
