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
namespace OCA\CalendarNews\Controller;

use OCA\CalendarNews\Service\ScheduleService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ScheduleController extends Controller {
    /**
     * @var ScheduleService
     */
    private $scheduleService;

    public function __construct(
        $AppName,
        IRequest $request,
        ScheduleService $scheduleService
    ) {
        parent::__construct($AppName, $request);
        $this->scheduleService = $scheduleService;
    }

    public function get(): JSONResponse {
        $schedule = $this->scheduleService->load();
        $last = $this->scheduleService->getLastExecutionTime();
        $next = $this->scheduleService->getNextExecutionTime();
        $schedule["lastExecutionTime"] = $last == null ? null : $last->format(\DateTimeInterface::ISO8601);
        $schedule["nextExecutionTime"] = $next == null ? null : $next->format(\DateTimeInterface::ISO8601);
        return new JSONResponse($schedule);
    }

    public function post(): JSONResponse {
        return $this->sanitizeDto(function ($dto) {
            $this->scheduleService->save($dto);
            return new JSONResponse(["success" => true]);
        });
    }

    public function previewNext(): JSONResponse {
        return $this->sanitizeDto(function ($dto) {
            $nextExecutionTime = $this->scheduleService->getNextExecutionTime($dto["schedule"]);
            return new JSONResponse([
                "nextExecutionTime" => $nextExecutionTime != null
                    ? $nextExecutionTime->format(\DateTimeInterface::ISO8601)
                    : null
            ]);
        });
    }

    public function removeLast(): JSONResponse {
        $this->scheduleService->removeLastExecutionTime();
        return new JSONResponse(["success" => true]);
    }

    public function sendNow(): JSONResponse {
        $this->scheduleService->sendNow();
        return new JSONResponse(["success" => true]);
    }

    private function sanitizeDto(\Closure $closure): JSONResponse {
        try {
            $schedule = $this->request->post["schedule"];
            $dto = [];
            $dto["emails"] = $schedule["emails"];
            $dto["subject"] = ValidationException::onBlank($schedule, "subject");
            $dto["repeatInterval"] = ValidationException::onValueNotInList($schedule, "repeatInterval", [
                "off", "daily", "weekly", "monthly", "monthly_dom", "yearly", "yearly_dom"
            ]);
            $dto["skip"] = ValidationException::onNumberOutOfRange($schedule, "skip", 0, PHP_INT_MAX);
            if (in_array($dto["repeatInterval"], ["yearly", "yearly_dom"])) {
                $dto["repeatMonth"] = ValidationException::onValueNotInList($schedule, "repeatMonth", [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ]);
            }
            if (in_array($dto["repeatInterval"], ["yearly", "monthly"])) {
                $dto["repeatWeek"] = ValidationException::onValueNotInList($schedule, "repeatWeek", [
                    "next", "second", "third", "fourth", "fifth"
                ]);
            }
            if (in_array($dto["repeatInterval"], ["yearly_dom", "monthly_dom"])) {
                $dto["repeatDayOfMonth"] = ValidationException::onNumberOutOfRange($schedule, "repeatDayOfMonth", -31, 31);
            }
            if (in_array($dto["repeatInterval"], ["yearly", "monthly", "weekly"])) {
                $dto["repeatWeekday"] = ValidationException::onValueNotInList($schedule, "repeatWeekday", [
                    "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"
                ]);
            }
            $dto["repeatTime"] = ValidationException::onBlank($schedule, "repeatTime");
            return $closure(["schedule" => $dto]);
        } catch (ValidationException $e) {
            return new JSONResponse($e->getErrors(), Http::STATUS_BAD_REQUEST);
        }
    }

}
