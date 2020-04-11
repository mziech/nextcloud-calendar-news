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
namespace OCA\CalendarNews\Controller;

use OCA\CalendarNews\Service\NewsletterService;
use OCA\CalendarNews\Service\ScheduleService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ScheduleController extends Controller {
    private $userId;
    /**
     * @var NewsletterService
     */
    private $newsletterService;
    /**
     * @var ScheduleService
     */
    private $scheduleService;

    public function __construct(
        $AppName,
        IRequest $request,
        $UserId,
        NewsletterService $newsletterService,
        ScheduleService $scheduleService
    ) {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->newsletterService = $newsletterService;
        $this->scheduleService = $scheduleService;
    }

    /**
     * @return JSONResponse
     */
    public function get() {
        $schedule = $this->scheduleService->load();
        $next = $this->scheduleService->getNextExecutionTime();
        $schedule["nextExecutionTime"] = $next == null ? null : $next->format(\DateTime::ISO8601);
        return new JSONResponse($schedule);
    }

    /**
     * @return JSONResponse
     */
    public function post() {
        $this->scheduleService->save($this->request->post);
        return new JSONResponse(["success" => true]);
    }

    /**
     * @return JSONResponse
     */
    public function sendNow() {
        $this->scheduleService->sendNow();
        return new JSONResponse(["success" => true]);
    }

}
