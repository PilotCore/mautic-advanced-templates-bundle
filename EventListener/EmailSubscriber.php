<?php

namespace MauticPlugin\MauticAdvancedTemplatesBundle\EventListener;
use Mautic\CampaignBundle\Entity\Lead;
use Mautic\CoreBundle\EventListener\CommonStatsSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event as Events;
use Mautic\EmailBundle\Helper\PlainTextHelper;
use Mautic\CoreBundle\Exception as MauticException;
use MauticPlugin\MauticAdvancedTemplatesBundle\Helper\TemplateProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber extends CommonStatsSubscriber
{
     /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TokenHelper $tokenHelper ;
     */
    protected $templateProcessor;


    /**
     * EmailSubscriber constructor.
     *
     * @param TokenHelper $tokenHelper
     */
    public function __construct(TemplateProcessor $templateProcessor, LoggerInterface $logger)
    {
     	$this->templateProcessor = $templateProcessor;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_SEND => ['onEmailGenerate', 300],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailGenerate', 0],
        ];
    }

    /**
     * Search and replace tokens with content
     *
     * @param Events\EmailSendEvent $event
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function onEmailGenerate(Events\EmailSendEvent $event)
    {
        $this->logger->info('onEmailGenerate MauticAdvancedTemplatesBundle\EmailSubscriber');

        if ($event->getEmail()) {
            $subject = $event->getEmail()->getSubject();
            $content = $event->getEmail()->getCustomHtml();
        }else{
            $subject = $event->getSubject();
            $content = $event->getContent();
        }

        $subject = $this->templateProcessor->processTemplate($subject,  $event->getLead());
        $event->setSubject($subject);

        $content = $this->templateProcessor->processTemplate($content,  $event->getLead());
        $event->setContent($content);


        if ( empty( trim($event->getPlainText()) ) ) {
            $event->setPlainText( (new PlainTextHelper($content))->getText() );
        }
    }
}
