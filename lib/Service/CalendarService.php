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


use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IUserManager;

/**
 * Service to access Nextcloud's calendar database, big parts of this code are copied from the original calendar app.
 *
 * @package OCA\CalendarNews\Service
 */
class CalendarService {

    public function __construct(
        IDBConnection $db,
        IUserManager $userManager,
        IGroupManager $groupManager,
        IL10N $l10n
    ) {
        $this->db = $db;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->l10n = $l10n;
    }

    public function getAllCalendars() {
        $fields = [
            "id",
            "displayName",
            "principaluri"
        ];

        $query = $this->db->getQueryBuilder();
        $query->select($fields)->from('calendars')
            ->addOrderBy("principaluri", "ASC")
            ->addOrderBy('calendarorder', 'ASC');
        $stmt = $query->execute();

        $calendars = [];
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $calendars[] = $row;
        }

        $stmt->closeCursor();

        return $calendars;
    }

    public function getCalendarObjects($calendarIds, $timeRange = null, $componentType = null) {
        if (empty($calendarIds)) {
            return [];
        }

        $columns = ['calendardata'];

        $query = $this->db->getQueryBuilder();
        $query->select($columns)
            ->from('calendarobjects')
            ->where($query->expr()->in('calendarid', $query->createNamedParameter($calendarIds, IQueryBuilder::PARAM_STR_ARRAY)))
            ->orderBy("firstoccurence", "ASC");

        if ($componentType) {
            $query->andWhere($query->expr()->eq('componenttype', $query->createNamedParameter($componentType)));
        }

        if ($timeRange && $timeRange['start']) {
            $query->andWhere($query->expr()->gt('lastoccurence', $query->createNamedParameter($timeRange['start']->getTimeStamp())));
        }
        if ($timeRange && $timeRange['end']) {
            $query->andWhere($query->expr()->lt('firstoccurence', $query->createNamedParameter($timeRange['end']->getTimeStamp())));
        }

        $stmt = $query->execute();

        $result = [];
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $this->parseVcf($row["calendardata"]);
        }

        return $result;
    }

    private function parseVcf($text) {
        return \Sabre\VObject\Reader::read($text);
    }

}