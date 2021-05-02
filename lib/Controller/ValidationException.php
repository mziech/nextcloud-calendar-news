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

namespace OCA\CalendarNews\Controller;


class ValidationException extends \Exception {

    private $errors;

    public function __construct(array $errors) {
        $this->errors = $errors;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * @throws ValidationException if the value is a blank string or null
     */
    public static function onBlank($dto, $key) {
        $s = self::onMissing($dto, $key);
        if ($s === null || trim($s) === "") {
            throw new ValidationException([$key => "not-blank"]);
        }
        return $s;
    }

    /**
     * @throws ValidationException if the value is out of range
     */
    public static function onNumberOutOfRange($dto, $key, int $low, int $high) {
        $n = self::onMissing($dto, $key);
        if ($n !== intval($n) || $n < $low || $n > $high) {
            throw new ValidationException([$key => "value-range"]);
        }
        return intval($n);
    }

    /**
     * @throws ValidationException if the value is not in the allowed list of values
     */
    public static function onValueNotInList($dto, $key, array $allowed) {
        $s = self::onMissing($dto, $key);
        if (!in_array($s, $allowed)) {
            throw new ValidationException([$key => "value-in"]);
        }
        return $s;
    }

    /**
     * @throws ValidationException if the given key is missing from the DTO
     */
    private static function onMissing($dto, $key) {
        if (!isset($dto[$key])) {
            throw new ValidationException([$key => "required"]);
        }
        $s = $dto[$key];
        return $s;
    }

}
