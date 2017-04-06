/**
 * Legacy scripts taken from old RedDot website.
 */
jQuery(function() {
  // Homepage promo banners
  if (jQuery('.CandidatePromotion').length > 0) {
    var old_headline = 0;
    var current_headline = 0;
    var headline_count = jQuery(".CandidatePromotion").size();
    jQuery(".CandidatePromotion").hide().eq(0).show();
    setInterval(function() {
      current_headline = (old_headline + 1) % headline_count;
      jQuery(".CandidatePromotion:eq(" + old_headline + ")").fadeOut(1000).removeClass('Show').addClass('Hide');
      jQuery(".CandidatePromotion:eq(" + current_headline + ")").removeClass('Hide').addClass('Show').fadeIn(4000);
      old_headline = current_headline;
    }, 7000);
  }
});
