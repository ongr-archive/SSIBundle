<?php
namespace Crunch\Bundle\SSIBundle\KernelProxy;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class SSIProxy implements HttpKernelInterface, TerminableInterface
{
    private $kernel;
    private $options = array(
        'pass_through' => false
    );

    public function __construct (HttpKernelInterface $kernel, array $options = array())
    {
        $this->kernel = $kernel;
        $this->options = array_merge($this->options, $options);
    }

    public function handle (Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (!$this->serverHasCapability($request)) {
            $this->addCapabilityHeader($request);
        }

        $response = $this->kernel->handle($request, $type, $catch);

        if (!$this->options['pass_through'] && $this->responseHasControlHeader($response)) {
            $this->parse($request, $response);
        }

        return $response;
    }

    public function terminate (Request $request, Response $response)
    {
        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }
    }

    private function parse (Request $request, Response $response)
    {
        $content = preg_replace_callback(
            '#<!--\#include\s+(.*?)\s*-->#',
            new IncludeHandler($this, $request, $response),
            $response->getContent()
        );
        $response->setContent($content);

        $this->removeControlHeader($response);
    }

    private function serverHasCapability (Request $request)
    {
        return (bool) strpos($request->headers->get('Surrogate-Capability', ''), 'SSI/1.0');
    }

    private function addCapabilityHeader (Request $request)
    {
        $current = $request->headers->get('Surrogate-Capability');
        $request->headers->set('Surrogate-Capability', ($current ? $current . ', ' : '') . sprintf('symfony2="%s"', 'SSI/1.0'));
    }

    private function responseHasControlHeader (Response $response)
    {
        return (bool) strpos($response->headers->get('Surrogate-Control', ''), 'SSI/1.0');
    }

    private function removeControlHeader (Response $response)
    {
        $current = $response->headers->get('Surrogate-Control', '');
        $new = array_filter(
            explode(',', $current),
            function ($control) {
                return $control && strpos($control, 'SSI/1.0');
            }
        );
        if ($new) {
            $response->headers->set('Surrogate-Control', implode(', ', $new));
        } else {
            $response->headers->remove('Surrogate-Control');
        }
    }
}
