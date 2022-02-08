# mp-filter-recitautolink
This filter allows, among other things, the display of the activity icon as well as a check mark indicating the status of a completed activity. It also allows you to take advantage of Moodle's database and create a more personalized experience. The examples below show you the available integration codes and the result on the user's screen.

[![Youtube video](https://img.youtube.com/vi/FkpwLCFOUTU/0.jpg)](https://www.youtube.com/watch?v=FkpwLCFOUTU)

## Link to the activity
<img src='https://github.com/SN-RECIT-formation-a-distance/moodle-filter_recitautolink/blob/master/docs/filtre2.jpg' alt='Link to the activity' width='500px'/>
The attached example shows the display of a link to an activity. The left side shows the learner's screen. On the right-hand side, we see the integration code in the editor. The i/ parameter displays the activity icon, the c/ parameter displays the hook and the exact name of the activity title displays the link to the activity.

## Generation of the student's avatar or name
<img src='https://github.com/SN-RECIT-formation-a-distance/moodle-filter_recitautolink/blob/master/docs/filtre1.jpg' alt="Generation of the student's avatar or name" style='width: 500px;'/>
The attached example shows the display of the student's name on a page. The left side shows the learner's screen. On the right side, we see the embedding code in the editor. The `[[d/user.firstname]]` parameter causes the student's first name to be displayed. The information is taken from the database.

# Technical information
Represents the separator character used in the filter. If the character is <b>/</b>, the filter will search for it in <b>[[i/activityname]]</b>. All indicators ( i/, c/, d/ ) must be at the begenning of double brackets [[.<br/>

## Integration Code
<ul>
	<li>Activity name link : <b>[[activityname]]</b>.</li>
	<li>Activity name link with icon : <b>[[i/activityname]]</b>.</li>
	<li>Activity name link with completion checkbox : <b>[[c/activityname]]</b>.</li>
    <li>Activity name link with icon and completion checkbox : <b>[[i/c/activityname]]</b>.</li>
    <li>Change link name : <b>[[/i/c/desc:"Name"/]]</b> activityname.</li>
    <li>Add CSS classes : <b>[[/i/c/class:"btn btn-primary"/]]</b>.</li>
    <li>Open the link to an activity in another tab : <b>[[c/b/activityname]]</b> ou <b>[[i/c/b/activityname]]</b>.</li>
    <li>Link to a section: <b>[[s/sectionname]]</b> or <b>[[s/6]]</b> to go to section 6 if its name is not personalized (not usable in edit mode)..</li>
	<li>Course informations : <b>[[d/course.fullname]]</b>, <b>[[d/course.shortname]]</b>.</li>
	<li>Student firstname, lastname, email and avatar : <b>[[d/user.firstname]]</b>, <b>[[d/user.lastname]]</b>, <b>[[d/user.email]]</b> and <b>[[d/user.picture]]</b>.</li>
	<li>First teacher firstname, lastname, email and avatar : <b>[[d/teacher1.firstname]]</b>, <b>[[d/teacher1.lastname]]</b>, <b>[[d/teacher1.email]]</b> and <b>[[d/teacher1.picture]]</b>. The teacher must be in the group for his name to appear. Same for teacher2, teacher3, etc. for all teachers for that course.</li>
    <li>Link to H5P content: <b>[[h5p/Name of H5P]]</b></li>
</ul>

## Non-standard post-installation steps
 After installing this plugin, it is necessary to place it before the default Activity linking filter.