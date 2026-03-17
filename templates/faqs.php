<?php
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
?>
<?php if (count ($questions) > 0): ?>

    <div class="fuse-faqs-container">
        
        <?php foreach ($questions AS $faq): ?>
                
            <div class="fuse-faq-container">
                <h6 class="fuse-faq-question"><?php echo $faq->post_title; ?></h6>
                <div class="fuse-faq-answer">
                    <?php
                        echo apply_filters ('the_title', $faq->post_content);
                    ?>
                </div>
            </div>
                
        <?php endforeach; ?>
                
    </div>
    
    <?php
        $sep = '';
    ?>
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            <?php foreach ($questions as $question): ?>
                <?php
                    echo $sep;
                    $sep = ', ';
                ?>
                
                {
                    "@type": "Question",
                    "name": <?php echo json_encode ($question->post_title); ?>,
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": <?php echo json_encode (apply_filters ('the_content', $question->post_content)); ?>
                    }
                }
                
            <?php endforeach; ?>
          
          ]
        }
    </script>

<?php endif; ?>