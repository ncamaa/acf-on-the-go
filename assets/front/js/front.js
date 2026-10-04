jQuery(document).ready(function ($) {

  /**
   * Shows a red error toast.
   *
   * The toast library renders its text as HTML, so only pass it strings
   * that come from the plugin's own (translated) messages.
   *
   * @param {string} message Message to display.
   */
  function acfgShowError(message) {
    $.toast({
      loader: false,
      heading: acfg_object.error_txt,
      icon: 'error',
      text: message,
      showHideTransition: 'plain',
      bgColor: '#d63638',
      textColor: '#fff',
      allowToastClose: true,
      hideAfter: 7000,
      stack: 5,
      textAlign: 'left',
      position: 'mid-center'
    });
  }

  //style all the dialogue

  jQuery(function ($) {
    $(".acfg-dialogbox").dialog({
      modal: true,
      autoOpen: false,
      dialogClass: 'ui-dialog-osx acfg-dialog-box',
      resizable: true,
      width: 'auto',
      buttons: [
        {
          text: acfg_object.update_txt,
          class: 'acfg-update-btn',
          click: function () {
            var acf_data = $(this).parent().find('.acfg-dialogbox');
            var textString = [];
            acf_data.each(function () {
              var field_content = $(this).children('.acfg-inner-content').val();
              var field_key = $(this).data('key');
              var field_name = $(this).data('name');
              var current_postid = $(this).data('postid');
              var textArr = [field_key, field_content, field_name, current_postid];
              textString.push(textArr);
            });
            
            $.ajax({
              url: acfg_object.ajaxurl,
              method: 'POST',
              data: {
                  'action': 'acfg_update_fields',
                  'nonce': acfg_object.nonce,
                  'textArr': textString
              },
              success: function(data) {
                
                  textString = [];
                  var jsonObj = data;

                  if(jsonObj.status == 'success') {
                      $('body').find('span[data-key="' + jsonObj.field_key + '"]').text(jsonObj.field_content);

                      $(".acfg-dialogbox").dialog('close');
                      $.toast({ 
                        loader: false, 
                        heading: acfg_object.success_txt,
                        icon: 'success',
                        text : acfg_object.success_msg, 
                        showHideTransition : 'plain',  // It can be plain, fade or slide
                        bgColor : '#28a745',             // Background color for toast
                        textColor : '#eee',            // text color
                        allowToastClose : true,       // Show the close button or not
                        hideAfter : 5000,              // `false` to make it sticky or time in miliseconds to hide after
                        stack : 5,                     // `fakse` to show one stack at a time count showing the number of toasts that can be shown at once
                        textAlign : 'left',            // Alignment of text i.e. left, right, center
                        position : 'mid-center'       // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values to position the toast on page
                      })
                  }

                  if(jsonObj.status == 'no-changes') {
                     
                      $(".acfg-dialogbox").dialog('close');
                      $.toast({ 
                        loader: false,
                        heading: acfg_object.nochange_txt,
                        icon: 'success',
                        text : acfg_object.nochange_msg,
                        showHideTransition : 'slide',  // It can be plain, fade or slide
                        bgColor : 'blue',              // Background color for toast
                        textColor : '#eee',            // text color
                        allowToastClose : true,       // Show the close button or not
                        hideAfter : 5000,              // `false` to make it sticky or time in miliseconds to hide after
                        stack : 5,                     // `fakse` to show one stack at a time count showing the number of toasts that can be shown at once
                        textAlign : 'left',            // Alignment of text i.e. left, right, center
                        position : 'mid-center'       // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values to position the toast on page
                      })
                  }

                  // wp_send_json_error() responses (bad nonce data, no permission, field not editable).
                  if (jsonObj.success === false) {
                      acfgShowError(jsonObj.data && jsonObj.data.message ? jsonObj.data.message : acfg_object.error_msg);
                  }
              },
              error: function(jqXHR) {
                  // An expired nonce makes check_ajax_referer() answer with HTTP 403.
                  console.error('ACF On The Go: save request failed', jqXHR.status);
                  acfgShowError(acfg_object.error_msg);
              }
          });

          }
        },
        {
          text: acfg_object.close_txt,
          class: 'acfg-close-btn',
          click: function () {
            $(this).dialog("close")
          }
        }
      ]
    });
  });

  //opens the appropriate dialog
  jQuery(function ($) {
    $(".acfg-dialog").click(function () {
      //takes the ID of appropriate dialogue
      var id = $(this).data('id');
      var label = $(this).data('field-label');
      $(id).dialog("option","title",label).dialog('open');
    });
  });
});

