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
\OCP\Util::addStyle('calendar_news', 'style');
\OCP\Util::addScript('calendar_news', 'simple');
?>

<div id="app" class="calendar-news-simple">
    <div id="app-navigation" class="app-navigation-administration">
        <ul>
            <li><a class="frame-link" href="last-newsletter"><?php p($l->t("Previous Newsletter"))?></a></li>
            <li><a class="frame-link" href="next-newsletter"><?php p($l->t("Next Newsletter"))?></a></li>
        </ul>
    </div>

    <div id="app-content">
        <div id="app-content-wrapper">
            <div class="center-container">
                <div class="note">
                    <?php p($l->t("Due to changes to the calendars used to generate the newsletter, this preview might not be accurate!"))?>
                </div>
                <iframe></iframe>
                <div class="loading">
                    <div class="icon-loading-dark"></div>
                </div>
            </div>
        </div>
    </div>
</div>

