
var app = angular.module("CalendarNews", ["ngRoute"]);

app.config(function ($httpProvider, $routeProvider) {
    // Always send the CSRF token by default
    $httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;

    $routeProvider
        .when('/content', {
            controller: 'ConfigController',
            templateUrl: 'config.html'
        })
        .when('/schedule', {
            controller: 'ScheduleController',
            templateUrl: 'schedule.html'
        })
        .otherwise({
            redirectTo: '/content'
        });
});

app.controller("ScheduleController", function ($rootScope, $scope, $http) {
    $scope.schedule = {
        emails: [],
        repeatInterval: "weekly"
    };

    function load() {
        $http.get("schedule").then(function (xhr) {
            $scope.schedule = xhr.data.schedule;
            $scope.schedule.repeatTime = new Date($scope.schedule.repeatTime);
            $scope.lastExecutionTime = xhr.data.lastExecutionTime;
            $scope.nextExecutionTime = xhr.data.nextExecutionTime;
            $scope.previewNextExecutionTime = xhr.data.nextExecutionTime;
            $scope.scheduleForm.$setPristine();
        }, function () {
            OC.Notification.showTemporary(t("calendar_news", "Failed to load schedule"), {type: "error"});
        });
    }

    load();

    $scope.$watch("schedule", function () {
        $http.post("schedule/preview", {schedule: $scope.schedule}).then(function (xhr) {
            $scope.previewNextExecutionTime = xhr.data.nextExecutionTime;
        });
    }, true);

    $scope.save = function () {
        $http.post("schedule", {schedule: $scope.schedule}).then(function () {
            OC.Notification.showTemporary(t("calendar_news", "Configuration successfully saved"));
            load();
        }, function () {
            OC.Notification.showTemporary(t("calendar_news", "Failed to save configuration"), {type: "error"});
        });
    };

    $scope.sendNow = function () {
        if (confirm(t("calendar_news", "Really send the newsletter NOW to all receipients?"))) {
            $http.post("send-now", {emails: $scope.schedule.emails}).then(function () {
                OC.Notification.showTemporary(t("calendar_news", "Newsletter sent"));
            }, function () {
                OC.Notification.showTemporary(t("calendar_news", "Failed to send newsletter"), {type: "error"});
            });
        }
    };

    $scope.removeEmail = function (email) {
        var pos = $scope.schedule.emails.indexOf(email);
        if (pos >= 0) {
            $scope.schedule.emails.splice(pos, 1);
        }
    };

    $scope.addEmail = function ($event) {
        if ($event.keyCode === 13 || $event.keyCode === 10) {
            $event.preventDefault();
            $event.stopPropagation();
            if (!$scope.emailToAdd) {
                return false;
            }
            var parts = $scope.emailToAdd.split(/[,;|\/ ]+/);
            for (var i in parts) {
                var email = parts[i].trim();
                if (!$scope.schedule.emails) {
                    $scope.schedule.emails = [];
                }
                if (email !== "" && $scope.schedule.emails.indexOf(email) < 0) {
                    $scope.schedule.emails.push(email);
                }
            }
            $scope.emailToAdd = "";
            return false;
        }
    };

});

app.controller("ConfigController", function ($rootScope, $scope, $http) {
    $rootScope.sections = [];
    $scope.calendarDisplayNames = {};
    $scope.calendarToAdd = {};

    $http.get("calendars").then(function (xhr) {
        $scope.calendars = xhr.data;
        angular.forEach(xhr.data, function (cal) {
            $scope.calendarDisplayNames[cal.id] = cal.displayName + " (" + cal.user + ")";
        });
    });

    $http.get("config").then(function (xhr) {
        $rootScope.sections = xhr.data.sections;
    });

    $scope.addSection = function () {
        $rootScope.sections.push({
            type: "calendar",
            calendar: {
                ids: []
            },
            text: ""
        });
    };

    $scope.addCalendar = function (nr) {
        if ($scope.calendarToAdd[nr]) {
            $scope.removeCalendar(nr, $scope.calendarToAdd[nr].id);
            $rootScope.sections[nr].calendar.ids.push($scope.calendarToAdd[nr].id);
            $scope.calendarToAdd[nr] = null;
        }
    };

    $scope.removeCalendar = function (nr, id) {
        if (
            $rootScope.sections[nr].calendar.ids.indexOf(id) >= 0
        ) {
            $rootScope.sections[nr].calendar.ids.splice($rootScope.sections[nr].calendar.ids.indexOf(id), 1);
        }
    };

    $scope.removeSection = function (nr) {
        $rootScope.sections.splice(nr, 1);
    };

    $scope.moveSection = function (nr, up) {
        var other;
        if (up && nr > 0) {
            other = nr - 1;
        } else if (!up && nr < $rootScope.sections.length - 1) {
            other = nr + 1;
        } else {
            return;
        }

        var tmp = $rootScope.sections[other];
        $rootScope.sections[other] = $rootScope.sections[nr];
        $rootScope.sections[nr] = tmp;
    };

    $scope.updatePreview = function () {
        $rootScope.updatePreview();
    };

    $scope.$watch("sections", function () {
        $rootScope.updatePreview();
    }, true);

    $scope.save = function () {
        $http.post("config", {sections: $rootScope.sections}).then(function () {
            OC.Notification.showTemporary(t("calendar_news", "Configuration successfully saved"));
        }, function () {
            OC.Notification.showTemporary(t("calendar_news", "Failed to save configuration"), {type: "error"});
        });
    };

});

app.controller("PreviewController", function ($rootScope, $scope, $http) {
    $scope.previewType = "html";

    $scope.$watch("previewType", function () {
        $rootScope.updatePreview();
    }, true);

    function setPreviewHTML(html) {
        var doc = $('#preview-iframe').get(0).contentWindow.document.open();
        doc.write(html);
        doc.close();
    }

    $rootScope.updatePreview = function () {
        if (!$rootScope.sections) {
            setPreviewHTML("");
            return;
        }

        $http.post("preview", {
            sections: $rootScope.sections,
            previewType: $scope.previewType,
            previewTime: $scope.previewTime
        }).then(function (xhr) {
            setPreviewHTML(xhr.data);
        }).catch(function (xhr) {
            setPreviewHTML(xhr.data);
        });
    };

    $scope.refresh = function () {
        $rootScope.updatePreview();
    };
});
