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


use OC\AppFramework\Http;
use OCA\CalendarNews\Service\CalendarService;
use OCA\CalendarNews\Service\ConfigService;
use OCA\CalendarNews\Service\NewsletterService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * Class ConfigController
 *
 * @package OCA\CalendarNews\Controller
 */
class ConfigController extends Controller {
    private $userId;
    private $calendarService;
    /**
     * @var ConfigService
     */
    private $configService;
    /**
     * @var NewsletterService
     */
    private $newsletterService;

    public function __construct(
        $AppName,
        IRequest $request,
        $UserId,
        CalendarService $calendarService,
        ConfigService $configService,
        NewsletterService $newsletterService
    ) {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->calendarService = $calendarService;
        $this->configService = $configService;
        $this->newsletterService = $newsletterService;
    }

    /**
     * @return JSONResponse
     */
    public function getCalendars() {
        return new JSONResponse(array_map(function ($it) {
            return [
                "id" => $it["id"],
                "displayName" => $it["displayName"],
                "user" => array_values(array_slice(explode("/", $it["principaluri"]), -1))[0]
            ];
        }, $this->calendarService->getAllCalendars()));
    }

    /**
     * @return DataDisplayResponse
     */
    public function preview() {
        return new DataDisplayResponse(
            $this->newsletterService->getPreview($this->request->post),
            Http::STATUS_OK,
            ["Content-Type" => "text/html"]
        );
    }

    /**
     * @return JSONResponse
     */
    public function get() {
        return new JSONResponse($this->configService->load());
    }

    /**
     * @return JSONResponse
     */
    public function post() {
        $this->configService->save($this->request->post);
        return new JSONResponse(["success" => true]);
    }

}