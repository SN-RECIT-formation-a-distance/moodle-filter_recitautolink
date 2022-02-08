
# mp-filter-recitautolink (Français)
Ce filtre permet entre autres l'affichage de l'icône de l'acitvité ainsi qu'un crochet qui indique le statut d'une activité complétée. Il permet aussi de tirer profit de la base de données de Moodle et de créer une expérience plus personnalisée. Les exemples ci-dessous vous présentent les codes d'intégration disponibles et le résultat à l'écran de l'utilisateur.

[![Youtube video](https://img.youtube.com/vi/FkpwLCFOUTU/0.jpg)](https://www.youtube.com/watch?v=FkpwLCFOUTU)

## Lien vers l'activité
<img src='https://github.com/SN-RECIT-formation-a-distance/moodle-filter_recitautolink/blob/master/docs/filtre2.jpg' alt='Lien vers l'activité' width="500px"/>
L'exemple ci-joint présente l'affichage d'un lien vers une activité. La portion de gauche montre l'écran de l'apprenant. Du côté droit, on constate la présence du code d'intégration dans l'éditeur. Le paramètre i/ engendre l'affichage l'icône de l'activité, le paramètre c/ engendre la présence du crochet et le nom exact du titre de l'activité engendre la liaison vers celle-ci.

## Génération de l'avatar ou du nom de l'élève
<img src='https://github.com/SN-RECIT-formation-a-distance/moodle-filter_recitautolink/blob/master/docs/filtre1.jpg' alt='Génération de l'avatar ou du nom de l'élève' width="500px"/>
L'exemple ci-joint présente l'affichage du nom de l'élève dans une page. La portion de gauche montre l'écran de l'apprenant. Du côté droit, on constate la présence du code d'intégration dans l'éditeur. Le paramètre <b>[[d/user.firstname]]</b> engendre l'affichage du prénom de l'élève. L'information est tirée de la base de données.

# Informations techniques
Ceci repésente le caractère séparateur à utiliser dans le filtre. Si le caractère est <b>/</b>, la syntaxe sera la suivante <b>[[i/Nom de l'activité]]</b>. Tous les indicateurs ( i/, c/, d/, b/, s/ ) doivent être placés au début du double crochets ouverts [[.<br/>

## Code d'intégration
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

## Étapes non standard de la post-installation
Après avoir installé ce plugin, il est nécessaire de le placer avant le filtre par défaut des liens d'activité.
