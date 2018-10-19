<?php

namespace LoveCoding\CReceiver;

class UploadIO {

    /**
     * Move file upload from client to temporary
     * 
     * @param  String $uploadedFilePath       Default php will save file at temp folder in system
     *                                        $uploadedFilePath is the path to temp folder of system
     *                                        Ex: C://php/temp/XXXXX
     * @param  String $pathTemporaryDirectory Path to directory contains chunked files
     * @param  String $pathTemporaryFile      Path full as text of files temporary name
     * @return Boolean                        Check move from temp in system to custom temp folder of
     *                                        user will be OK?
     */
    public function writeToTemporary($uploadedFilePath, $pathTemporaryDirectory, $pathTemporaryFile)
    {
        UploadHelper::createMultipleFolder($pathTemporaryDirectory);

        move_uploaded_file($uploadedFilePath, $pathTemporaryFile);
    }

    /**
     * Process create file from chunks have been uploaded
     * Append data content to a exist file
     * @param  String $pathSaveDirectory [description]
     * @param  String $pathSaveFile      [description]
     * @param  String $pathTemporaryFile [description]
     */
    public function createFileFromChunked($pathSaveDirectory, $pathSaveFile, $pathTemporaryFile)
    {
        UploadHelper::createMultipleFolder($pathSaveDirectory);
        UploadHelper::checkWritePermissionDirectory($pathSaveDirectory);

        $fwSaveFile = fopen($pathSaveFile, 'a+');
        fwrite( $fwSaveFile, file_get_contents($pathTemporaryFile) );
        fclose($fwSaveFile);
    }

    /**
     * Delete file
     *
     * @param string $file
     */
    public function deleteFile($file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function removeFolder($pathToFolder) {
        UploadHelper::removeFolder($pathToFolder);
    }
}