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
 * @copyright  2019 RECIT
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */

$string['filtername'] = "Liens automatiques du RÉCIT";
$string['pluginname'] = "Liens automatiques du RÉCIT";
$string['noteacheringroup'] = "Enseignant non défini";
$string['recitactivity:teacher'] = "Peut afficher le nom de l'enseignant";
$string['privacy:metadata'] = 'Le plugin "Liens automatiques du RÉCIT" ne conserve aucune donnée personelle.';
$string['teacherbygroup'] = 'Afficher les professeurs par groupe seulement';
$string['teacherbygroup_desc'] = 'Si ceci est à off, d/teacher va afficher tous les professeurs du cours.';
$string['character'] = 'Caractère servant de séparateur';
$string['character_desc'] = 'Ceci repésente le caractère séparateur à utiliser dans le filtre.
    <br>Si le caractère est <b style="color:red">/</b>, la syntaxe sera la suivante [[i<b style="color:red">/</b>Nom de l\'activité]].
	<br>Tous les indicateurs (<b style="color:red"> i/, c/, d/, b/, s/ </b>) doivent être placés au début du double crochets ouverts <b style="color:red">[[</b>.
    <br><br><b>Code d\'intégration</b>
    <ul>
	<li>Lien vers une activité : [[Nom de l\'activité]]</li>
	<li>Lien vers une activité avec icône : [[<b style="color:red">i/</b>Nom de l\'activité]]</li>
	<li>Lien vers une activité avec une case à cocher pour la complétion : [[<b style="color:red">c/</b>Nom de l\'activité]]</li>
    <li>Lien vers une activité avec icône et une case à cocher pour la complétion : [[<b style="color:red">i/c/</b>Nom de l\'activité]]</li>
    <li>Changer le nom du lien : [[/i/c/desc:"Nom"/</b>Nom de l\'activité]]</li>
    <li>Mettre des classes CSS par example pour faire un bouton : [[/i/c/class:"btn btn-primary"/Nom de l\'activité]]</li>
    <li>Ouvrir le lien vers une activité dans une autre onglet : [[<b style="color:red">c/b/</b>Nom de l\'activité]] ou [[<b style="color:red">i/c/b/</b>Nom de l\'activité]]</li>
    <li>Lien vers une section : [[<b style="color:red">s/</b>Nom de la section]] ou [[<b style="color:red">s/</b>/6]] pour se diriger vers la section 6 si son nom n\'est pas personnalisé (pas utilisable en mode édition).</li>
	<li>Informations pour les noms du cours : [[<b style="color:red">d/</b>course.fullname]], [[<b style="color:red">d/</b>course.shortname]]</li>
	<li>Informations de l\'élève, prénom, nom, courriel et avatar : [[<b style="color:red">d/</b>user.firstname]], [[<b style="color:red">d/</b>user.lastname]], [[<b style="color:red">d/</b>user.email]] et [[<b style="color:red">d/</b>user.picture]]</li>
	<li>Informations pour le premier professeur, prénom, nom, courriel et avatar : [[<b style="color:red">d/</b>teacher1.firstname]], [[<b style="color:red">d/</b>teacher1.lastname]], [[<b style="color:red">d/</b>teacher1.email]] et [[<b style="color:red">d/</b>teacher1.picture]]. Le professeur doit être inscrit dans le groupe pour que l\'affichage de son nom apparaisse.</li>
    <li>Pour les autres professeurs du cours, ils sont numérotés teacher2, teacher3, ...</li>
    <li>Lien vers un contenu H5P : [[<b style="color:red">h5p/</b>Nom du H5P]]</li>
    </ul>
	';

    $string['dismissMsg'] = 'Ne plus afficher';
    