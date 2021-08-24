<?php
/**
 * iCalendar sync for the Calendar plugin
 *
 * @version @package_version@
 * @author Daniel Morlock <daniel.morlock@awesome-it.de>
 * @author JodliDev <jodlidev@gmail.com>
 *
 * Copyright (C) Awesome IT GbR <info@awesome-it.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ical_sync implements Isync
{
    const ACTION_NONE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_CREATE = 4;

    private $cal_id = null;
    private $url = null;
    private $user = null;
    private $pass = null;
    private $ical = null;

    /**
     *  Default constructor for calendar synchronization adapter.
     *
     * @param int Calendar id.
     * @param array Hash array with ical properties:
     *   url: Absolute URL to iCAL resource.
     */
    public function __construct($props)
    {
        $this->ical = libcalendaring::get_ical();
        $this->cal_id = $props["id"];

        $this->url = $props["caldav_url"];
        $this->user = isset($props["ical_user"]) ? $props["ical_user"] : null;
        $this->pass = isset($props["ical_pass"]) ? $props["ical_pass"] : null;
    }

    /**
     * Determines whether current calendar needs to be synced.
     *
     * @return boolean True if the current calendar needs to be synced, false otherwise.
     */
    public function is_synced()
    {
        // No change to check that so far.
        return false;
    }

    /**
     * Fetches events from iCAL resource and returns updates.
     *
     * @param array List of local events.
     * @return array Tuple containing the following lists:
     *
     * Hash list for iCAL events to be created or to be updated with the keys:
     *  local_event: The local event in case of an update.
     * remote_event: The current event retrieved from caldav server.
     *
     * A list of event ids that are in sync.
     */
    public function get_updates($events)
    {
        $context = null;
        if($this->user != null && $this->pass != null)
        {
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic " . base64_encode("$this->user:$this->pass")
                )
            ));
        }

        $vcal = file_get_contents($this->url, false, $context);
        $updates = array();
        $synced = array();
        if($vcal !== false) {

            // Hash existing events by uid.
            $events_hash = array();
            foreach($events as $event) {
                $events_hash[$event['uid']] = $event;
            }

            foreach ($this->ical->import($vcal) as $remote_event) {

                // Attach remote event to current calendar
                $remote_event['calendar'] = $this->cal_id;

                $local_event = null;
                if($events_hash[$remote_event['uid']])
                    $local_event = $events_hash[$remote_event['uid']];

                // Determine whether event don't need an update.
                if($local_event && $local_event['changed'] >= $remote_event['changed']) {
                    array_push($synced, $local_event["id"]);
                }
                else if($local_event) {
                    array_push($updates, array('local_event' => $local_event, 'remote_event' => $remote_event, 'url' => $this->url));
                }
                else {
                    array_push($updates, array('remote_event' => $remote_event, 'url' => $this->url));
                }
            }
        }

        return array($updates, $synced);
    }

    /**
     * Getter for current calendar ctag (only for CalDAV).
     * @return string
     */
    public function get_ctag() {
        return 'none';
    }

    /**
     * Creates the given event.
     *
     * @param array Hash array with event properties.
     * @return array with updated "caldav_url" and "caldav_tag" attributes, null on error.
     */
    public function create_event($event) {
        return $event;
    }

    /**
     * Updates the given event.
     *
     * @param array Hash array with event properties to update, must include "uid", "caldav_url" and "caldav_tag".
     * @return boolean True on success, false on error, -1 if the given event/etag is not up to date.
     */
    public function update_event($event) {
        return false;
    }

    /**
     * Removes the given event.
     *
     * @param array Hash array with events properties, must include "caldav_url".
     * @return boolean True on success, false on error.
     */
    public function remove_event($event) {
        return false;
    }
}
?>