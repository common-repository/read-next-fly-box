jQuery(document).ready(function(){

    var box = jQuery('#flybox-wrapper');

    if(box.length)
    {
        /**
         * Get the current size of the window
         * @type {*}
         */
        var height = jQuery(window).height();
        var top = jQuery(document).scrollTop();



        threshold = parseFloat(threshold);

        jQuery(document).scroll(function(){

            top = jQuery(document).scrollTop();

            if(top < height + threshold)
            {
                // esconder o box
                jQuery('#flybox-wrapper').slideUp('fast');
            }
            else
            {
                //mostrar o box
                jQuery('#flybox-wrapper').slideDown('fast');
            }

        })
    }
});
