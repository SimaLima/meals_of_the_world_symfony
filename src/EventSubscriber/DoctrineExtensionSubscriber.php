<?php


namespace App\EventSubscriber;

use Gedmo\Loggable\LoggableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Translatable\TranslatableListener;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoctrineExtensionSubscriber implements EventSubscriberInterface
{
    /**
     * @var SoftDeleteableListener
     */
    private $softDeletableListener;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TranslatableListener
     */
    private $translatableListener;

    /**
     * @var LoggableListener
     */
    private $loggableListener;


    public function __construct(
        SoftDeleteableListener $softDeletableListener,
        TokenStorageInterface $tokenStorage,
        TranslatableListener $translatableListener,
        LoggableListener $loggableListener
    ) {
        $this->softDeletableListener = $softDeletableListener;
        $this->tokenStorage = $tokenStorage;
        $this->translatableListener = $translatableListener;
        $this->loggableListener = $loggableListener;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::FINISH_REQUEST => 'onLateKernelRequest'
        ];
    }

    public function onKernelRequest(): void
    {
        if ($this->tokenStorage !== null &&
            $this->tokenStorage->getToken() !== null &&
            $this->tokenStorage->getToken()->isAuthenticated() === true
        ) {
            //$this->softDeletableListener->
        }
    }

    public function onLateKernelRequest(FinishRequestEvent $event): void
    {
        $this->translatableListener->setTranslatableLocale($event->getRequest()->getLocale());
    }

}