( function( $ ) {
  "use strict";

  // Edit prompt
  $( function() {

    $('#set_default_language').click(function(){
      var button = $(this);
      var data = {
        action: 'wpm_set_default_language',
        security: wpm_additional_settings_params.set_default_language_nonce
      };

      $.ajax({
        url: wpm_additional_settings_params.ajax_url,
        type: 'post',
        data: data,
        dataType: 'json',
        beforeSend: function() {
          button.prop('disabled', true).after('<span class="spinner is-active"></span>');
        },
        success: function (json) {
          button.next().remove();
          button.after('<span class="success">' + json + '</span>');
        },
        complete: function() {
          button.prop('disabled', false);
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    });

    $('#wpm_installed_localizations').on('init_localizations', function(){
      if ($(this).val()) {
        $('#delete_localization').prop('disabled', false);
      } else {
        $('#delete_localization').prop('disabled', true);
      }
    });

    $('#delete_localization').click(function(){

      if (confirm(wpm_additional_settings_params.confirm_question)) {

        var locale = $('#wpm_installed_localizations').val();
        var button = $(this);

        var data = {
          action: 'wpm_delete_localization',
          locale: locale,
          security: wpm_additional_settings_params.delete_localization_nonce
        };

        $.ajax({
          url: wpm_additional_settings_params.ajax_url,
          type: 'post',
          data: data,
          dataType: 'json',
          beforeSend: function() {
            button.prop('disabled', true).after('<span class="spinner is-active"></span>');
          },
          success: function (json) {
            button.next().remove();
            if (json.success) {
              button.after('<span class="success">' + json.data + '</span>');
              $('#wpm_installed_localizations option[value="' + locale + '"]').remove();
            } else {
              button.after('<span class="error">' + json.data + '</span>');
            }
            $('#wpm_installed_localizations').trigger('init_localizations');
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    });

    $('#qts_import').click(function(){
      var button = $(this);

      var data = {
        action: 'wpm_qts_import',
        security: wpm_additional_settings_params.qts_import_nonce
      };

      $.ajax({
        url: wpm_additional_settings_params.ajax_url,
        type: 'post',
        data: data,
        dataType: 'json',
        beforeSend: function() {
          button.prop('disabled', true).after('<span class="spinner is-active"></span>');
        },
        success: function (json) {
          button.next().remove();
          button.after('<span class="success">' + json + '</span>');
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    });

  });
})( jQuery );
