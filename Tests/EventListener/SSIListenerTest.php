<?php
namespace Crunch\SSIBundle\Tests\EventListener;

use Crunch\Bundle\SSIBundle\EventListener\SSIListener;
use \PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class SSIListenerTest extends TestCase
{
    public function testUpdateHeaderOnMasterRequest ()
    {
        $listener = new SSIListener;
        $listener->setUseHeader(true);
        $event = $this->mockFilterResponseEvent();
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $headerBag = $this->mockResponseHeaderBag();
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue((object) array('headers' => $headerBag)));

        $headerBag->expects($this->any())
            ->method('get')
            ->with('Surrogate-Control')
            ->will($this->returnValue(null));
        $headerBag->expects($this->once())
            ->method('set')
            ->with('Surrogate-Control', 'content=SSI/1.0');

        $listener->updateHeader($event);
    }

    public function testUpdateHeaderWithExistingValue ()
    {
        $listener = new SSIListener;
        $listener->setUseHeader(true);
        $event = $this->mockFilterResponseEvent();
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $headerBag = $this->mockResponseHeaderBag();
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue((object) array('headers' => $headerBag)));

        $headerBag->expects($this->any())
            ->method('get')
            ->with('Surrogate-Control')
            ->will($this->returnValue('foo'));
        $headerBag->expects($this->once())
            ->method('set')
            ->with('Surrogate-Control', 'foo,content=SSI/1.0');

        $listener->updateHeader($event);
    }

    public function testDoNotUpdateHeaderOnMasterRequest ()
    {
        $listener = new SSIListener;
        $listener->setUseHeader(false);
        $event = $this->mockFilterResponseEvent();
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $headerBag = $this->mockResponseHeaderBag();
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue((object) array('headers' => $headerBag)));

        $headerBag->expects($this->any())
            ->method('get')
            ->with('Surrogate-Control')
            ->will($this->returnArgument(null));
        $headerBag->expects($this->never())
            ->method('set');

        $listener->updateHeader($event);
    }

    public function testDoNotUpdateHeaderOnSubRequest ()
    {
        $listener = new SSIListener;
        $listener->setUseHeader(true);
        $event = $this->mockFilterResponseEvent();
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));
        $headerBag = $this->mockResponseHeaderBag();
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue((object) array('headers' => $headerBag)));

        $headerBag->expects($this->any())
            ->method('get')
            ->with('Surrogate-Control')
            ->will($this->returnArgument(null));
        $headerBag->expects($this->never())
            ->method('set');

        $listener->updateHeader($event);
    }

    /**
     * @return FilterResponseEvent|Mock
     */
    private function mockFilterResponseEvent ()
    {
        return $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterResponseEvent',
            array('getRequestType', 'getResponse'),
            array(),
            '',
            false
        );
    }

    /**
     * @return ResponseHeaderBag|Mock
     */
    private function mockResponseHeaderBag ()
    {
        return $this->getMock(
            'Symfony\Component\HttpFoundation\ResponseHeaderBag',
            array('get', 'set'),
            array(),
            '',
            false
        );
    }
}
