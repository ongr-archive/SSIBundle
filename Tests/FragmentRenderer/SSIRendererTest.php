<?php

namespace Crunch\SSIBundle\Tests\FragmentRenderer;

use Crunch\Bundle\SSIBundle\FragmentRenderer\SSIRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\UriSigner;

class SSIRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testRenderDefaultStrategy()
     *
     * @return array
     */
    public function getTestRenderDefaultStrategyData()
    {
        $out = array();

        // case #0: request with Surrogate capability
        $request = new Request();
        $request->headers->add(
            array(
                "Surrogate-Capability" => "abc='SSI/1.0'",
            )
        );
        $defaultRenderer = $this->getRendererMock(0);

        $out[] = array($request, $defaultRenderer);

        // case #1: empty request - no  surrogate capability
        $request = new Request();
        $defaultRenderer = $this->getRendererMock(1);

        $out[] = array($request, $defaultRenderer);

        return $out;
    }

    /**
     * @dataProvider    getTestRenderDefaultStrategyData()
     *
     * @param $request
     * @param $defaultRenderer
     */
    public function testRenderDefaultStrategy($request, $defaultRenderer)
    {
        $signer = new UriSigner('');

        $renderer = new SSIRenderer($signer, $defaultRenderer);
        $renderer->setUseHeader(true);
        $renderer->render('', $request);
    }

    /**
     * Returns mock for FragmentRendererInterface which render method is
     * expected to be called exactly $callCount times
     *
     * @return  FragmentRendererInterface
     */
    protected function getRendererMock($callCount)
    {
        $mock = $this->getMock(
            'Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface'
        );

        $mock
            ->expects($this->exactly($callCount))
            ->method('render')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything()
            );

        return $mock;
    }
}
