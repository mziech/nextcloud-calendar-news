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
use OCA\CalendarNews\Service\ConfigService;
use OCA\CalendarNews\Service\NewsletterService;
use OCA\CalendarNews\Service\ScheduleService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IGroupManager;
use OCP\IRequest;

class PageController extends Controller {
	private $userId;
    /**
     * @var IGroupManager
     */
    private $groupManager;
    /**
     * @var NewsletterService
     */
    private $newsletterService;
    /**
     * @var ConfigService
     */
    private $configService;
    /**
     * @var ScheduleService
     */
    private $scheduleService;

    public function __construct(
        $AppName,
        IRequest $request,
        IGroupManager $groupManager,
        NewsletterService $newsletterService,
        ConfigService $configService,
        ScheduleService $scheduleService,
        $UserId
    ){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
        $this->groupManager = $groupManager;
        $this->newsletterService = $newsletterService;
        $this->configService = $configService;
        $this->scheduleService = $scheduleService;
    }

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
	    if ($this->groupManager->isAdmin($this->userId)) {
            return new TemplateResponse('calendar_news', 'index');
        } else {
            return new TemplateResponse('calendar_news', 'simple');
        }
	}

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function simple() {
        return new TemplateResponse('calendar_news', 'simple');
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function lastNewsletter() {
        return new DataDisplayResponse(
            $this->newsletterService->getPreview(array_merge($this->configService->load(), [
                "previewType" => "html",
                "previewTime" => $this->scheduleService->getLastExecutionTime()->format("Y-m-d\\TH:i:s.uO")
            ])),
            Http::STATUS_OK,
            ["Content-Type" => "text/html"]
        );
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function nextNewsletter() {
        return new DataDisplayResponse(
            $this->newsletterService->getPreview(array_merge($this->configService->load(), [
                "previewType" => "html",
                "previewTime" => $this->scheduleService->getNextExecutionTime()->format("Y-m-d\\TH:i:s.uO")
            ])),
            Http::STATUS_OK,
            ["Content-Type" => "text/html"]
        );
    }

}
