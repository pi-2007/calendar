<?php
/**
 * Interface for different sync drivers
 *
 * @version @package_version@
 * @author JodliDev <jodlidev@gmail.com>
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

interface Isync {
    /**
     * Getter for current calendar ctag (only for CalDAV).
     * @return string
     */
    public function get_ctag();

    /**
     * Determines whether current calendar needs to be synced
     *
     * @return boolean True if the current calendar needs to be synced, false otherwise.
     */
    public function is_synced();

    /**
     * Synchronizes given events with server and returns updates.
     *
     * @param array List of hash arrays with event properties, must include "caldav_url" and "tag".
     * @return array Tuple containing the following lists:
     *
     * Caldav properties for events to be created or to be updated with the keys:
     *          url: Event ical URL relative to calendar URL
     *         etag: Remote etag of the event
     *  local_event: The local event in case of an update.
     * remote_event: The current event retrieved from caldav server.
     *
     * A list of event ids that are in sync.
     */
    public function get_updates($events);

    /**
     * Creates the given event.
     *
     * @param array Hash array with event properties.
     * @return array with updated "caldav_url" and "caldav_tag" attributes, null on error.
     */
    public function create_event($event);

    /**
     * Updates the given event.
     *
     * @param array Hash array with event properties to update, must include "uid", "caldav_url" and "caldav_tag".
     * @return boolean True on success, false on error, -1 if the given event/etag is not up to date.
     */
    public function update_event($event);

    /**
     * Removes the given event.
     *
     * @param array Hash array with events properties, must include "caldav_url".
     * @return boolean True on success, false on error.
     */
    public function remove_event($event);
}