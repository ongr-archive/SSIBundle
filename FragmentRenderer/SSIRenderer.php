<?php
namespace Crunch\Bundle\SSIBundle\FragmentRenderer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;
use Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer;

class SSIRenderer extends RoutableFragmentRenderer
{
    /**
     * Fallback inline fragment renderer
     *
     * @var \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer
     */
    private $defaultStrategy;

    /**
     * URI Signer
     *
     * NGinx in conjunction with PHP-FPM doesn't replace the Remote-IP by default,
     * thus from the application point of view it looks like the request
     * always come from the client itself.
     *
     * @var \Symfony\Component\HttpKernel\UriSigner
     */
    private $signer;
    protected $useHeader = false;
    protected $substitute = false;

    public function __construct(UriSigner $signer, InlineFragmentRenderer $defaultStrategy)
    {
        $this->defaultStrategy = $defaultStrategy;
        $this->signer = $signer;
    }

    public function render($uri, Request $request, array $options = array())
    {
        if ($this->substitute || $this->useHeader && \strpos($request->headers->get('Surrogate-Capability', ''), 'SSI/1.0')) {
            return $this->defaultStrategy->render($uri, $request, $options);
        }

        if ($uri instanceof ControllerReference) {
            $uri = $this->generateFragmentUri($uri, $request);
        }

        $uri = $this->signer->sign($uri);

        if (!\strncmp($uri, $request->getSchemeAndHttpHost(), \strlen($request->getSchemeAndHttpHost()))) {
            $uri = \substr($uri, \strlen($request->getSchemeAndHttpHost()));
        }

        return new Response(\sprintf('<!--#include virtual="%s" -->', $uri));
    }

    public function getName()
    {
        return 'ssi';
    }

    /**
     * Whether or not the renderer should respect the Surrogate-Capability header
     *
     * If set to 'true' the fragment renderer falls back to the default
     * rendering strategy ("inline"), when there is no appropriate
     * `Surrogate-Capability`-header with value `xyz=SSI/1.0`
     *
     * The default is to always render the SSI-tags regardless whether or
     * not the header exists.
     *
     * @param boolean $respectHeader
     */
    public function setUseHeader ($respectHeader)
    {
        $this->useHeader = $respectHeader;
    }

    /**
     * Whether or not the content should be rendered inline always
     *
     * This technically disables the rendering of SSI-tags completely and
     * always utilizes the default render
     *
     * @param boolean $substitute
     */
    public function setInline ($substitute)
    {
        $this->substitute = $substitute;
    }
}
