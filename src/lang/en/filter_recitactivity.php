<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Activity name filtering defined strings.

/**
 * This filter must be put before Auto-linking with Manage Filters to work properly.
 *
 * @package    filter_recitactivity
 * @copyright  RECITFAD
 * @author     RECITFAD
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */

$string['filtername'] = "Recit's better activity names auto-linking";
$string['privacy:metadata'] = 'The "Recit\'s better activity names auto-linking" plugin does not store any personal data.';
$string['character'] = 'The separator character';
$string['teacherbygroup'] = 'Show teachers in user\'s group only';
$string['teacherbygroup_desc'] = 'If this is off, d/teacher will display all teachers of the course regardless of the group';
$string['character_desc'] = 'Represents the separator character used in the filter. 
    <br>If the character is <b style="color:red">/</b>, the filter will search for it in [[i<b style="color:red">/</b>activityname]].
	<br>All indicators (<b style="color:red"> i/, c/, d/, b/, s/</b>) must be at the begenning of double brackets <b style="color:red">[[</b>.
    <br><br><b>Integration code</b>
    <ul>
	<li>Activity name link : [[activityname]]</li>
	<li>Activity name link with icon : [[<b style="color:red">i/</b>activityname]]</li>
	<li>Activity name link with completion checkbox : [[<b style="color:red">c/</b>activityname]]</li>
    <li>Activity name link with icon and completion checkbox : [[<b style="color:red">i/c/</b>activityname]]</li>
    <li>Change link name : [[/i/c/desc:"Name"/]]activityname</li>
    <li>Add CSS classes : [[/i/c/class:"btn btn-primary"/]]</li>
    <li>Open the link to an activity in another tab : [[<b style="color:red">c/b/</b>activityname]] ou [[<b style="color:red">i/c/b/</b>activityname]]</li>
    <li> Link to a section: [[<b style="color: red">s/</b>sectionname]] or [[<b style="color: red">s/</b>/6]] to go to section 6 if its name is not personalized (not usable in edit mode).</li>
	<li>Course informations : [[<b style="color:red">d/</b>course.fullname]], [[<b style="color:red">d/</b>course.shortname]]</li>
	<li>Student firstname, lastname, email and avatar : [[<b style="color:red">d/</b>user.firstname]], [[<b style="color:red">d/</b>user.lastname]], [[<b style="color:red">d/</b>user.email]] and [[<b style="color:red">d/</b>user.picture]]</li>
	<li>First teacher firstname, lastname, email and avatar : [[<b style="color:red">d/</b>teacher1.firstname]], [[<b style="color:red">d/</b>teacher1.lastname]], [[<b style="color:red">d/</b>teacher1.email]] and [[<b style="color:red">d/</b>teacher1.picture]]</li>
    <li>Same for teacher2, teacher3, ... for all teachers for that course.</li>
    </ul>
	';
