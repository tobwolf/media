<?php
namespace TYPO3\CMS\Media\ViewHelpers\Render;
/***************************************************************
*  Copyright notice
*
*  (c) 2012
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * View helper for rendering configuration that will be consumed by Javascript
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  media
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class ColumnsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render the columns of the grid
	 *
	 * @return string
	 */
	public function render() {

		$output = '';

		/** @var $grid  \TYPO3\CMS\Media\Service\Grid */
		$grid = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Media\Service\Grid');

		foreach($grid->getColumns() as $column) {
			$output .= sprintf('Media._columns.push({ "mData": "%s", "bSortable": %s, "bVisible": %s });' . chr(10),
				$column['field'],
				isset($column['sortable']) && !$column['sortable']? 'false' : 'true',
				isset($column['visible']) && !$column['visible']? 'false' : 'true'
			);
		}

		return $output;
	}

}

?>