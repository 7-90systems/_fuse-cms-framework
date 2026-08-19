<?php
    /**
     *  This class is used to geo-encode an address.
     */
    
    namespace Fuse\Geo;
    
    use Fuse\Geo\Map\Point;
    
    
    class Encode {
        
        /**
         *  @var string The address that we want to encode.
         */
        protected $_address;
        
        /**
         *  @var Fuse\Geo\Map\Point The map point for the current address if set.
         */
        protected $_point;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $address The address to encode.
         */
        public function __construct ($address = '') {
            $this->setAddress ($address);
        } // __construct ()
        
        
        
        
        /**
         *  Set the address. Note that this does not generate the map point now. This is only done as required.
         *
         *  @param string $address The address to set.
         *
         *  @return Fuse\Geo\Encode This object.
         */
        public function setAddress ($address) {
            $this->_address = $address;
            $this->_point = NULL;
            
            return $this;
        } // setAddress ()
        
        
        
        
        /**
         *  Get the map point for the current address.
         *
         *  @param string $address Set an address here to over-ride the current address and force point generation.
         *
         *  @return Fuse\Geo\Map\Point|false The mao point or false if an error has occured.
         */
        public function getPoint ($address = NULL) {
            $point = false;
            
            if (empty ($address) === false) {
                $this->setAddress ($address);
            } // if ()
            
            if (empty ($this->_point) === false) {
                $point = $this->_point;
            } // if ()
            else {
                $point = $this->_encode ();
            } // else
            
            return $point;
        } // getPoint ()
        
        
        
        
        /**
         *  Encode the current address to get the map point;
         *
         *  @return Fuse\Geo\Map\Point|false The mao point or false if an error has occured.
         */
        protected function _encode () {
            $point = false;
            
            $geo_key = get_fuse_option ('google_api_key', '');
            
            if (empty ($geo_key) === false) {
                // Check that we have an address est!
                if (strlen ($this->_address) > 0) {
                    $this->_point = NULL;
                    
                    /**
                     *  Geocoding the same address over and over is both slow
                     *  and billable, so the answer is cached for a day.
                     */
                    $cache_key = 'fuse_geocode_'.md5 ($this->_address);
                    $cached = get_transient ($cache_key);
                    
                    if (is_array ($cached) && array_key_exists ('lat', $cached) && array_key_exists ('lng', $cached)) {
                        $this->_point = new Point ($cached ['lat'], $cached ['lng']);
                        
                        return $this->_point;
                    } // if ()
                    
                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode ($this->_address).'&key='.urlencode ($geo_key);
                    
                    /**
                     *  This used to call cURL directly, with no timeout and no
                     *  error handling at all -- a failed or refused request
                     *  came back as null and then fatally errored on count ().
                     */
                    $response = wp_remote_get ($url, array (
                        'timeout' => 15
                    ));
                    
                    if (is_wp_error ($response) || wp_remote_retrieve_response_code ($response) != 200) {
                        return false;
                    } // if ()
                    
                    $result = json_decode (wp_remote_retrieve_body ($response));
                    
                    if (is_object ($result) === false || isset ($result->results) === false || is_array ($result->results) === false) {
                        return false;
                    } // if ()
                    
                    if (count ($result->results) > 0) {
                        $record = $result->results [0];
                        
                        if (isset ($record->geometry->location->lat, $record->geometry->location->lng) === false) {
                            return false;
                        } // if ()
                        
                        $geometry = $record->geometry->location;
                        
                        $point = new Point ($geometry->lat, $geometry->lng);
                        $this->_point = $point;
                        
                        set_transient ($cache_key, array (
                            'lat' => $geometry->lat,
                            'lng' => $geometry->lng
                        ), DAY_IN_SECONDS);
                    } // if ()
                } // if ()
            } // if ()
            else {
                throw new \Exception (__ ('Google API key not defined.', 'fuse'));
            } // else
            
            return $point;
        } // _encode ()
        
    } // class Encode