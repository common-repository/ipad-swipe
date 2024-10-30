Zepto(document).ready(function($){
    var ajax_busy = '<div id="swp_ajax_busy"><span id="swp_ajax_loader"><img src="'+ swp_loading_img +'" /> </span><h2>Loading...</h2></div>';
    var swp_msg = '';
    var count = getCookie('view_post_count');
    if (count != "") {
        count++;
    } else {
        count = 1;
    }  
    document.cookie = "view_post_count="+ count;  

    if (count <= swp_msg_count && getCookie('dont_show_swipe_msg') == "") { 
      swp_msg = '<div id="swp-post-msg-container"><div id="swp-close-link-container"><div id="center-align-div"></div><span id="close-swp-post-msg" href="#">X</span></div><p>'+swp_message+'</p></div>';  
      floatingMenu.add('swp-post-msg-container',
      {
        centerX: true,
        centerY: true,
      });
    }

    var content_html = ajax_busy + swp_msg;
    Zepto(document.body).append(content_html);

    Zepto(document.body).swipeLeft( function() {
      if(typeof nextURL != "undefined") {
         Zepto("#swp-post-msg-container").hide(); 
         Zepto("#swp_ajax_busy").show();
         document.location = nextURL; 
      } else { 
         alert("No more pages");         
      }
    });
    Zepto("body").swipeRight( function() {
      if(typeof prevURL != "undefined") {
          Zepto("#swp-post-msg-container").hide();               
          Zepto("#swp_ajax_busy").show();
          document.location = prevURL; 
      } else {
         alert("No more pages");                                     
      }
    });

    Zepto("#swp-close-link-container").tap(function() {
        Zepto("#swp-post-msg-container").remove();
        document.cookie = "dont_show_swipe_msg=true";     
        Zepto("#swp-post-msg-container").unbind();
        return false;     
    });

    floatingMenu.add('swp_ajax_busy',
    {
      // Uncomment one of those if you need centering on
      // X- or Y- axis.
      centerX: true,
      centerY: true,
    });
    Zepto("#swp_ajax_busy").hide();

});

/**
 * Function to get cookie
 */
function getCookie(c_name)
{
  if (document.cookie.length>0)
  {
  c_start=document.cookie.indexOf(c_name + "=");
  if (c_start!=-1)
    {
    c_start=c_start + c_name.length+1;
    c_end=document.cookie.indexOf(";",c_start);
    if (c_end==-1) c_end=document.cookie.length;
    return unescape(document.cookie.substring(c_start,c_end));
    }
  }
  return "";
}
