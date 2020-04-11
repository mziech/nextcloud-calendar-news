<?php
/**
 * @copyright Copyright (c) 2020 Marco Ziech <marco+nc@ziech.net>
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
namespace OCA\CalendarNews\Service;


use OCP\IConfig;
use OCP\ILogger;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property\ICalendar\Duration;

class ScheduleService {

    /**
     * @var IConfig
     */
    private $config;
    private $AppName;
    /**
     * @var NewsletterService
     */
    private $newsletterService;
    /**
     * @var ILogger
     */
    private $logger;

    function __construct($AppName, IConfig $config, NewsletterService $newsletterService, ILogger $logger) {
        $this->config = $config;
        $this->AppName = $AppName;
        $this->newsletterService = $newsletterService;
        $this->logger = $logger;
    }


    public function load() {
        $str = $this->config->getAppValue($this->AppName, "schedule");
        if ($str != "") {
            return json_decode($str, true);
        }

        return [
            "schedule" => [
                "subject" => "",
                "emails" => [],
                "repeatInterval" => "off",
            ]
        ];
    }

    public function save($schedule) {
        $this->config->setAppValue($this->AppName, "schedule", json_encode($schedule));
    }

    public function sendNow() {
        $this->logger->info("Sending newsletter now");

        $schedule = $this->load();
        $this->newsletterService->send($schedule["schedule"]["emails"], $schedule["schedule"]["subject"]);
    }

    public function getLastExecutionTime() {
        $str = $this->config->getAppValue($this->AppName, "schedule.lastExecutionTime");
        if ($str != "") {
            return \DateTime::createFromFormat(\DateTime::ISO8601, $str);
        }
        return null;
    }

    public function setLastExecutionTime(\DateTime $t) {
        $this->config->setAppValue($this->AppName, "schedule.lastExecutionTime", $t->format(\DateTime::ISO8601));
    }

    public function getNextExecutionTime() {
        $t = $this->getLastExecutionTime();
        if ($t == null) {
            $t = new \DateTime();
            $t->modify("yesterday");
        }
        $t->setTimezone(new \DateTimeZone("Europe/Berlin"));
        $schedule = $this->load();
        $rt = \DateTime::createFromFormat("Y-m-d\\TH:i:s.uO", $schedule["schedule"]["repeatTime"]);
        switch ($schedule["schedule"]["repeatInterval"]) {
            case "weekly":
                $t->modify("next " . $schedule["schedule"]["repeatWeekday"]);
                $t->modify($rt->format("H:i"));
                return $t;
            case "off":
            default:
                return null;
        }
    }

}