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
          text: "Update",
          class: 'acfg-update-btn',
          click: function () {
            var acf_data = $(this).parent().find('.acfg-dialogbox');
            var textString = [];
            acf_data.each(function () {
              // var field_content = $(this).html();
              var field_content = $(this).children('.acfg-inner-content').val();
              var field_key = $(this).data('key');
              var field_name = $(this).data('name');
              var current_postid = $(this).data('postid');
              var textArr = [field_key, field_content, field_name, current_postid];
              textString.push(textArr);
            });

            $.ajax({
              url: js_object.ajaxurl,
              data: {
                  'action': 'scrap_it',
                  'textArr': textString
              },
              success: function(data) {
                  textString = [];
                  var jsonObj = jQuery.parseJSON(data);

                  if(jsonObj.status == 'success') {
                      $('body').find('span[data-key = '+ jsonObj.field_key +' ]').html(jsonObj.field_content);
                      // alert(jsonObj.message);
                      $(".acfg-dialogbox").dialog('close');
                      $.toast({ 
                        loader: false, 
                        heading: 'Success',
                        icon: 'success',
                        text : jsonObj.message, 
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
                      alert(jsonObj.message);
                      $.toast({ 
                        text : jsonObj.message, 
                        showHideTransition : 'slide',  // It can be plain, fade or slide
                        bgColor : 'blue',              // Background color for toast
                        textColor : '#eee',            // text color
                        allowToastClose : false,       // Show the close button or not
                        hideAfter : 5000,              // `false` to make it sticky or time in miliseconds to hide after
                        stack : 5,                     // `fakse` to show one stack at a time count showing the number of toasts that can be shown at once
                        textAlign : 'left',            // Alignment of text i.e. left, right, center
                        position : 'bottom-left'       // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values to position the toast on page
                      })
                      // $(".acfg-dialogbox").dialog('close');
                  }
              },
              error: function(errorThrown) {
                  console.error('errorThrown');
              }
          });

          }
        },
        {
          text: "Close",
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
      //open dialogue
      // $(id).dialog("open");
      $(id).dialog("option","title",label).dialog('open');

    });
  });

  var old_html = jQuery('.acf-onthego').html();

});

