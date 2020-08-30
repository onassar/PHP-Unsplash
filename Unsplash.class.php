<?php

    // Namespace overhead
    namespace onassar\Unsplash;
    use onassar\RemoteRequests;

    /**
     * Unsplash
     * 
     * PHP wrapper for Unsplash
     * 
     * @link    https://github.com/onassar/PHP-Unsplash
     * @link    https://unsplash.com/developer/
     * @link    https://unsplash.com/documentation
     * @author  Oliver Nassar <onassar@gmail.com>
     * @extends RemoteRequests\Base
     */
    class Unsplash extends RemoteRequests\Base
    {
        /**
         * RemoteRequets\Pagination
         * 
         */
        use RemoteRequests\Pagination;

        /**
         * RemoteRequets\RateLimits
         * 
         */
        use RemoteRequests\RateLimits;

        /**
         * RemoteRequets\SearchAPI
         * 
         */
        use RemoteRequests\SearchAPI;

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
            'search' => '/search/photos'
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
            $this->setMaxPerPage(30);
            $this->_responseResultsIndex = 'results';
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
         * @return  null|int
         */
        protected function _getRateLimitResetValue(): ?int
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
         * download
         * 
         * @access  public
         * @param   string $photoId
         * @return  bool
         */
        public function download(string $photoId): bool
        {
        }
    }
