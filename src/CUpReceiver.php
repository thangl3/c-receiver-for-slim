<?php
namespace LoveCoding\CReceiver;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Chunked file upload receiver class
 * Author: Thang Le (lethethangdn@gmail.com)
 * Create date: 2018/08/23
 * Last updated: 2018/08/24
 */
class CUpReceiver
{
    use EventListenerTrait;

	protected $indentifier;
	protected $fileName;
	protected $isCompleted;
	protected $chunkedNumber;
	protected $chunkedSize;
	protected $totalFileSize;
	protected $numberChunkedOfFile;
	protected $request;
	protected $response;

    protected $baseDirectorySaveFile;
    protected $baseDirectorySaveTemporaryFile;

	private $settings = [
		'parameters' => [
			'identifier' => 'identifier',
	        'fileName' => 'filename',
	        'chunkedNumber' => 'chunkedNumber',
	        'numberChunkedOfFile' => 'numberChunkedOfFile',
	        'chunkedSize' => 'chunkedSize',
	        'totalFileSize' => 'totalSize'
		],
		'baseDirectorySaveFile' => '',
		'baseDirectorySaveTemporaryFile' => '/temp',
		'debug' => false
	];

	/**
	 * Create and settings Uploader
	 * @param Request  $request  Recieve all of param from client
	 * @param Response $response Return response to client
	 * @param array    $settings optional for custom settings
	 */
	public function __construct(Request $request, Response $response, array $settings = [])
	{
		$this->request = $request;
		$this->response = $response;
		$this->addSettings($settings);
	}

	/********************************************************************************
     * Settings management
     *******************************************************************************/

    /**
     * Does uploader have a setting with given key?
     *
     * @param string $key
     * @return bool
     */
    public function hasSetting($key)
    {
        return isset($this->settings[$key]);
    }

	/**
     * Get all uploader's settings
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get uploader's setting
     *
     * @return mixed
     */
    public function getSetting($key)
    {
        if ($this->hasSetting($key)) {
        	return $this->settings[$key];
        }

        return null;
    }

	/**
     * Merge a key-value array with existing app settings
     *
     * @param array $settings
     */
    public function addSettings(array $settings)
    {
        $this->settings = @array_merge($this->settings, $settings);
    }


    /********************************************************************************
     * Set get properties of uploader
     *******************************************************************************/

    public function getSettingParameters()
    {
    	return $this->settings['parameters'];
    }

    public function hasSettingParameter($key)
    {
    	return isset($this->settings['parameters'][$key]);
    }

    public function getSettingParameter($key)
    {
    	if ($this->hasSettingParameter($key)) {
    		return $this->settings['parameters'][$key];
    	}
    	return null;
    }

    /********************************************************************************
     * Runner
     *******************************************************************************/
    
    /**
     * Main function - when user call function it will be automatic recieve and upload file from client
     * @return Response $response | return response to client
     */
    public function process()
    {
    	$this->parseRequestParameter();

        $this->baseDirectorySaveFile = $this->getSetting('baseDirectorySaveFile');
        $this->baseDirectorySaveTemporaryFile = $this->getSetting('baseDirectorySaveTemporaryFile');

        $uploadHandler = new UploadHandler(
            $this->baseDirectorySaveTemporaryFile,
            $this->baseDirectorySaveFile,
            $this->indentifier,
            $this->fileName,
            $this->numberChunkedOfFile
        );

        $this->callEventListener('onload');

        // handle multi upload
        foreach ($this->request->getUploadedFiles() as $uploadedFile) {
            $uploadHandler->receiveUploadChunked($uploadedFile, $this->chunkedNumber);

            $uploadHandler->addEventListener('onprogress', function() {
                $this->callEventListener('onprogress');
                $this->response = $this->response->withStatus(202);
            });

            $uploadHandler->addEventListener('onerror', function($error) {
                $this->callEventListener('onerror', $error);
                $this->response = $this->response->withStatus(500);
            });

            $uploadHandler->addEventListener('onfinished', function() {
                $this->callEventListener('onfinished');
                $this->response = $this->response->withStatus(200);
            });

            $uploadHandler->process();
        }

		return $this->response;
    }

    /**
     * Parse and set data have send from client
     * Using Http/ServerRequestInterface
     */
    private function parseRequestParameter()
    {
    	$paramIndentifier = $this->getSettingParameter('identifier');
    	$paramFileName = $this->getSettingParameter('fileName');
    	$paramChunkedNumber = $this->getSettingParameter('chunkedNumber');
    	$paramChunkedSize = $this->getSettingParameter('chunkedSize');
    	$paramTotalFileSize = $this->getSettingParameter('totalFileSize');
    	$paramTotalChunkOfFile = $this->getSettingParameter('numberChunkedOfFile');

    	$this->indentifier = $this->request->getParam($paramIndentifier);
    	$this->fileName = $this->request->getParam($paramFileName);
    	$this->chunkedNumber = $this->request->getParam($paramChunkedNumber);
    	$this->chunkedSize = $this->request->getParam($paramChunkedSize);
    	$this->totalFileSize = $this->request->getParam($paramTotalFileSize);
    	$this->numberChunkedOfFile = $this->request->getParam($paramTotalChunkOfFile);
    }
}