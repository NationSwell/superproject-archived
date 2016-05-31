// // global
// var bitlyClientJS = window.bitlyClientJS = function(){};

// bitlyClientJS.prototype.LastClickedURL = "";
// bitlyClientJS.prototype.shareThisFix = function(){
//   // sharethis JS fixes
//   var that = this;
//   console.log('initializing stf')
//   jQuery('span.stButton').on('click', function(){
//     console.log('stButton click');
//     var URLElement = jQuery(this).closest('span[st_url]');
//     if ( URLElement.length > 0 ){
//      that.LastClickedURL = URLElement.attr('st_url');
//     }
//   });

//   jQuery('span[data-pin-url]').on('click', function() {
//     console.log('st_pin');
//     // set it to the correct one!
//     if ( that.LastClickedURL != "" ){
//       jQuery(this).attr('data-pin-url', that.LastClickedURL);
//     }else{ // if we're on just a single post page, we can set it!
//       var BitlyURL = jQuery('meta[property="bitly:url"]').attr('content');
//       if ( typeof BitlyURL  !== 'undefined' ){
//         jQuery(this).attr('data-pin-url', BitlyURL);
//       }
//     }
//   });
  

// };

// jQuery(document).ready(function () {
//   var bcjs = new bitlyClientJS();
//   bcjs.shareThisFix();
// });

