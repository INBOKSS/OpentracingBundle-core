<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

final class FinishRootSpanSubscriber implements EventSubscriberInterface
{
    private Tracing $tracing;
    private Persistence $persistence;

    public function __construct(Tracing $tracing, Persistence $persistence)
    {
        $this->tracing = $tracing;
        $this->persistence = $persistence;
    }

    /**
     * @return array<string,array<int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.finish_request' => ['onFinishRequest', -16],
            'kernel.terminate' => ['onTerminate', -16],
        ];
    }

    public function onFinishRequest(KernelEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->tracing->finishActiveSpan();
    }

    public function onTerminate(KernelEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->persistence->flush();
    }
}
