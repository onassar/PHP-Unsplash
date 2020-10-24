<?php

    // Namespace overhead
    namespace onassar\Unsplash;
    use onassar\RemoteRequests;
    use onassar\RiskyClosure;

    /**
     * Unsplash
     * 
     * PHP wrapper for Unsplash.
     * 
     * @link    https://github.com/onassar/PHP-Unsplash
     * @link    https://unsplash.com/developer/
     * @link    https://unsplash.com/documentation
     * @author  Oliver Nassar <onassar@gmail.com>
     * @extends RemoteRequests\Base
     */
    class Base extends RemoteRequests\Base
    {
        /**
         * Traits
         * 
         */
        use RemoteRequests\Traits\Pagination;
        use RemoteRequests\Traits\RateLimits;
        use RemoteRequests\Traits\SearchAPI;

        /**
         * _host
         * 
         * @access  protected
         * @var     string (default: 'api.unsplash.com')
         */
        protected $_host = 'api.unsplash.com';

        /**
         * _paths
         * 
         * @access  protected
         * @var     array
         */
        protected $_paths = array(
            'search' => '/search/photos',
            'trackDownload' => '/photos/:id/download'
        );

        /**
         * __construct
         * 
         * @link    https://unsplash.com/documentation#pagination
         * @see     https://i.imgur.com/g2Vafzf.png
         * @access  public
         * @return  void
         */
        public function __construct()
        {
            $this->_maxResultsPerRequest = 30;
            $this->_responseResultsIndex = 'results';
        }

        /**
         * _attempt
         * 
         * @access  protected
         * @param   \Closure $closure
         * @return  null|string
         */
        protected function _attempt(\Closure $closure): ?string
        {
            $callback = array($this, 'validFailedAttemptLog');
            $this->setFailedAttemptLoggingEvaluator($callback);
            $response = parent::_attempt($closure);
            return $response;
        }

        /**
         * _getAuthorizationHeader
         * 
         * @access  protected
         * @return  string
         */
        protected function _getAuthorizationHeader(): string
        {
            $apiKey = $this->_apiKey;
            $header = 'Authorization: Client-ID ' . ($apiKey);
            return $header;
        }

        /**
         * _getCURLRequestHeaders
         * 
         * @access  protected
         * @return  array
         */
        protected function _getCURLRequestHeaders(): array
        {
            $headers = parent::_getCURLRequestHeaders();
            $header = $this->_getAuthorizationHeader();
            array_push($headers, $header);
            return $headers;
        }

        /**
         * _getRateLimitResetValue
         * 
         * Unsplash resets their quotas (at least, during development mode)
         * every hour.
         * 
         * @access  protected
         * @param   
         * @return  null|int|string
         */
        protected function _getRateLimitResetValue()
        {
            $timestamp = time();
            $timestamp = ($timestamp) + (60 * 60);
            $timestamp = $this->_roundToLower($timestamp, 3600);
            return $timestamp;
        }

        /**
         * _getRequestStreamContextOptions
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRequestStreamContextOptions(): array
        {
            $options = parent::_getRequestStreamContextOptions();
            $header = $this->_getAuthorizationHeader();
            $options['http']['header'] = $header;
            return $options;
        }

        /**
         * _getTrackDownloadRequestURL
         * 
         * @access  protected
         * @param   string $photoId
         * @return  string
         */
        protected function _getTrackDownloadRequestURL(string $photoId): string
        {
            $host = $this->_host;
            $path = $this->_paths['trackDownload'];
            $path = str_replace(':id', $photoId, $path);
            $url = 'https://' . ($host) . ($path);
            return $url;
        }

        /**
         * _setTrackDownloadRequestURL
         * 
         * @access  protected
         * @param   string $photoId
         * @return  void
         */
        protected function _setTrackDownloadRequestURL(string $photoId): void
        {
            $trackDownloadURL = $this->_getTrackDownloadRequestURL($photoId);
            $this->setURL($trackDownloadURL);
        }

        /**
         * trackDownload
         * 
         * @access  public
         * @param   string $photoId
         * @return  bool
         */
        public function trackDownload(string $photoId): bool
        {
            $this->_setTrackDownloadRequestURL($photoId);
            $response = $this->_getURLResponse() ?? false;
            if ($response === false) {
                return false;
            }
            return true;
        }

        /**
         * validFailedAttemptLog
         * 
         * @access  public
         * @param   RiskyClosure\Base $riskyClosure
         * @return  bool
         */
        public function validFailedAttemptLog(RiskyClosure\Base $riskyClosure): bool
        {
            $maxAttempts = $riskyClosure->getMaxAttempts();
            if ($maxAttempts === 1) {
                return true;
            }
            $currentAttempt = $riskyClosure->getCurrentAttempt();
            if ($currentAttempt === 1) {
                return false;
            }
            return true;
        }
    }
