<?php
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
error_log ("Getting template!!");
    
    // Extract tab contents from the inner block content using DOM parsing
    $dom = new DOMDocument ();
    libxml_use_internal_errors (true);
    $dom->loadHTML (mb_convert_encoding ($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors ();
    
    $tabs = array ();
    
    foreach ($dom->getElementsByTagName ('div') as $div) {
        if ($div->hasAttribute ('class') && strpos ($div->getAttribute ('class'), 'fuse-tab') !== false) {
            $label = $div->getAttribute ('data-label');
            
            if (strlen ($label) > 0) {
                $innerHTML = '';
                
                foreach ($div->childNodes as $child) {
                    $innerHTML.= $dom->saveHTML ($child);
                } // foraech ()
                
                $tabs [] = array (
                    'label' => esc_html ($label),
                    'content' => $innerHTML
                );
            } // if ()
        }
    } // foreach ()
    
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
                                <a href="#<?php esc_attr_e ('tab-'.$id.'-'.$index); ?>"><?php echo $tab ['label']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="fuse-tabs-list">
                    
                    <?php foreach ($tabs as $index => $tab): ?>
                    
                        <a href="#<?php esc_attr_e ('tab-'.$id.'-'.$index); ?>" class="fuse-tab-nav-mobile"><?php echo $tab ['label']; ?></a>
                        <div id="<?php esc_attr_e ('tab-'.$id.'-'.$index); ?>" class="fuse-tab">
                            <?php
                                echo $tab ['content'];
                            ?>
                        </div>
                    
                    <?php endforeach; ?>
                    
                </div>
                
            </div>
        <?php
    } // else