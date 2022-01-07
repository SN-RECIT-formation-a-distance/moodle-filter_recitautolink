# mp-filter-recitautolink

 Ce filtre permet entre autres l'affichage de l'icône de l'acitvité ainsi qu'un crochet qui indique le statut d'une activité complétée. Il permet aussi de tirer profit de la base de données de Moodle et de créer une expérience plus personnalisée. Les exemples ci-dessous vous présentent les codes d'intégration disponibles et le résultat à l'écran de l'utilisateur.
 
This filter allows, among other things, the display of the activity icon as well as a check mark indicating the status of a completed activity. It also allows you to take advantage of Moodle's database and create a more personalized experience. The examples below show you the available integration codes and the result on the user's screen.
 
# Comment utiliser ce plug-in / How to use this 
Ceci repésente le caractère séparateur à utiliser dans le filtre. Si le caractère est <b>/</b>, la syntaxe sera la suivante <b>[[i/Nom de l'activité]]</b>. Tous les indicateurs ( i/, c/, d/, b/, s/ ) doivent être placés au début du double crochets ouverts [[.<br/>
<h4>Code d'intégration</h4>
<ul>
	<li>Lien vers une activité : <b>[[Nom de l'activité]]</b></li>
	<li>Lien vers une activité avec icône : <b>[[i/Nom de l'activité]]</b></li>
	<li>Lien vers une activité avec une case à cocher pour la complétion : <b>[[c/Nom de l'activité]]</b></li>
	<li>Lien vers une activité avec icône et une case à cocher pour la complétion : <b>[[i/c/Nom de l'activité]]</b></li>
	<li>Changer le nom du lien : <b>[[/i/c/desc:"Nom"/Nom de l'activité]]</b></li>
	<li>Mettre des classes CSS par example pour faire un bouton : <b>[[/i/c/class:"btn btn-primary"/Nom de l'activité]]</b></li>
	<li>Ouvrir le lien vers une activité dans une autre onglet : <b>[[c/b/Nom de l'activité]]</b> ou <b>[[i/c/b/Nom de l'activité]]</b></li>
	<li>Lien vers une section : <b>[[s/Nom de la section]]</b> ou <b>[[s/6]]</b> pour se diriger vers la section 6 si son nom n'est pas personnalisé (pas utilisable en mode édition). Exemble de lien vers une section : <b>[[s/class:"btn btn-primary"/NOM_DE_LA_SECTION_ICI]]</b></li>
	<li>Informations pour les noms du cours : <b>[[d/course.fullname]]</b>, <b>[[d/course.shortname]]</b></li>
	<li>Informations de l'élève, prénom, nom, courriel et avatar : <b>[[d/user.firstname]]</b>, <b>[[d/user.lastname]]</b>, <b>[[d/user.email]]</b> et <b>[[d/user.picture]]</b></li>
	<li>Informations pour le premier professeur, prénom, nom, courriel et avatar : <b>[[d/teacher1.firstname]]</b>, <b>[[d/teacher1.lastname]]</b>, <b>[[d/teacher1.email]]</b> et <b>[[d/teacher1.picture]]</b>. Le professeur doit être inscrit dans le groupe pour que l'affichage de son nom apparaisse. Pour les autres professeurs du cours, ils sont numérotés teacher2, teacher3, etc.</li>
	<li>Lien vers le contenu du H5P : <b>[[h5p/Nom du H5P]]</b></li>
</ul>
<hr/>
Represents the separator character used in the filter. If the character is <b>/</b>, the filter will search for it in <b>[[i/activityname]]</b>. All indicators ( i/, c/, d/ ) must be at the begenning of double brackets [[.<br/>
<h4>Integration Code</h4>
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

 # Plus d'information / More informations
 
<ul>
	<li>https://recitfad.ca/moodledocs/ (only in Canada)</li>	
	<li>https://www.youtube.com/watch?v=FkpwLCFOUTU</li>
</ul>
   
 # Non-standard post-installation steps
 After installing this plugin, it is necessary to place it before the default Activity linking filter.