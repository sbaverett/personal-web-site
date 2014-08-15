(function ($) {
  $.fn.substitute = function (html) {
    var element = this;
    var status = false;
    
    $(element).find(".button").bind("click", function (event) {
      if ($(element).find(".data").find(".field").size() != 0) {
        $(element).find(".data").find(".field").each(function (index) {
          var selection = this;
        
          $(selection).attr("class", $(selection).val() == $(selection).attr("placeholder") ? "field error" : "field");
        });
      }
      if ($(element).find(".data").find(".error").size() == 0) {
        if (status == false) {
          status = true;
          if (html) {
            $.ajax({
              "url": $(element).find(".data").attr("action"),
              "type": $(element).find(".data").attr("method"),
              "data": $(element).find(".data").serialize(),
              "success": function (data) {                
                if ($(html).size() != 0) {
                  $(element).find(".button").replaceWith(html);
                  status = false;
                }
              }
            });
          }
          else {
            $(element).find(".data").submit();
          }
        }
      }
      return (false);
    });
    return (this);
  };
})(jQuery);
