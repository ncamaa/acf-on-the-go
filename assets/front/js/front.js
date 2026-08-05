jQuery(document).ready(function ($) {
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
          text: js_object.update_txt,
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
              url: js_object.ajaxurl,
              method: 'POST',
              data: {
                  'action': 'scrap_it',
                  'nonce': js_object.nonce,
                  'textArr': textString
              },
              success: function(data) {
                
                  textString = [];
                  var jsonObj = data;

                  if(jsonObj.status == 'success') {
                      $('body').find('span[data-key = '+ jsonObj.field_key +' ]').html(jsonObj.field_content);
                     
                      $(".acfg-dialogbox").dialog('close');
                      $.toast({ 
                        loader: false, 
                        heading: js_object.success_txt,
                        icon: 'success',
                        text : js_object.success_msg, 
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
                        heading: js_object.nochange_txt,
                        icon: 'success',
                        text : js_object.nochange_msg,
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
              },
              error: function(errorThrown) {
                  console.error('errorThrown');
              }
          });

          }
        },
        {
          text: js_object.close_txt,
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

