<?php

    /**
     * Unsplash
     * 
     * PHP wrapper for Unsplash
     * 
     * @author Oliver Nassar <onassar@gmail.com>
     * @see    https://github.com/onassar/PHP-Unsplash
     * @see    https://unsplash.com/developer/
     */
    class Unsplash
    {
        /**
         * _appId
         * 
         * @var    false|string (default: false)
         * @access protected
         */
        protected $_appId = false;

        /**
         * _associative
         * 
         * @var    boolean
         * @access protected
         */
        protected $_associative;

        /**
         * _base
         * 
         * @var    string
         * @access protected
         */
        protected $_base = 'https://api.unsplash.com/search/photos';

        /**
         * _limits
         * 
         * @var    null|array
         * @access protected
         */
        protected $_limits = null;

        /**
         * _page
         * 
         * @var    string (default: '1')
         * @access protected
         */
        protected $_page = '1';

        /**
         * _photosPerPage
         * 
         * @var    string (default: '20')
         * @access protected
         */
        protected $_photosPerPage = '20';

        /**
         * __construct
         * 
         * @access public
         * @param  string $appId
         * @param  boolean $associative (default: true)
         * @return void
         */
        public function __construct($appId, $associative = true)
        {
            $this->_appId = $appId;
            $this->_associative = $associative;
        }

        /**
         * _get
         * 
         * @access protected
         * @param  array $args
         * @return false|array|stdClass
         */
        protected function _get(array $args)
        {
            // Auth
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'ignore_errors' => true,
                    'header' => 'Authorization: Client-ID ' . ($this->_appId)
                )
            ));

            // Build the query
            $path = http_build_query($args);
            $url = ($this->_base) . '?' . ($path);
            $response = file_get_contents($url, false, $context);
            $headers = $http_response_header;
            $this->_limits = $this->_getRateLimits($http_response_header);

            // Attempt request; fail with false if it bails
            json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_decode(
                    $response,
                    $this->_associative
                );
            }

            // Fail
            error_log('Unsplash:    failed response');
            return false;
        }

        /**
         * _getRateLimits
         * 
         * @see    http://php.net/manual/en/reserved.variables.httpresponseheader.php
         * @access protected
         * @param  array $http_response_header
         * @return array
         */
        protected function _getRateLimits(array $http_response_header)
        {
            $headers = $http_response_header;
            $formatted = array();
            foreach ($headers as $header) {
                $pieces = explode(':', $header);
                if (count($pieces) >= 2) {
                    $formatted[$pieces[0]] = $pieces[1];
                }
            }
            $limits = array(
                'remaining' => false,
                'limit' => false,
                'reset' => false
            );
            if (isset($formatted['X-Ratelimit-Remaining']) === true) {
                $limits['remaining'] = (int) trim($formatted['X-Ratelimit-Remaining']);
            }
            if (isset($formatted['X-Ratelimit-Limit']) === true) {
                $limits['limit'] = (int) trim($formatted['X-Ratelimit-Limit']);
            }
            if (isset($formatted['X-Ratelimit-Reset']) === true) {
                $limits['reset'] = (int) trim($formatted['X-Ratelimit-Reset']);
            }
            return $limits;
        }

        /**
         * getLimits
         * 
         * @access public
         * @return null|array
         */
        public function getLimits()
        {
            return $this->_limits;
        }

        /**
         * query
         * 
         * @access public
         * @param  string $query
         * @param  array $args (default: array())
         * @return false|array|stdClass
         */
        public function query($query, array $args = array())
        {
            $args = array_merge(
                array(
                    'query' => $query,
                    'page' => $this->_page,
                    'per_page' => $this->_photosPerPage
                ),
                $args
            );
            $response = $this->_get($args);
            if ($response === false) {
                return false;
            }

            // Add original query
            if ($this->_associative === true) {
                foreach ($response['results'] as $index => $hit) {
                    $response['results'][$index]['original_query'] = $query;
                }
            } else {
                foreach ($response->results as $index => $hit) {
                    $response->results[$index]->original_query = $query;
                }
            }
            return $response;
        }

        /**
         * setPage
         * 
         * @access public
         * @param  string $page
         * @return void
         */
        public function setPage($page)
        {
            $this->_page = $page;
        }

        /**
         * setPhotosPerPage
         * 
         * @access public
         * @param  string $photosPerPage
         * @return void
         */
        public function setPhotosPerPage($photosPerPage)
        {
            $this->_photosPerPage = $photosPerPage;
        }
    }
