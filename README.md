# mp-filter-recitautolink

 Ce filtre permet entre autres l'affichage de l'icône de l'acitvité ainsi qu'un crochet qui indique le statut d'une activité complétée. Il permet aussi de tirer profit de la base de données de Moodle et de créer une expérience plus personnalisée. Les exemples ci-dessous vous présentent les codes d'intégration disponibles et le résultat à l'écran de l'utilisateur.
 
 This filter allows, among other things, the display of the activity icon as well as a check mark indicating the status of a completed activity. It also allows you to take advantage of Moodle's database and create a more personalized experience. The examples below show you the available integration codes and the result on the user's screen.
 
# Utilisation/ Use
Ceci repésente le caractère séparateur à utiliser dans le filtre.
Si le caractère est /, la syntaxe sera la suivante [[i/Nom de l'activité]].
Tous les indicateurs ( i/, c/, d/, b/, s/ ) doivent être placés au début du double crochets ouverts [[.
Code d'intégration
Lien vers une activité : [[Nom de l'activité]]
Lien vers une activité avec icône : [[i/Nom de l'activité]]
Lien vers une activité avec une case à cocher pour la complétion : [[c/Nom de l'activité]]
Lien vers une activité avec icône et une case à cocher pour la complétion : [[i/c/Nom de l'activité]]
Changer le nom du lien : [[/i/c/desc:"Nom"/Nom de l'activité]]
Mettre des classes CSS par example pour faire un bouton : [[/i/c/class:"btn btn-primary"/Nom de l'activité]]
Ouvrir le lien vers une activité dans une autre onglet : [[c/b/Nom de l'activité]] ou [[i/c/b/Nom de l'activité]]
Lien vers une section : [[s/Nom de la section]] ou [[s//6]] pour se diriger vers la section 6 si son nom n'est pas personnalisé (pas utilisable en mode édition).
Exemble de lien vers une section : [[s/class:"btn btn-primary"/NOM_DE_LA_SECTION_ICI]]
Informations pour les noms du cours : [[d/course.fullname]], [[d/course.shortname]]
Informations de l'élève, prénom, nom, courriel et avatar : [[d/user.firstname]], [[d/user.lastname]], [[d/user.email]] et [[d/user.picture]]
Informations pour le premier professeur, prénom, nom, courriel et avatar : [[d/teacher1.firstname]], [[d/teacher1.lastname]], [[d/teacher1.email]] et [[d/teacher1.picture]]. Le professeur doit être inscrit dans le groupe pour que l'affichage de son nom apparaisse.
Pour les autres professeurs du cours, ils sont numérotés teacher2, teacher3, ...

Represents the separator character used in the filter. If the character is /, the filter will search for it in [[i/activityname]].
All indicators ( i/, c/, d/ ) must be at the begenning of double brackets [[.
Integration code
	Activity name link : [[activityname]]
	Activity name link with icon : [[<b style="color:red">i/</b>activityname]]
	Activity name link with completion checkbox : [[<b style="color:red">c/</b>activityname]]
    Activity name link with icon and completion checkbox : [[<b style="color:red">i/c/</b>activityname]]
    Change link name : [[/i/c/desc:"Name"/]]activityname
    Add CSS classes : [[/i/c/class:"btn btn-primary"/]]
    Open the link to an activity in another tab : [[<b style="color:red">c/b/</b>activityname]] ou [[<b style="color:red">i/c/b/</b>activityname]]
     Link to a section: [[<b style="color: red">s/</b>sectionname]] or [[<b style="color: red">s/</b>/6]] to go to section 6 if its name is not personalized (not usable in edit mode).
	Course informations : [[<b style="color:red">d/</b>course.fullname]], [[<b style="color:red">d/</b>course.shortname]]
	Student firstname, lastname, email and avatar : [[<b style="color:red">d/</b>user.firstname]], [[<b style="color:red">d/</b>user.lastname]], [[<b style="color:red">d/</b>user.email]] and [[<b style="color:red">d/</b>user.picture]]
	First teacher firstname, lastname, email and avatar : [[<b style="color:red">d/</b>teacher1.firstname]], [[<b style="color:red">d/</b>teacher1.lastname]], [[<b style="color:red">d/</b>teacher1.email]] and [[<b style="color:red">d/</b>teacher1.picture]]. The teacher must be in the group for his name to appear.
    Same for teacher2, teacher3, ... for all teachers for that course.
    Link to H5P content: [[<b style="color:red">h5p/</b>Name of H5P]]
    
 #Plus d'information / More informations
  https://recitfad.ca/moodledocs/
    
