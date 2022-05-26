<?php
/**
 * @copyright Copyright (c) 2021 Marco Ziech <marco+nc@ziech.net>
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

use OCA\DAV\CalDAV\CalDavBackend;
use OCA\DAV\CalDAV\Calendar;
use OCA\DAV\CalDAV\CalendarImpl;
use OCP\IConfig;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

class CalendarService {


    /**
     * @var CalDavBackend
     */
    private $calDavBackend;
    /**
     * @var IL10N
     */
    private $l10n;
    /**
     * @var IConfig
     */
    private $config;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CalDavBackend $calDavBackend,
        IL10N $l10n,
        IConfig $config,
        LoggerInterface $logger
    ) {
        $this->calDavBackend = $calDavBackend;
        $this->l10n = $l10n;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function searchCalendars($ids, $timeRange) {
        $items = [];
        foreach ($ids as $id) {
            $calendarInfo = $this->calDavBackend->getCalendarById($id);
            if (!$calendarInfo) {
                $this->logger->warning("CalDav calendar not found: $id");
                continue;
            }

            $calendar = $this->createCalendar($calendarInfo);
            $results = $calendar->search("", ["SUMMARY"], ["timerange" => $timeRange]);
            foreach ($results as $result) {
                $items = array_merge($items, $result["objects"]);
            }
        }
        usort($items, function ($a, $b) {
            $astart = $a["DTSTART"][0];
            $bstart = $b["DTSTART"][0];
            return $astart->getTimestamp() - $bstart->getTimestamp();
        });

        return $items;
    }

    private function createCalendar(array $calendarInfo) {
        $calendar = new Calendar($this->calDavBackend, $calendarInfo, $this->l10n, $this->config, $this->logger);
        return new CalendarImpl(
            $calendar,
            $calendarInfo,
            $this->calDavBackend
        );
    }

}
