<?php
    /**
     *  Set up our map class.
     */
    
    namespace Fuse\Geo;
    
    use Fuse\Geo\Map\Point;
    
    
    class Map {
        
        /**
         *  @var array These are the points for the map.
         */
        protected $points = array ();
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param array $points An array of Fuse\Geo\Map\Points points for this map.
         */
        public function __construct ($points = array ()) {
            $this->addPoints ($points);
        } // __construct ()
        
        
        
        
        /**
         *  Add a set of points
         *
         *  @param array $points The points to add to this map.
         *
         *  @return Fuse\Geo]Map This map.
         */
        public function addPoints ($points) {
            foreach ($points as $point) {
                $this->addPoint ($point);
            } // foreach ()
            
            return $this;
        } // addPoints ()
        
        /**
         *  Add a single point to the map.
         *
         *  @param Fuse\Geo\Map\Point $point The point.
         *
         *  return Fuse\Geo]Map This map.
         */
        public function addPoint (Point $point) {
            $this->_points [] = $point;
            
            return $this;
        } // addPoint ()
        
        /**
         *  Clear the points from this map.
         *
         *  @return Fuse\Geo]Map This map.
         */
        public function clear () {
            $this->_points = array ();
            
            return $this;
        } // clear ()
        
        
        
        
        /**
         *  Get the points for this map.
         *
         *  @return array This returns the array of points set for this map.
         */
        public function getPoints () {
            return $this->_points ();
        } // getPoints ()
        
        
        
        
        /**
         *  Display our map code!
         */
        public function display () {
            $geo_key = get_fuse_option ('google_api_key', '');
            
            if (empty ($geo_key) === false && count ($this->_points) > 0) {
                $map_id = uniqid ('fuse_geo_map_');
                
                if (count ($this->_points) > 1) {
                    $this->_displayMultiPointMap ($geo_key, $map_id);
                } // if ()
                else {
                    $this->_displaySinglePointMap ($geo_key, $map_id);
                } // else
            } // if ()
        } // display ()
        
        /**
         *  Display a map with direction markers.
         */
        public function displayDirectionMap () {
            $geo_key = get_fuse_option ('google_api_key', '');
            
            if (empty ($geo_key) === false && count ($this->_points) > 0) {
                $map_id = uniqid ('fuse_geo_map_');
                
                $this->_displayDirectionMap ($geo_key, $map_id);
            } // if ()
        } // displayDirectionMap ()
        
        
        
        
        /**
         *  Show a map with a single point.
         *
         *  @param string $geo_key The Google API key.
         *  @param string $map_id The ID for the map area.
         */
        protected function _displaySinglePointMap ($geo_key, $map_id) {
            $map_zoom = get_option ('fuse_geo_zoom', 16);
            $point = $this->_points [0];
            
            ?>
                <div id="<?php echo esc_attr ($map_id); ?>" class="fuse-map-container" style="width: 100%; height: 100%;"></div>
                <script type="text/javascript">
                    function <?php echo $map_id; ?>_initMap () {
                        var marker_point = {
                            lat: <?php echo floatval ($point->lat); ?>,
                            lng: <?php echo floatval ($point->lng); ?>
                        };
                        
                        var map = new google.maps.Map (
                            document.getElementById ('<?php echo esc_js ($map_id); ?>'),
                            {
                                zoom: <?php echo intval ($map_zoom); ?>,
                                center: marker_point
                            }
                        );
                        
                        var marker = new google.maps.Marker ({
                            position: marker_point,
                            <?php if (strlen ($point->label) > 0): ?>
                                title: '<?php echo esc_js ($point->label); ?>',
                            <?php endif; ?>
                            map: map
                        });
                        
                        jQuery (document).trigger ('fuse_geo_maps_callback');
                    } // <?php echo $map_id; ?>_initMap ()
                </script>
                <script defer src="<?php echo esc_url ('https://maps.googleapis.com/maps/api/js?key='.$geo_key.'&callback='.$map_id.'_initMap'); ?>"></script>
            <?php
        } // _displaySinglePointMap ()
        
        /**
         *  Display a multi-point map
         */
        protected function _displayMultiPointMap ($geo_key, $map_id) {
            /**
             *  We start with these numbers as the opposite of the actual
             *  min/max values so we can get actual bounds set.
             */
            $bounds = array (
                'north' => -90,
                'south' => 90,
                'east' => -180,
                'west' => 180
            );
            
            foreach ($this->_points as $point) {
// echo "<p>'".$point->lat."' : '".$point->lng."'</p>";
                if ($point->lat > $bounds ['north']) {
                    $bounds ['north'] = $point->lat;
                } // if ()
                
                if ($point->lat < $bounds ['south']) {
                    $bounds ['south'] = $point->lat;
                } // if ()
                
                if ($point->lng > $bounds ['east']) {
                    $bounds ['east'] = $point->lng;
                } // if ()
                
                if ($point->lng < $bounds ['west']) {
                    $bounds ['west'] = $point->lng;
                } // if ()
            } // foreach ()
// \Fuse\Debug::dump ($bounds, 'Bounds');
            ?>
                <div id="<?php echo esc_attr ($map_id); ?>" class="fuse-map-container" style="width: 100%; height: 100%;"></div>
                <script type="text/javascript">
                    function <?php echo $map_id; ?>_initMap () {
                        var map = new google.maps.Map (document.getElementById ('<?php echo esc_js ($map_id); ?>'));
                        map.fitBounds (new google.maps.LatLngBounds (
                            {lat: <?php echo floatval ($bounds ['south']); ?>, lng: <?php echo floatval ($bounds ['west']); ?>},
                            {lat: <?php echo floatval ($bounds ['north']); ?>, lng: <?php echo floatval ($bounds ['east']); ?>}
                        ));
                        
                        var locations = [
                            <?php
                                $sep = '';
                                
                                foreach ($this->_points as $point) {
                                    echo $sep.'{lat: '.floatval ($point->lat).', lng: '.floatval ($point->lng).'}';
                                    
                                    $sep = ', ';
                                } // foreach ()
                            ?>
                        ];
                        
                        var labels = [
                            <?php
                                $sep = '';
                                
                                foreach ($this->_points as $point) {
                                    echo $sep.'"'.esc_js ($point->label).'"';
                                    
                                    $sep = ', ';
                                } // foreach ()
                            ?>
                        ];
                        
                        var markers = locations.map ((location, i) => {
                            return new google.maps.Marker ({
                                position: location,
                                title: labels [i],
                                map: map
                            });
                        });
                    } // <?php echo $map_id; ?>_initMap ()
                </script>
                <script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr ($geo_key); ?>&callback=<?php echo esc_attr ($map_id); ?>_initMap"></script>
            <?php
        } // _displayMultipointMap ()
        
        /**
         *  Display a map with directinal polylines.
         */
        protected function _displayDirectionMap ($geo_key, $map_id) {
             /**
             *  We start with these numbers as the opposite of the actual
             *  min/max values so we can get actual bounds set.
             */
            $bounds = array (
                'north' => -90,
                'south' => 90,
                'east' => -180,
                'west' => 180
            );
            
            foreach ($this->_points as $point) {
// echo "<p>'".$point->lat."' : '".$point->lng."'</p>";
                if ($point->lat > $bounds ['north']) {
                    $bounds ['north'] = $point->lat;
                } // if ()
                
                if ($point->lat < $bounds ['south']) {
                    $bounds ['south'] = $point->lat;
                } // if ()
                
                if ($point->lng > $bounds ['east']) {
                    $bounds ['east'] = $point->lng;
                } // if ()
                
                if ($point->lng < $bounds ['west']) {
                    $bounds ['west'] = $point->lng;
                } // if ()
            } // foreach ()
// \Fuse\Debug::dump ($bounds, 'Bounds');
            ?>
                <div id="<?php echo esc_attr ($map_id); ?>" class="fuse-map-container" style="width: 100%; height: 100%;"></div>
                <script type="text/javascript">
                    function <?php echo $map_id; ?>_initMap () {
                        var map = new google.maps.Map (document.getElementById ('<?php echo esc_js ($map_id); ?>'));
                        map.fitBounds (new google.maps.LatLngBounds (
                            {lat: <?php echo floatval ($bounds ['south']); ?>, lng: <?php echo floatval ($bounds ['west']); ?>},
                            {lat: <?php echo floatval ($bounds ['north']); ?>, lng: <?php echo floatval ($bounds ['east']); ?>}
                        ));
                        
                        var locations = [
                            <?php
                                $sep = '';
                                
                                foreach ($this->_points as $point) {
                                    echo $sep.'{lat: '.floatval ($point->lat).', lng: '.floatval ($point->lng).'}';
                                    
                                    $sep = ', ';
                                } // foreach ()
                            ?>
                        ];
                        
                        var labels = [
                            <?php
                                $sep = '';
                                
                                foreach ($this->_points as $point) {
                                    echo $sep.'"'.esc_js ($point->label).'"';
                                    
                                    $sep = ', ';
                                } // foreach ()
                            ?>
                        ];
                        
                        var markers = locations.map ((location, i) => {
                            return new google.maps.Marker ({
                                position: location,
                                title: labels [i],
                                map: map
                            });
                        });
                        
                        var map_path = new google.maps.Polyline ({
                            path: [
                                <?php
                                    $sep = '';
                                    
                                    foreach ($this->_points as $point) {
                                        echo $sep.'{lat: '.floatval ($point->lat).', lng: '.floatval ($point->lng).'}';
                                        $sep = ', ';
                                    } // foreach ()
                                ?>
                            ],
                            geodesic: true,
                            strokeColor: '#CC0000',
                            strokeOpacity: 1.0,
                            strokeWeight: 1,
                            icons: [
                                {
                                    icon: {
                                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW
                                    },
                                    offset: '50%'
                                }
                            ]
                        });
                        
                        map_path.setMap (map);
                    } // <?php echo $map_id; ?>_initMap ()
                </script>
                <script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr ($geo_key); ?>&callback=<?php echo esc_attr ($map_id); ?>_initMap"></script>
            <?php
        } // _displayDirectionMap ()
        
    } // class Map