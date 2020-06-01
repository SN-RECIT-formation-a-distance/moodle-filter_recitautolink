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

$string['filtername'] = "Liens automatiques améliorés du RÉCIT";
$string['privacy:metadata'] = 'Le plugin "Liens automatiques améliorés du RÉCIT" ne conserve aucune donnée.';
$string['character'] = 'Caractère servant de séparateur';
$string['character_desc'] = 'Ceci repésente le caractère séparateur à utiliser dans le filtre . Si le caractère est <b style="color:red">/</b>, la syntaxe sera la suivante [[i<b style="color:red">/</b>Nom de l\'activité]].
	<br>Tous les indicateurs (<b style="color:red"> i/, c/, d/ </b>) doivent être placés au début du double crochets ouverts <b style="color:red">[[</b>.
	<br><b>Code d\'intégration</b>
	<br>Lien vers une activité : [[Nom de l\'activité]]
	<br>Lien vers une activité avec icône : [[<b style="color:red">i/</b>Nom de l\'activité]]
	<br>Lien vers une activité avec une case à cocher pour la complétion : [[<b style="color:red">c/</b>Nom de l\'activité]]
	<br>Lien vers une activité avec icône et une case à cocher pour la complétion : [[<b style="color:red">i/c/</b>Nom de l\'activité]]
	<br>Informations pour les noms du cours : [[<b style="color:red">d/</b>course.fullname]], [[<b style="color:red">d/</b>course.shortname]]
	<br>Informations de l\'élève, prénom, nom, courriel et avatar : [[<b style="color:red">d/</b>user.firstname]], [[<b style="color:red">d/</b>user.lastname]], [[<b style="color:red">d/</b>user.email]] et [[<b style="color:red">d/</b>user.picture]]
	<br>Informations pour le premier professeur, prénom, nom, courriel et avatar : [[<b style="color:red">d/</b>teacher1.firstname]], [[<b style="color:red">d/</b>teacher1.lastname]], [[<b style="color:red">d/</b>teacher1.email]] et [[<b style="color:red">d/</b>teacher1.picture]]
	<br>Pour les autres professeurs du cours, ils sont numérotés teacher2, teacher3, ...
	';