<?php
/* Copyright (C) 2014 Alexis Algoud        <support@atm-conuslting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       /scrumboard/migration.php
 *	\ingroup    projet
 *	\brief      Project card
 */

//require('config.php');
dol_include_once('/scrumboard/lib/scrumboard.lib.php');

/**
 * Actions
 */
global $db;

$error = 0;
$TData = getData();

foreach($TData as $fk_project => $stories) {
	$TStorieLabel = explode(',', $stories);

	foreach($TStorieLabel as $k => $storie_label) {
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'projet_storie(fk_projet, storie_order, label)';
		$sql .= ' VALUES('.$fk_project.', '.($k+1).', "'.ltrim($storie_label).'")';

		$resql = $db->query($sql);
		if(! $resql) $error++;
	}
}

if(empty($error)) {
	$extrafields=new ExtraFields($db);
	$extrafields->delete('stories', 'projet');
}

function getData() {
	global $db;

	// Vérifie si la colonne "stories" a été supprimée, car la 2e requête dépend de cette colonne
	$extrafields=new ExtraFields($db);
	$extralabels = $extrafields->fetch_name_optionals_label('projet');
	if(empty($extralabels['stories'])) {
		return array();
	}

	$sql = 'SELECT fk_object, stories';
	$sql .= ' FROM '.MAIN_DB_PREFIX.'projet_extrafields';
	$sql .= ' WHERE stories IS NOT NULL';

	$resql = $db->query($sql);

	$TData = array();
	if($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$TData[$obj->fk_object] = $obj->stories;
		}
	}

	return $TData;
}