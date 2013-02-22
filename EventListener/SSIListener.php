<?php
namespace Crunch\Bundle\SSIBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class SSIListener implements EventSubscriberInterface {
    /**
     * Whether or not the `Surrogate-Control`-header should be set
     *
     * Defaults to `false`
     *
     * @var bool
     */
    protected $useHeader = false;

    /**
     * Whether or not the `Surrogate-Control`-header should be set
     *
     * @param bool $useHeader
     */
    public function setUseHeader ($useHeader) { $this->useHeader = $useHeader; }

    /**
     * Event-Methodname-map of events to listen for
     *
     * @return array
     */
    public static function getSubscribedEvents () {
        return array(
            KernelEvents::RESPONSE => 'updateHeader',
        );
    }

    /**
     * If $useHeader is set adds SSI-header to the response
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function updateHeader(FilterResponseEvent $event) {
        if ($this->useHeader && $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $event->getResponse()->headers->set(
                'Surrogate-Control',
                trim($event->getResponse()->headers->get('Surrogate-Control') . ',content=SSI/1.0', ', ')
            );
        }
    }
}
