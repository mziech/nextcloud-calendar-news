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
namespace OCA\CalendarNews\Service;


use OCP\IL10N;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;
use Psr\Log\LoggerInterface;

class NewsletterService {

    /**
     * @var ConfigService
     */
    private $configService;
    /**
     * @var IMailer
     */
    private $mailer;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var IL10N
     */
    private $l10n;

    /**
     * @var \DateTimeZone
     */
    private $tz;
    /**
     * @var CalendarService
     */
    private $calendarService;

    function __construct(
        CalendarService $calendarService,
        ConfigService $configService,
        IMailer $mailer,
        LoggerInterface $logger,
        IL10N $l10n
    ) {
        $this->configService = $configService;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->l10n = $l10n;
        $this->tz = new \DateTimeZone("Europe/Berlin");
        $this->calendarService = $calendarService;
    }

    private function buildTemplate(array $config) {
        $template = $this->mailer->createEMailTemplate(uniqid());
        $template->addHeader();

        if (!isset($config["sections"])) {
            throw new \Exception("Sections are not defined in config: " . json_encode($config));
        }

        $lc = $this->l10n->getLanguageCode();
        $lcu = strtoupper($lc);
        setlocale(LC_ALL, $lc, "$lc.utf8", "{$lc}_{$lcu}", "{$lc}_{$lcu}.utf8");
        foreach ($config["sections"] as $section) {
            if ($section["type"] === "heading") {
                $template->addHeading($section["text"]);
            } elseif ($section["type"] === "text") {
                $template->addBodyText($section["text"]);
            } elseif ($section["type"] === "calendar") {
                $this->addCalendarSection($template, $config, $section);
            } else {
                $template->addBodyText("Unknown section:");
                $template->addBodyText(json_encode($section));
            }
        }
        $template->addFooter();
        return $template;
    }

    public function getPreview(array $config) {
        $this->configService->validateCalendarIds($config);
        $template = $this->buildTemplate($config);
        return $config["previewType"] === "text" ?
            "<html><body><pre>".htmlentities($template->renderText())."</pre></body></html>" :
            $template->renderHtml();
    }

    public function send(array $recipients, $subject) {
        $this->logger->info("Sending newsletter with subject '$subject' to ". implode(", ", $recipients));
        $config = $this->configService->load();
        $template = $this->buildTemplate($config);
        $msg = $this->mailer->createMessage();
        $msg->setBcc($recipients);
        $msg->setPlainBody($template->renderText());
        $msg->setHtmlBody($template->renderHtml());
        $msg->setSubject($subject);
        $this->mailer->send($msg);
    }

    private function now(array $config) {
        return isset($config["previewTime"]) && !empty($config["previewTime"]) ?
            \DateTime::createFromFormat("Y-m-d\\TH:i:s.uO", $config["previewTime"]) :
            new \DateTime();
    }

    private function addCalendarSection(IEMailTemplate $template, array $config, array $section) {
        $timeRange = ["start" => $this->now($config)];
        if (isset($section["calendar"]["start"]) && !empty($section["calendar"]["start"])) {
            $now = $this->now($config);
            $timeRange["start"] = $now->add(new \DateInterval($section["calendar"]["start"]));
        }
        if (isset($section["calendar"]["end"]) && !empty($section["calendar"]["end"])) {
            $now = $this->now($config);
            $timeRange["end"] = $now->add(new \DateInterval($section["calendar"]["end"]));
        }
        $items = $this->calendarService->searchCalendars($section["calendar"]["ids"], $timeRange);
        foreach ($items as $item) {
            $this->addCalenderEvent($template, $item, $section);
        }
        $template->addBodyButton('***', '',
            "\n===========================================================================\n");
    }

    /**
     * @param IEMailTemplate $template
     * @param $item
     */
    private function addCalenderEvent(IEMailTemplate $template, $item, array $section) {
        $placeholders = [];
        date_default_timezone_set($this->tz->getName());
        /** @var $t \DateTimeImmutable */
        $t = $item["DTSTART"][0]->setTimezone($this->tz);
        /** @var $tend \DateTimeImmutable */
        $tend = $item["DTEND"][0]->setTimezone($this->tz);
        $allDay = isset($item["DTSTART"][1]["VALUE"]); // TODO: really???
        $description = isset($item["DESCRIPTION"][0]) ? $item["DESCRIPTION"][0] : "";

        $placeholders['summary'] = $item["SUMMARY"][0];
        $placeholders['startDate'] = strftime("%A, %d.%m.%Y", $t->getTimestamp());
        $placeholders['endDate'] = strftime("%A, %d.%m.%Y", $tend->getTimestamp());
        $tstr = strftime($allDay ? "%A, %d.%m.%Y" : "%A, %d.%m.%Y %R", $t->getTimestamp());
        if ($allDay) {
            $tend = $tend->sub(new \DateInterval("P1D"));
        }
        if ($t->getTimeStamp() != $tend->getTimeStamp()) {
            if ($placeholders['startDate'] != $placeholders['endDate']) {
                $placeholders['dateTimeRange'] = $tstr . " - " . strftime($allDay ? "%A, %d.%m.%Y" : "%A, %d.%m.%Y %R", $tend->getTimestamp());
            } else {
                $placeholders['dateTimeRange'] = $tstr . " - " . strftime("%R", $tend->getTimestamp());
            }
        } else {
            $placeholders['dateTimeRange'] = $tstr;
        }
        if ($placeholders['startDate'] != $placeholders['endDate']) {
            $placeholders['dateRange'] = $placeholders['startDate'] . " - " . $placeholders['endDate'];
        } else {
            $placeholders['dateRange'] = $placeholders['startDate'];
        }
        $placeholders['description'] = $description;
        if (isset($section["calendar"]["descriptionRegex"]) && !empty($section["calendar"]["descriptionRegex"])) {
            $matches = [];
            if (preg_match($section["calendar"]["descriptionRegex"], $description, $matches)) {
                foreach ($matches as $k => $v) {
                    $placeholders["$k"] = $v;
                }
            }
        }
        $placeholders = array_merge($placeholders, $this->parseDescriptionItems($description));
        $placeholders['*'] = json_encode($placeholders, JSON_PRETTY_PRINT);
        $line2 = $this->applyFormat($section["calendar"]["format2"], $placeholders);
        $line1 = $this->applyFormat($section["calendar"]["format1"], $placeholders);
        $template->addBodyListItem(
            nl2br(htmlentities($line2)),
            nl2br(htmlentities($line1)),
            '',
            str_replace("\n", "\n    ", wordwrap($line2 != '' ? rtrim($line1) . ":\n" . $line2 : $line1)),
            false
        );
    }

    private function applyFormat($format, $placeholders) {
        return preg_replace_callback("/\\\$\\{[^\\}]*\\}/", function ($expr) use ($placeholders) {
            $exprs = explode(':', substr($expr[0], 2, strlen($expr[0]) - 3));
            foreach ($exprs as $expr) {
                $nameAndFilters = explode("|", $expr);
                $name = $nameAndFilters[0];
                if (isset($placeholders[$name])) {
                    $value = $placeholders[$name];
                    if (count($nameAndFilters) > 1) {
                        foreach (array_slice($nameAndFilters, 1) as $filter) {
                            $value = $this->applyFilter($value, $filter);
                        }
                    }
                    return $value;
                }
            }
            return '';
        }, $format);
    }

    private function parseDescriptionItems($desc) {
        $current = "prefix";
        $body = "";
        $placeholders = [];
        foreach (explode("\n", $desc) as $line) {
            $matches = [];
            if (preg_match("/^([A-Z][^:]*):(?: (.*)|)$/", $line, $matches)) {
                $placeholders["item.$current"] = trim($body);
                $current = $matches[1];
                $body = isset($matches[2]) ? $matches[2] : "";
            } else {
                $body = $body . "\n" . $line;
            }
        }
        $placeholders["item.$current"] = trim($body);
        return $placeholders;
    }

    private function applyFilter($value, $filter) {
        if ($filter == "badge") {
            return array_reduce(explode("\n", $value), function ($carry, $item) {
                return "$carry [$item]";
            }, "");
        } else if ($filter == "pipe") {
            return implode(" | ", explode("\n", $value));
        } else if ($filter == "slash") {
            return implode(" / ", explode("\n", $value));
        } else if ($filter == "nl") {
            return implode("\n", explode("\n", $value));
        } else if ($filter == "dash") {
            return implode("\n- ", explode("\n", $value));
        } else if ($filter == "dash2") {
            return implode("\n-- ", explode("\n", $value));
        } else if ($filter == "dash3") {
            return implode("\n--- ", explode("\n", $value));
        } else {
            $this->logger->warning("Unknown filter: $filter");
            return $value;
        }
    }

}