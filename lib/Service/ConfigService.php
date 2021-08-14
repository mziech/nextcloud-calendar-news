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


use OCP\Calendar\ICalendar;
use OCP\Calendar\IManager;
use OCP\IConfig;

class ConfigService {

    /**
     * @var IConfig
     */
    private $config;
    /**
     * @var IManager
     */
    private $calendarManager;

    private $AppName;

    function __construct($AppName, IManager $calendarManager, IConfig $config) {
        $this->config = $config;
        $this->AppName = $AppName;
        $this->calendarManager = $calendarManager;
    }

    public function validateCalendarIds($config) {
        $actual = [];
        foreach ($config["sections"] as $section) {
            if ($section["type"] === "calendar") {
                $actual = array_merge($actual, $section["calendar"]["ids"]);
            }
        }
        $actual = array_unique($actual);

        $allowed = array_map(function (ICalendar $calendar) {
            return $calendar->getKey();
        }, $this->calendarManager->getCalendars());

        if (!empty(array_diff($actual, $allowed))) {
            throw new \RuntimeException("Will not save configuration with calendars the user cannot access: " .
                "allowed=" . implode(", ", $allowed) . ", " .
                "actual=" . implode(", ", $actual));
        }
    }

    public function save($config) {
        $this->validateCalendarIds($config);
        $this->config->setAppValue($this->AppName, "config", json_encode($config));
    }

    public function load() {
        return json_decode(
            $this->config->getAppValue($this->AppName, "config", '{"sections":[]}'),
            true
        );
    }

}