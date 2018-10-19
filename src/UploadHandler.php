<?php
namespace LoveCoding\CReceiver;

use FilesystemIterator;
use Exception;
use Psr\Http\Message\UploadedFileInterface;

class UploadHandler
{
    use EventListenerTrait;

	private $fileName;
	private $originalFileName;
	private $fileExtension;
	private $pathTemporaryDirectory;
    private $pathSaveFileDirectory;
    private $indentifier;
    private $numberChunkedOfFile;
    private $chunkedNumber;

    private $uploadIO;

    public function __construct($pathTemporaryDirectory, $pathSaveFileDirectory, $indentifier, $originalFileName, $numberChunkedOfFile)
    {
        $this->pathTemporaryDirectory = $pathTemporaryDirectory;
        $this->pathSaveFileDirectory = $pathSaveFileDirectory;
        $this->indentifier = $indentifier;
        $this->originalFileName = $originalFileName;
        $this->numberChunkedOfFile = $numberChunkedOfFile;

        $this->fileName = $this->removeFileExtension($originalFileName);
        $this->fileExtension = $this->getFileExtension($originalFileName);

        $this->uploadIO = new UploadIO();
    }

    /**
     * Get extension of file from original file name
     * @param  String $fileName full name of file
     * @return String           extension of file
     */
    public function getFileExtension($fileName)
    {
        $parts = @explode('.', @basename($fileName));

    	return @end($parts);
    }

    /**
     * Remove extension of file
     * @param  String $fileName Original file name
     * @return String           File name without extension
     */
    public function removeFileExtension($fileName)
    {
    	return @str_replace(sprintf('.%s', $this->getFileExtension($fileName)), '', $fileName);
    }

    /**
     * Generator path to file temporary
     * @param  Integer $partIndex Number of part has been uploaded
     * @return String             Path to file
     */
	public function pathTemporaryFileGenerator($partIndex)
    {
        return $this->getPathTemporaryDirectory() .'/' .$this->fileName .'_part_' .$partIndex;
    }

    public function pathSaveFileGenerator()
    {
        return $this->getPathSaveFileDirectory() .'/' .$this->originalFileName;
    }

    public function getPathTemporaryDirectory()
    {
        $dir = $this->pathTemporaryDirectory .'/' .$this->indentifier;

        return $dir;
    }

    public function getPathSaveFileDirectory()
    {
        return $this->pathSaveFileDirectory;
    }

    /**
     * Processing upload file
     */
    public function process()
    {
        try {
            $this->uploadIO->createFileFromChunked(
                $this->getPathSaveFileDirectory(),
                $this->pathSaveFileGenerator(),
                $this->pathTemporaryFileGenerator($this->chunkedNumber)
            );
        } catch(Exception $exception) {
            $this->callEventListener('onerror', $exception);
        }

        $numberFilesInTemporary = new FilesystemIterator(
            $this->getPathTemporaryDirectory(),
            FilesystemIterator::SKIP_DOTS
        );
        $totalChunked = iterator_count($numberFilesInTemporary);

		if ($this->numberChunkedOfFile == $totalChunked) {
			$this->uploadIO->removeFolder($this->getPathTemporaryDirectory());
            $this->callEventListener('onfinished');
		} else {
            $this->callEventListener('onprogress');
        }
	}

	/**
     * Receive chunked upload file from client
     * @param  UploadedFileInterface  $uploadedFile  Get path of file upload from client
     * @param  Integer                $chunkedNumber Number of part
     */
	public function receiveUploadChunked(UploadedFileInterface $uploadedFile, $chunkedNumber)
    {
        try {
        	$this->uploadIO->writeToTemporary(
        		$uploadedFile->file,
        		$this->getPathTemporaryDirectory(),
        		$this->pathTemporaryFileGenerator($chunkedNumber)
        	);
            $this->chunkedNumber = $chunkedNumber;
        } catch(Exception $exception) {
            $this->callEventListener('onerror', $exception);
        }
    }

    /**
     * The partial of file has been uploaded
     * @param  Integer  $chunkedNumber part of temporary files
     * @return Boolean                 true if chunked has been uploaded, else
     */
    public function isChunkedFileUploaded($chunkedNumber) {
    	return @is_uploaded_file($this->pathTemporaryFileGenerator($chunkedNumber));
    }

    /**
     * Check whever upload file is done?
     * @return boolean  True or False
     */
    public function isUploadedFile() {
    	return @is_uploaded_file($this->pathSaveFileGenerator());
    }
}