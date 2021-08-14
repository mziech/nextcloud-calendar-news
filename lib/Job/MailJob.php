<?php
/**
 * @copyright Copyright (c) 2020-2021 Marco Ziech <marco+nc@ziech.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\CalendarNews\Job;

use OCA\CalendarNews\Service\ScheduleService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;

class MailJob extends TimedJob {

    /**
     * @var ScheduleService
     */
    private $scheduleService;
    /**
     * @var LoggerInterface
     */
    private $logger;

    function __construct(
        ITimeFactory $time,
        ScheduleService $scheduleService,
        LoggerInterface $logger
    ) {
        parent::__construct($time);
        parent::setInterval(20 * 60 /* s */);
        $this->scheduleService = $scheduleService;
        $this->logger = $logger;
    }

    protected function run($argument) {
        $next = $this->scheduleService->getNextExecutionTime();
        if ($next == null) {
            return;
        }
        $now = new \DateTime();

        if ($next < $now) {
            $this->logger->info("Sending newsletter is due: {$next->format("c")} < {$now->format("c")}");
            $this->scheduleService->setLastExecutionTime($now);
            $this->scheduleService->sendNow();
        }
    }

}
