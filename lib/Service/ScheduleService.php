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
namespace OCA\CalendarNews\Service;


use OCP\IAppConfig;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class ScheduleService {

    private IAppConfig $config;
    private string $AppName;
    private NewsletterService $newsletterService;
    private LoggerInterface $logger;

    function __construct(
        string $AppName,
        IAppConfig $config,
        NewsletterService $newsletterService,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->AppName = $AppName;
        $this->newsletterService = $newsletterService;
        $this->logger = $logger;
    }

    public function load() {
        $str = $this->config->getValueString($this->AppName, "schedule");
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
        $this->config->setValueString($this->AppName, "schedule", json_encode($schedule));
    }

    public function sendNow() {
        $this->logger->info("Sending newsletter now");

        $schedule = $this->load();
        $this->newsletterService->send($schedule["schedule"]["emails"], $schedule["schedule"]["subject"]);
    }

    public function getLastExecutionTime() {
        $str = $this->config->getValueString($this->AppName, "schedule.lastExecutionTime");
        if ($str != "") {
            return \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $str);
        }
        return null;
    }

    public function setLastExecutionTime(\DateTime $t) {
        $this->config->setValueString($this->AppName, "schedule.lastExecutionTime", $t->format(\DateTimeInterface::ISO8601));
    }

    public function removeLastExecutionTime() {
        $this->config->setValueString($this->AppName, "schedule.lastExecutionTime", null);
    }

    public function getNextExecutionTime($schedule=null) {
        if ($schedule == null) {
            $schedule = $this->load()["schedule"];
        }
        $t = $this->getLastExecutionTime();
        if ($t == null) {
            $t = new \DateTime();
            $t->modify("yesterday");
        }
        $t->setTimezone(new \DateTimeZone("Europe/Berlin"));
        $rt = \DateTime::createFromFormat("Y-m-d\\TH:i:s.uO", $schedule["repeatTime"]);
        switch ($schedule["repeatInterval"]) {
            case "yearly":
                if ($schedule["skip"]) {
                    $t->modify($schedule["skip"] . " years");
                }
                $t->modify("1 year");
                $t->modify($schedule["repeatWeek"] . " " . $schedule["repeatWeekday"]
                    . " of " . $schedule["repeatMonth"]);
                break;
            case "yearly_dom":
                if ($schedule["skip"]) {
                    $t->modify($schedule["skip"] . " years");
                }
                $t->modify("1 year");
                $t->modify(($schedule["repeatDayOfMonth"] > 0 ? "first" : "last")
                    . " day of " . $schedule["repeatMonth"]);
                if ($schedule["repeatDayOfMonth"] !== 0) {
                    $t->modify(($schedule["repeatDayOfMonth"] - 1) . " days");
                }
                break;
            case "monthly":
                if ($schedule["skip"]) {
                    $t->modify($schedule["skip"] . " months");
                }
                $t->modify($schedule["repeatWeek"] . " " . $schedule["repeatWeekday"]
                    . " of next month");
                break;
            case "monthly_dom":
                if ($schedule["skip"]) {
                    $t->modify($schedule["skip"] . " months");
                }
                $t->modify(($schedule["repeatDayOfMonth"] > 0 ? "first" : "last") . " day of next month");
                if ($schedule["repeatDayOfMonth"] !== 0) {
                    $t->modify(($schedule["repeatDayOfMonth"] - 1) . " days");
                }
                break;
            case "weekly":
                if ($schedule["skip"]) {
                    $t->modify($schedule["skip"] . " weeks");
                }
                $t->modify("next " . $schedule["repeatWeekday"]);
                break;
            case "daily":
                if ($schedule["skip"]) {
                    $t->modify($schedule["skip"] . " days");
                }
                $t->modify("tomorrow");
                break;
            case "off":
            default:
                return null;
        }
        $t->modify($rt->format("H:i"));
        return $t;
    }

}