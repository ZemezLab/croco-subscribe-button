<?php
/**
 * CrocoBlock Subscribe Button html markup.
 */

$settings = $this->get_settings();
$price    = $settings['price'];
?>

<div class="croco-subscribe-btn-container">
	<a class="croco-subscribe-btn" href="https://crocoblock.com/pricing/" target="_blank">
		<span class="croco-subscribe-btn__price">$<?php echo $price; ?></span>
		<span class="croco-subscribe-btn__price-suffix"><?php esc_html_e( '/per year', 'croco-subscribe-button' ); ?></span>
		<span class="croco-subscribe-btn__text"><?php esc_html_e( 'Subscribe now!', 'croco-subscribe-button' ); ?></span>
	</a>
</div>
