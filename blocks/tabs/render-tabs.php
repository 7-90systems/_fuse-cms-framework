<?php
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
    
    $tabs = array ();
    
    /**
     *  An empty tabs block has no inner content, and DOMDocument::loadHTML ()
     *  throws a ValueError when handed an empty string, so there is nothing to
     *  parse in that case.
     */
    if (is_string ($content) === false || trim ($content) === '') {
        $content = '';
    } // if ()
    
    if ($content !== '') {
        // Extract tab contents from the inner block content using DOM parsing
        $dom = new DOMDocument ();
        libxml_use_internal_errors (true);
        
        /**
         *  mb_convert_encoding () with 'HTML-ENTITIES' is deprecated as of PHP
         *  8.2. An XML encoding declaration tells libxml the input is UTF-8 and
         *  does the same job without it.
         */
        $dom->loadHTML ('<?xml encoding="UTF-8">'.$content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors ();
        
        foreach ($dom->getElementsByTagName ('div') as $div) {
            if ($div->hasAttribute ('class') && strpos ($div->getAttribute ('class'), 'fuse-tab') !== false) {
                $label = $div->getAttribute ('data-label');
                
                if (strlen ($label) > 0) {
                    $innerHTML = '';
                    
                    foreach ($div->childNodes as $child) {
                        $innerHTML.= $dom->saveHTML ($child);
                    } // foreach ()
                    
                    $tabs [] = array (
                        'label' => esc_html ($label),
                        'content' => $innerHTML
                    );
                } // if ()
            } // if ()
        } // foreach ()
    } // if ()
    
    /**
     *  Rendering starts here.
     */
    
    ob_start ();
    get_template_part ('templates/tabs/tabs', '', array (
        'tabs' => $tabs
    ));
    $html = ob_get_contents ();
    ob_end_clean ();
    
    if (strlen ($html) > 0) {
        echo $html;
    } // if ()
    else {
        $id = uniqid ();
        ?>
            <div class="fuse-tabs-container">
                
                <div class="fuse-tabs-nav">
                    <ul>
                        <?php foreach ($tabs as $index => $tab): ?>
                            <li>
                                <a href="#<?php echo esc_attr ('tab-'.$id.'-'.$index); ?>"><?php echo $tab ['label']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="fuse-tabs-list">
                    
                    <?php foreach ($tabs as $index => $tab): ?>
                        
                        <a href="#<?php echo esc_attr ('tab-'.$id.'-'.$index); ?>" class="fuse-tab-nav-mobile"><?php echo $tab ['label']; ?></a>
                        <div id="<?php echo esc_attr ('tab-'.$id.'-'.$index); ?>" class="fuse-tab">
                            <?php
                                echo $tab ['content'];
                            ?>
                        </div>
                    
                    <?php endforeach; ?>
                    
                </div>
                
            </div>
        <?php
    } // else