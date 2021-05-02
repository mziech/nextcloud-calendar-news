<div class="view-container" ng-view></div>

<script type="text/ng-template" id="schedule.html">
    <div id="config-container">
        <h1><?php p($l->t("Schedule Newsletter")); ?></h1>
    </div>
    <form name="scheduleForm" ng-submit="save()">
        <div>
            <label>
                <?php p($l->t("Recipients:")); ?>
                <input type="email" ng-model="emailToAdd" ng-keydown="addEmail($event);">
            </label>
            <div class="element-badge" ng-repeat="email in schedule.emails">
                {{ email }}
                <button type="button" class="icon icon-close"
                        ng-click="removeEmail(email)">
                </button>
            </div>
        </div>
        <div>
            <button type="button" ng-click="sendNow()" ng-disabled="scheduleForm.$dirty"><?php p($l->t("Send now")); ?></button>
        </div>
        <div>
            <?php p($l->t("Last execution time:")); ?><br>
            <span ng-if="lastExecutionTime">{{lastExecutionTime|date:"medium"}}</span>
            <span ng-if="lastExecutionTime === null"><?php p($l->t("never")); ?></span>
        </div>
        <div>
            <label>
                <?php p($l->t("Subject:")); ?>
                <input ng-model="schedule.subject">
            </label>
            <label>
                <?php p($l->t("Interval:")); ?>
                <select ng-model="schedule.repeatInterval">
                    <option value="off"><?php p($l->t("Off")); ?></option>
                    <option value="daily"><?php p($l->t("Daily")); ?></option>
                    <option value="weekly"><?php p($l->t("Weekly")); ?></option>
                    <option value="monthly"><?php p($l->t("Monthly (fixed weekday)")); ?></option>
                    <option value="monthly_dom"><?php p($l->t("Monthly (fixed day of month)")); ?></option>
                    <option value="yearly"><?php p($l->t("Yearly (fixed weekday)")); ?></option>
                    <option value="yearly_dom"><?php p($l->t("Yearly (fixed day of month)")); ?></option>
                </select>
            </label>
            <label>
                <?php p($l->t("Skip executions:")); ?>
                <input ng-model="schedule.skip" type="number" min="0"/>
            </label>
            <label ng-if="schedule.repeatInterval == 'yearly' || schedule.repeatInterval == 'yearly_dom'">
                <?php p($l->t("Month:")); ?>
                <select ng-model="schedule.repeatMonth">
                    <option value="January"><?php p($l->t("January")); ?></option>
                    <option value="February"><?php p($l->t("February")); ?></option>
                    <option value="March"><?php p($l->t("March")); ?></option>
                    <option value="April"><?php p($l->t("April")); ?></option>
                    <option value="May"><?php p($l->t("May")); ?></option>
                    <option value="June"><?php p($l->t("June")); ?></option>
                    <option value="July"><?php p($l->t("July")); ?></option>
                    <option value="August"><?php p($l->t("August")); ?></option>
                    <option value="September"><?php p($l->t("September")); ?></option>
                    <option value="October"><?php p($l->t("October")); ?></option>
                    <option value="November"><?php p($l->t("November")); ?></option>
                    <option value="December"><?php p($l->t("December")); ?></option>
                </select>
            </label>
            <label ng-if="schedule.repeatInterval == 'monthly' || schedule.repeatInterval == 'yearly'">
                <?php p($l->t("Week of month:")); ?>
                <select ng-model="schedule.repeatWeek">
                    <option value="next"><?php p($l->t("First")); ?></option>
                    <option value="second"><?php p($l->t("Second")); ?></option>
                    <option value="third"><?php p($l->t("Third")); ?></option>
                    <option value="fourth"><?php p($l->t("Fourth")); ?></option>
                    <option value="fifth"><?php p($l->t("Fifth")); ?></option>
                </select>
            </label>
            <label ng-if="schedule.repeatInterval == 'monthly_dom' || schedule.repeatInterval == 'yearly_dom'">
                <?php p($l->t("Day of month: (zero and negative values refer to days before end of month)")); ?>
                <input ng-model="schedule.repeatDayOfMonth" type="number" min="-31" max="31"/>
            </label>
            <label ng-if="schedule.repeatInterval == 'weekly' || schedule.repeatInterval == 'monthly' || schedule.repeatInterval == 'yearly'">
                <?php p($l->t("Weekday:")); ?>
                <select ng-model="schedule.repeatWeekday">
                    <option value="monday"><?php p($l->t("Monday")); ?></option>
                    <option value="tuesday"><?php p($l->t("Tuesday")); ?></option>
                    <option value="wednesday"><?php p($l->t("Wednesday")); ?></option>
                    <option value="thursday"><?php p($l->t("Thursday")); ?></option>
                    <option value="friday"><?php p($l->t("Friday")); ?></option>
                    <option value="saturday"><?php p($l->t("Saturday")); ?></option>
                    <option value="sunday"><?php p($l->t("Sunday")); ?></option>
                </select>
            </label>
            <label>
                <?php p($l->t("Time:")); ?>
                <input type="time" ng-model="schedule.repeatTime" ng-model-options="{timezone: 'utc'}">
            </label>
            <div>
                <?php p($l->t("Next execution time:")); ?><br>
                <span ng-if="nextExecutionTime !== previewNextExecutionTime" style="text-decoration: line-through">
                    {{nextExecutionTime|date:"medium"}}
                </span>
                {{previewNextExecutionTime|date:"medium"}}
            </div>
        </div>
        <button type="submit" ng-disabled="!scheduleForm.$dirty"><?php p($l->t("Save schedule")); ?></button>
    </form>
</script>

<script type="text/ng-template" id="config.html">
    <div id="config-container">
        <h1><?php p($l->t("Configure Newsletter")); ?></h1>
        <form name="configForm" ng-submit="save()">
            <div ng-repeat="(nr, section) in sections" class="config-section">
                <h3>
                    <?php p($l->t("Section")); ?> {{nr + 1}}
                    <button type="button" class="icon icon-delete" ng-click="removeSection(nr)"></button>
                    <button type="button" class="icon icon-triangle-n" ng-if="nr > 0" ng-click="moveSection(nr, true)"></button>
                    <button type="button" class="icon icon-triangle-s" ng-if="nr < sections.length - 1" ng-click="moveSection(nr, false)"></button>
                </h3>
                <div>
                    <label>
                        <?php p($l->t("Section Type:")); ?>
                        <select ng-model="section.type">
                            <option value="calendar"><?php p($l->t("Calendar")); ?></option>
                            <option value="text"><?php p($l->t("Text")); ?></option>
                            <option value="heading"><?php p($l->t("Heading")); ?></option>
                        </select>
                    </label>
                </div>
                <div ng-if="section.type == 'heading'">
                    <label>
                        <?php p($l->t("Heading:")); ?>
                        <input ng-model="section.text" />
                    </label>
                </div>
                <div ng-if="section.type == 'calendar'">
                    <div>
                        <label>
                            <?php p($l->t("Calendars:")); ?>
                            <select ng-model="calendarToAdd[nr]"
                                    ng-change="addCalendar(nr)"
                                    ng-options="calendar.displayName + ' (' + calendar.user + ')' for calendar in calendars track by calendar.id"></select>
                        </label>
                        <div class="calendar-badges">
                            <div class="calendar-badge" ng-repeat="id in section.calendar.ids">
                                {{ calendarDisplayNames[id] }}
                                <button type="button" class="icon icon-close"
                                        ng-click="removeCalendar(nr, id)">
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label>
                            <?php p($l->t("Delay until start of entries:")); ?>
                            <input ng-model="section.calendar.start" />
                        </label>
                        <label>
                            <?php p($l->t("Time range until last entry:")); ?>
                            <input ng-model="section.calendar.end" />
                        </label>
                    </div>
                    <div>
                        <label>
                            <?php p($l->t("Format of first line:")); ?>
                            <input ng-model="section.calendar.format1" />
                        </label>
                    </div>
                    <div>
                        <label>
                            <?php p($l->t("Format of second line:")); ?>
                            <input ng-model="section.calendar.format2" />
                        </label>
                    </div>
                    <div>
                        <label>
                            <?php p($l->t("Description Regex:")); ?>
                            <input ng-model="section.calendar.descriptionRegex" />
                        </label>
                    </div>
                </div>
                <div ng-if="section.type == 'text'">
                    <label>
                        <?php p($l->t("Text:")); ?>
                        <textarea ng-model="section.text"></textarea>
                    </label>
                </div>
            </div>
            <button type="button" ng-click="addSection()"><?php p($l->t("Add Section")); ?></button>
            <button type="submit" ng-disabled="!configForm.$dirty"><?php p($l->t("Save configuration")); ?></button>
        </form>
    </div>
</script>

<div class="preview-container" ng-controller="PreviewController">
    <div class="preview-form">
        <label>
            <input type="radio" name="previewType" value="html" ng-model="previewType">
            <?php p($l->t("HTML")); ?>
        </label>
        <label>
            <input type="radio" name="previewType" value="text" ng-model="previewType">
            <?php p($l->t("Text")); ?>
        </label>
        <label>
            <input type="datetime-local" ng-model="previewTime">
        </label>
        <button type="button" ng-click="refresh()"><?php p($l->t("Refresh preview")); ?></button>
    </div>
    <div class="preview-iframe-container">
        <iframe id="preview-iframe" class="preview-iframe"></iframe>
    </div>
</div>