<!-- Hero Section -->
<section class="hero-carousel-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php
            $first = true;
            while ($slide = $slides->fetch_assoc()) {
                $active = $first ? 'active' : '';
                echo "
                <div class='carousel-item $active'>
                    <div class='hero-slide d-flex align-items-center text-white img-fluid'
                         style='background-image: url(\"Media/{$slide['background_image']}\");'>
                        <div class='container text-start'>
                            <div class='animate__animated animate__fadeInUp'>
                                <h1 class='display-4 fw-bold' style='font-family: \"Playfair Display\", serif; color: {$slide['text_color']};'>{$slide['title']}</h1>
                                <p class='lead' style='font-family: \"Open Sans\", sans-serif; color: {$slide['text_color']};'>{$slide['description']}</p>
                                <a href='{$slide['button_link']}' class='btn btn-lg btn-light hero-btn mt-3'>{$slide['button_text']}</a>
                            </div>
                        </div>
                    </div>
                </div>";
                $first = false;
            }
            ?>
        </div>
    </div>
</section>