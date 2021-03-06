<?php
namespace TYPO3\CMS\Media\FileUpload\Optimizer;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Media\FileUpload\ImageOptimizerInterface;
use TYPO3\CMS\Media\Utility\StorageUtility;

/**
 * Class that optimize an image according to some settings.
 */
class Resize implements ImageOptimizerInterface {

	/**
	 * @var \TYPO3\CMS\Frontend\Imaging\GifBuilder
	 */
	protected $gifCreator;

	/**
	 * @var \TYPO3\CMS\Core\Resource\ResourceStorage
	 */
	protected $storage;

	/**
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storage
	 * @return Resize
	 */
	public function __construct($storage = NULL) {
		$this->storage = $storage;
		$this->gifCreator = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Imaging\\GifBuilder');
		$this->gifCreator->init();
		$this->gifCreator->absPrefix = PATH_site;
	}

	/**
	 * Optimize the given uploaded image.
	 *
	 * @param \TYPO3\CMS\Media\FileUpload\UploadedFileInterface $uploadedFile
	 * @return \TYPO3\CMS\Media\FileUpload\UploadedFileInterface
	 */
	public function optimize($uploadedFile) {

		$imageInfo = getimagesize($uploadedFile->getFileWithAbsolutePath());

		$currentWidth = $imageInfo[0];
		$currentHeight = $imageInfo[1];

		// resize an image if this one is bigger than telling by the settings.
		if (is_object($this->storage)) {
			$storageRecord = $this->storage->getStorageRecord();
		} else {
			// Will only work in the BE for now.
			$storage = StorageUtility::getInstance()->getCurrentStorage();
			$storageRecord = $storage->getStorageRecord();
		}

		if (strlen($storageRecord['maximum_dimension_original_image']) > 0) {

			/** @var \TYPO3\CMS\Media\Dimension $imageDimension */
			$imageDimension = GeneralUtility::makeInstance('TYPO3\CMS\Media\Dimension', $storageRecord['maximum_dimension_original_image']);
			if ($currentWidth > $imageDimension->getWidth() || $currentHeight > $imageDimension->getHeight()) {

				// resize taking the width as reference
				$this->resize($uploadedFile->getFileWithAbsolutePath(), $imageDimension->getWidth(), $imageDimension->getHeight());
			}
		}
		return $uploadedFile;
	}

	/**
	 * Resize an image according to given parameter.
	 *
	 * @throws \Exception
	 * @param string $fileNameAndPath
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	public function resize($fileNameAndPath, $width = 0, $height = 0) {

		// Skip profile of the image
		$imParams = '###SkipStripProfile###';
		$options = array(
			'maxW' => $width,
			'maxH' => $height,
		);

		$tempFileInfo = $this->gifCreator->imageMagickConvert($fileNameAndPath, '', '', '', $imParams, '', $options, TRUE);
		if ($tempFileInfo) {

			// Overwrite original file
			@unlink($fileNameAndPath);
			@rename($tempFileInfo[3], $fileNameAndPath);
		}
	}

	/**
	 * Escapes a file name so it can safely be used on the command line.
	 *
	 * @see \TYPO3\CMS\Core\Imaging\GraphicalFunctions
	 * @param string $inputName filename to safeguard, must not be empty
	 * @return string $inputName escaped as needed
	 */
	protected function wrapFileName($inputName) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem']) {
			$currentLocale = setlocale(LC_CTYPE, 0);
			setlocale(LC_CTYPE, $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale']);
		}
		$escapedInputName = escapeshellarg($inputName);
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem']) {
			setlocale(LC_CTYPE, $currentLocale);
		}
		return $escapedInputName;
	}
}
