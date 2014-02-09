<?php

/**
 * Avisota newsletter and mailing system
 * Copyright (C) 2013 Tristan Lins
 *
 * PHP version 5
 *
 * @copyright  MEN AT WORK 2013
 * @package    avisota
 * @license    LGPL-3.0+
 * @filesource
 */

namespace Avisota\Contao\Entity;

use Avisota\Contao\Entity\MessageHistory;
use Avisota\Contao\Entity\MessageHistoryDetails;
use Avisota\Contao\Core\Event\PostSendImmediateEvent;
use Contao\Doctrine\ORM\Entity;
use Contao\Doctrine\ORM\EntityHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'avisota.post-send-immediate' => 'captureSendImmediate',
		);
	}

	public function captureSendImmediate(PostSendImmediateEvent $event)
	{
		$entityManager     = EntityHelper::getEntityManager();
		$historyRepository = $entityManager->getRepository('Avisota\Contao\Entity\MessageHistory');

		// get the history
		$loop = $event->getLoop();
		if ($loop) {
			$history = $historyRepository->findOneBy(array('loop' => $loop));
		}

		//create a new history if none was found
		if (!$history) {
			$history = new MessageHistory();
			$history->setMessage($event->getMessage());
			$history->setLoop($event->getLoop());
		}

		$history->setMailCount($history->getMailCount() + $event->getCount());

		$entityManager->persist($history);
	}
}
