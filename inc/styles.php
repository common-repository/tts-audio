<?php
add_action( 'wp_enqueue_scripts', 'ttsaudio_plugin_scripts', 20 );
function ttsaudio_plugin_scripts(){

  $options = get_option( 'ttsaudio_options');

  if( !empty($options['custom_skins']) ) {

    $skin_css = '';

    foreach( $options['custom_skins'] as $i => $skin){
      $num = $i+1;

      $wrapper = '.ttsaudio-plyr.ttsaudio-plyr--custom_skin_'.$num;
      $ctrl_wrapper = $wrapper . ' .plyr__controls';

      //Start
      if(!empty($skin['bg_color'])) $skin_css .= $wrapper . '{background-color: '.$skin['bg_color'].'}';
      if(!empty($skin['bg_img'])) {
        $bg_img = trim(str_replace('background-image:', '', $skin['bg_img']));
        if (filter_var( $skin['bg_img'], FILTER_VALIDATE_URL) === FALSE) {
          $bg_img = $bg_img;
        } else $bg_img = 'url(' . $bg_img . ')';

        $skin_css .= $wrapper . '{background-image: '. $bg_img .'}';
        $skin_css .= $wrapper . '{background-position: top center; background-position: cover}';
      }

      //button & current time
      if(!empty($skin['btn_curt_color'])){
        $skin_css .= $ctrl_wrapper . ' .plyr__time--current, '.$ctrl_wrapper.' button { color: '.$skin['btn_curt_color'].'}';
        $skin_css .= $ctrl_wrapper . ' button.tab-focus:focus, '.$ctrl_wrapper.' button:hover { background-color: '.$skin['btn_hover_bg'].'; color: '.$skin['btn_curt_color'].' }';
      }
        //thumb
      if(!empty($skin['thumb_color'])){
        $thumb_color = $skin['thumb_color'];
        $skin_css .= $ctrl_wrapper . ' input[type=range]::-webkit-slider-thumb{ background-color: '.$skin['btn_curt_color'].';}';
        $skin_css .= $ctrl_wrapper . ' input[type=range]::-moz-range-thumb{ background-color: '.$skin['btn_curt_color'].'; }';
        $skin_css .= $ctrl_wrapper . ' input[type=range]::-ms-thumb{ background-color: '.$skin['btn_curt_color'].'; }';

        $skin_css .= $ctrl_wrapper . ' input[type=range]:active::-webkit-slider-thumb {background: '.$thumb_color.';}';
        $skin_css .= $ctrl_wrapper . ' input[type=range]:active::-moz-range-thumb {background: '.$thumb_color.';}';
        $skin_css .= $ctrl_wrapper . ' input[type=range]:active::-ms-thumb {background: '.$thumb_color.';}';
        $skin_css .= $ctrl_wrapper . ' input[type=range]::-ms-fill-lower { background: '.$thumb_color.' }';
      }

      //author
      if(!empty($skin['btn_curt_color']))
        $skin_css .= $wrapper . ' .ttsaudio-plyr--playlist__author a, ' . $wrapper . ' .ttsaudio-plyr--playlist__author a:hover{
        color: '.$skin['btn_curt_color'].'; }';


      //track
      if(!empty($skin['track_color'])) $skin_css .= $ctrl_wrapper . ' .plyr__progress--played, '.$ctrl_wrapper.' .plyr__volume--display{ background-color: '.$skin['track_color'].';}';

      //buffer
      if(!empty($skin['buffer_color'])) $skin_css .= $ctrl_wrapper . ' .plyr__progress--buffer{ color: '.$skin['buffer_color'].';}';

      //played & volume display
      if(!empty($skin['played_voldis'])) $skin_css .= $ctrl_wrapper . ' .plyr__progress--played, '.$ctrl_wrapper.' .plyr__volume--display{ color: '.$skin['played_voldis'].';}';

      /* PLAYLIST */
      if(!empty($skin['pll_div_color']))
        $skin_css .= $wrapper . ' .ttsaudio-plyr--playlist__list li{ border-color: '.$skin['pll_div_color'].'!important; color: '.$skin['pll_text_color'].'; }';

      if(!empty($skin['pll_hover_color']))
        $skin_css .= $wrapper . ' .ttsaudio-plyr--playlist__list li.active, ' . $wrapper . ' .ttsaudio-plyr--playlist__list li:hover{
        color: '.$skin['pll_hover_color'].'; }';

      if(!empty($skin['pll_text_color']))
        $skin_css .= $wrapper . ' .ttsaudio-plyr--playlist__list li .postdate{ color: '.$skin['pll_text_color'].'; opacity: 0.7 }';
    }

    if( !empty($skin_css) ) wp_add_inline_style('ttsaudio-style', $skin_css);
  }

}


//Pro Skins
add_filter('ttsaudio_skins', 'add_new_skins');
function add_new_skins( $skins ){

  $colors = ['Turquoise', 'Emerland', 'Peterriver', 'Amethyst', 'Wetasphalt', 'Greensea', 'Nephritis', 'Belizehole', 'Wisteria', 'Midnightblue', 'Carrot', 'Alizarin', 'Concrete', 'Pumpkin', 'Pomegranate', 'Asbestos', 'Lime', 'Green', 'Emerald', 'Teal', 'Cyan', 'Cobalt', 'Indigo', 'Violet', 'Pink', 'Magenta', 'Crimson', 'Red', 'Orange', 'Yellow', 'Brown', 'Olive', 'Steel', 'Sienna',];

  $new_skins = [];
  foreach ($colors as $color) {
    $new_skins[strtolower($color)] = $color;
  }

  $skins = $skins + $new_skins;

  $options = get_option( 'ttsaudio_options' );

  if( !empty($options['custom_skins']) ) {
    foreach( $options['custom_skins'] as $i => $skin){
      $num = $i+1;
      $skins['custom_skin_'.$num] = 'Custom Skin #'.$num;
    }

  }

  return $skins;
}
